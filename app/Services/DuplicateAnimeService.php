<?php

namespace App\Services;

use App\Models\Anime;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;

class DuplicateAnimeService
{
    public function exportDuplicatesToCSV($logger = null)
    {
        $date = Carbon::now()->format('Y-m-d_H-i-s');
        $startTime = microtime(true);

        $exportResults = [];
        $exportResults['duplicateCounts'] = $this->exportDuplicateCounts($date, $logger);
        $exportResults['totalDuplicates'] = $this->exportTotalDuplicates($date, $logger);
        $exportResults['allDuplicateDetails'] = $this->exportAllDuplicateDetails($date, $logger);

        $duration = microtime(true) - $startTime;

        return [
            'duration' => $duration,
            'timestamp' => $date,
            'exports' => $exportResults,
        ];
    }

    private function exportDuplicateCounts($date, $logger)
    {
        $titleDuplicates = DB::table('anime')
            ->select('title', DB::raw('COUNT(*) as occurrences'))
            ->groupBy('title')
            ->havingRaw('COUNT(*) > 1')
            ->get();
        // Technically, duplicate pictures can generate false positives, like 139 entries with default spring picture would mean they aren't duplicates, but if there's two or three or four of the same picture, that's very likely a duplicate entry.
        $pictureDuplicates = DB::table('anime')
            ->select('picture', DB::raw('COUNT(*) as occurrences'))
            ->whereNotNull('picture')
            ->where('picture', '!=', '')
            ->groupBy('picture')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $duplicates = $titleDuplicates->merge($pictureDuplicates);

        return $this->saveToCsv($duplicates, "duplicate_counts_{$date}.csv", $logger);
    }

    private function exportTotalDuplicates($date, $logger)
    {
        $count = DB::table('anime')
            ->whereIn('title', function ($query) {
                $query->select('title')
                    ->from('anime')
                    ->groupBy('title')
                    ->havingRaw('COUNT(*) > 1');
            })
            ->orWhereIn('picture', function ($query) {
                $query->select('picture')
                    ->from('anime')
                    ->whereNotNull('picture')
                    ->where('picture', '!=', '')
                    ->groupBy('picture')
                    ->havingRaw('COUNT(*) > 1');
            })
            ->count();

        $totalDuplicatesData = new \stdClass;
        $totalDuplicatesData->total_duplicate_titles = $count;

        return $this->saveToCsv([$totalDuplicatesData], "total_duplicates_{$date}.csv", $logger);
    }

    private function exportAllDuplicateDetails($date, $logger)
    {
        // Technically, duplicate pictures can generate false positives, like 139 entries with default spring picture would mean they aren't duplicates, but if there's two or three or four of the same picture, that's very likely a duplicate entry.
        $duplicates = DB::table('anime')
            ->whereIn('title', function ($query) {
                $query->select('title')
                    ->from('anime')
                    ->groupBy('title')
                    ->havingRaw('COUNT(*) > 1');
            })
            ->orWhereIn('picture', function ($query) {
                $query->select('picture')
                    ->from('anime')
                    ->whereNotNull('picture')
                    ->where('picture', '!=', '')
                    ->groupBy('picture')
                    ->havingRaw('COUNT(*) > 1');
            })
            ->orderBy('title')
            ->get();

        return $this->saveToCsv($duplicates, "all_duplicates_details_{$date}.csv", $logger);
    }

    private function saveToCsv($data, $filename, $logger)
    {
        $csv = Writer::createFromString('');

        // Check if $data is a Collection or an array
        $firstRecord = is_array($data) ? reset($data) : $data->first();

        // Convert the first record to an array and insert the keys as the first row in the CSV
        $csv->insertOne(array_keys((array) $firstRecord));

        // Iterate over each record and insert into CSV
        foreach ($data as $record) {
            $csv->insertOne((array) $record);
        }

        $filePath = "csv/$filename";
        Storage::disk('local')->put($filePath, $csv->getContent());
        $logger && $logger('CSV file generated: '.storage_path($filePath));

        return [
            'count' => is_array($data) ? count($data) : $data->count(),
            'filePath' => $filePath,
        ];
    }

