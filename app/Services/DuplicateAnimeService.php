<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;
use Carbon\Carbon;

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
            'exports' => $exportResults
        ];
    }

    private function exportDuplicateCounts($date, $logger)
    {
        $duplicates = DB::table('anime')
            ->select('title', DB::raw('COUNT(*) as occurrences'))
            ->groupBy('title')
            ->havingRaw('COUNT(*) > 1')
            ->get();

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
            ->count();

        $totalDuplicatesData = new \stdClass();
        $totalDuplicatesData->total_duplicate_titles = $count;

        return $this->saveToCsv([$totalDuplicatesData], "total_duplicates_{$date}.csv", $logger);
    }

    private function exportAllDuplicateDetails($date, $logger)
    {
        $duplicates = DB::table('anime')
            ->whereIn('title', function ($query) {
                $query->select('title')
                      ->from('anime')
                      ->groupBy('title')
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
        $csv->insertOne(array_keys((array)$firstRecord));

        // Iterate over each record and insert into CSV
        foreach ($data as $record) {
            $csv->insertOne((array)$record);
        }

        $filePath = "csv/$filename";
        Storage::disk('local')->put($filePath, $csv->getContent());
        $logger && $logger("CSV file generated: " . storage_path($filePath));

        return [
            'count' => is_array($data) ? count($data) : $data->count(),
            'filePath' => $filePath
        ];
    }
}
