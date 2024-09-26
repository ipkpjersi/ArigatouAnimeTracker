<?php

namespace App\Services;

use App\Models\Anime;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        //Technically, duplicate pictures can generate false positives, like 139 entries with default spring picture would mean they aren't duplicates, but if there's two or three or four of the same picture, that's very likely a duplicate entry.
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

        $totalDuplicatesData = new \stdClass();
        $totalDuplicatesData->total_duplicate_titles = $count;

        return $this->saveToCsv([$totalDuplicatesData], "total_duplicates_{$date}.csv", $logger);
    }

    private function exportAllDuplicateDetails($date, $logger)
    {
        //Technically, duplicate pictures can generate false positives, like 139 entries with default spring picture would mean they aren't duplicates, but if there's two or three or four of the same picture, that's very likely a duplicate entry.
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

        //Check if $data is a Collection or an array
        $firstRecord = is_array($data) ? reset($data) : $data->first();

        //Convert the first record to an array and insert the keys as the first row in the CSV
        $csv->insertOne(array_keys((array) $firstRecord));

        //Iterate over each record and insert into CSV
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
            //Log start of process
            $logger && $logger("Starting merge of anime ID $oldAnimeId into $newAnimeId");

            //Update references in anime_user table
            DB::table('anime_user')
                ->where('anime_id', $oldAnimeId)
                ->update(['anime_id' => $newAnimeId]);

            $logger && $logger("Updated anime_user references from $oldAnimeId to $newAnimeId");

            //Update references in anime_reviews table
            DB::table('anime_reviews')
                ->where('anime_id', $oldAnimeId)
                ->update(['anime_id' => $newAnimeId]);

            $logger && $logger("Updated anime_reviews references from $oldAnimeId to $newAnimeId");

            //Delete the old anime entry
            DB::table('anime')
                ->where('id', $oldAnimeId)
                ->delete();

            $logger && $logger("Deleted old anime entry with ID $oldAnimeId");

            DB::commit();

            return ['status' => 'success', 'message' => "Anime with ID $oldAnimeId merged into $newAnimeId successfully"];
        } catch (\Exception $e) {
            DB::rollBack();
            $logger && $logger("Error during merge: " . $e->getMessage());
            Log::error("Error merging anime IDs $oldAnimeId into $newAnimeId: " . $e->getMessage());

            return ['status' => 'error', 'message' => 'Merge failed: ' . $e->getMessage()];
        }
    }

    public function getAnimeDetails($animeId)
    {
        return Anime::select('id', 'title', 'year', 'season', 'anime_type_id', 'episodes', 'synonyms')
            ->with('anime_type', 'anime_status')
            ->find($animeId);
    }
}