    public function mergeDuplicateAnime($oldAnimeId, $newAnimeId, $logger = null)
    {
        DB::beginTransaction();

        try {
            // Log start of process
            $logger && $logger("Starting merge of anime ID $oldAnimeId into $newAnimeId");

            // Reconcile column data before deleting the old row. The target (new)
            // anime keeps all of its own populated values; any field that is null
            // or empty on the target is back-filled from the old entry so scraped
            // data like mal_rank, mal_members, description, etc. is not lost.
            $this->reconcileAnimeColumns($oldAnimeId, $newAnimeId, $logger);

            // Update references in anime_user table
            DB::table('anime_user')
                ->where('anime_id', $oldAnimeId)
                ->update(['anime_id' => $newAnimeId]);

            $logger && $logger("Updated anime_user references from $oldAnimeId to $newAnimeId");

            // Update references in anime_reviews table
            DB::table('anime_reviews')
                ->where('anime_id', $oldAnimeId)
                ->update(['anime_id' => $newAnimeId]);

            $logger && $logger("Updated anime_reviews references from $oldAnimeId to $newAnimeId");

            // Update references in anime_favourites table
            DB::table('anime_favourites')
                ->where('anime_id', $oldAnimeId)
                ->update(['anime_id' => $newAnimeId]);

            $logger && $logger("Updated anime_favourites references from $oldAnimeId to $newAnimeId");

            // Delete the old anime entry
            DB::table('anime')
                ->where('id', $oldAnimeId)
                ->delete();

            $logger && $logger("Deleted old anime entry with ID $oldAnimeId");

            DB::commit();

            return ['status' => 'success', 'message' => "Anime with ID $oldAnimeId merged into $newAnimeId successfully"];
        } catch (\Exception $e) {
            DB::rollBack();
            $logger && $logger('Error during merge: '.$e->getMessage());
            Log::error("Error merging anime IDs $oldAnimeId into $newAnimeId: ".$e->getMessage());

            return ['status' => 'error', 'message' => 'Merge failed: '.$e->getMessage()];
        }
    }

    /**
     * Back-fill any null or empty columns on the target (new) anime using the
     * values from the old anime that is about to be deleted. The target's own
     * populated values always win on conflict, so this only ever fills gaps and
     * never overwrites existing data. Identity and timestamp columns are skipped.
     *
     * Booleans and numeric zeros are treated as real values (not "empty"), so a
     * legitimate 0 or false on the target is never clobbered.
     */
    private function reconcileAnimeColumns($oldAnimeId, $newAnimeId, $logger = null)
    {
        $oldAnime = DB::table('anime')->where('id', $oldAnimeId)->first();
        $newAnime = DB::table('anime')->where('id', $newAnimeId)->first();

        // Nothing to reconcile if either row is missing.
        if (! $oldAnime || ! $newAnime) {
            return;
        }

        // Pull columns dynamically so new migrations are covered automatically.
        $skip = ['id', 'created_at', 'updated_at'];
        $columns = array_diff(Schema::getColumnListing('anime'), $skip);

        $updates = [];
        foreach ($columns as $column) {
            $newValue = $newAnime->$column ?? null;
            $oldValue = $oldAnime->$column ?? null;

            // Only fill when the target is empty (null or empty string) and the
            // old row actually has something to offer. The string '0' and other
            // numeric/boolean zeros are not considered empty here.
            $targetEmpty = $newValue === null || $newValue === '';
            $oldHasValue = $oldValue !== null && $oldValue !== '';

            if ($targetEmpty && $oldHasValue) {
                $updates[$column] = $oldValue;
            }
        }

        if (! empty($updates)) {
            DB::table('anime')
                ->where('id', $newAnimeId)
                ->update($updates);

            $logger && $logger('Back-filled '.count($updates).' empty field(s) on anime ID '.$newAnimeId.' from anime ID '.$oldAnimeId.': '.implode(', ', array_keys($updates)));
        } else {
            $logger && $logger("No empty fields on anime ID $newAnimeId to back-fill from anime ID $oldAnimeId");
        }
    }
}
