<?php

namespace App\Services;

use App\Models\AnimeUser;
use App\Models\WatchStatus;
use Illuminate\Support\Facades\Auth;
use SimpleXMLElement;

class AnimeListExportService
{
    public function export($fileType, $userId, $logger = null)
    {
        if ($fileType === 'myanimelist') {
            return $this->exportToMyAnimeList($userId, $logger);
        } elseif ($fileType === 'arigatou') {
            return $this->exportToArigatou($userId, $logger);
        } else {
            // Unknown file type
            return "";
        }
    }

    private function exportToMyAnimeList($userId, $logger = null)
    {
        $startTime = microtime(true);
        $animeList = AnimeUser::with('anime', 'watch_status')
            ->where('user_id', $userId)
            ->get();
        $total = count($animeList);
        $xml = new SimpleXMLElement('<myanimelist></myanimelist>');
        $myinfo = $xml->addChild('myinfo');
        $myinfo->addChild('user_id', ""); //TODO: maybe fill these in from some user profile settings?
        $myinfo->addChild('user_name', ""); //TODO: maybe fill these in from some user profile settings?
        $myinfo->addChild('user_export_type', '1');
        $myinfo->addChild('user_total_anime', $animeList->count());

        $dom = new \DOMDocument("1.0");
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        foreach ($animeList as $animeUser) {
            $anime = $xml->addChild('anime');
            $anime->addChild('series_animedb_id', '');

            // Create 'series_title' element with CDATA content
            $seriesTitle = $dom->createElement('series_title');
            $titleCdata = $dom->createCDATASection($animeUser->anime->title);
            $seriesTitle->appendChild($titleCdata);

            // Create 'my_comments' element and add CDATA content
            $myComments = $dom->createElement('my_comments');
            $commentsCdata = $dom->createCDATASection($animeUser->notes ?? '');
            $myComments->appendChild($commentsCdata);

            // Import 'series_title' and 'my_comments' nodes
            $animeNode = dom_import_simplexml($anime);
            $animeNode->appendChild($animeNode->ownerDocument->importNode($seriesTitle, true));

            // Add other children to $anime
            $anime->addChild('series_type', $animeUser->anime->anime_type->type);
            $anime->addChild('series_episodes', $animeUser->anime->episodes);
            $anime->addChild('my_id', 0);
            $anime->addChild('my_watched_episodes', $animeUser->progress);
            $anime->addChild('my_start_date', '0000-00-00');
            $anime->addChild('my_finish_date', '0000-00-00');
            $anime->addChild('my_rated', '');
            $anime->addChild('my_score', $animeUser->score);
            $anime->addChild('my_storage', '');
            $anime->addChild('my_storage_value', '0.00');
            $anime->addChild('my_status', str_replace("-", " ", strtolower($animeUser->watch_status?->status ?? 'PLAN-TO-WATCH')));
            $animeNode->appendChild($animeNode->ownerDocument->importNode($myComments, true));
            $anime->addChild('my_times_watched', 0);
            $anime->addChild('my_rewatch_value', '');
            $anime->addChild('my_priority', 'LOW');
            $anime->addChild('my_tags', '');
            $anime->addChild('my_rewatching', 0);
            $anime->addChild('my_rewatching_ep', 0);
            $anime->addChild('my_discuss', 1);
            $anime->addChild('my_sns', 'default');
            $anime->addChild('update_on_import', 0);
        }

        $dom->loadXML($xml->asXML()); // Reload the XML with the newly added nodes
        $formattedXml = $dom->saveXML();
        $duration = microtime(true) - $startTime;
        return ["total" => $total, "duration" => $duration, "output" => $formattedXml];
    }

    private function exportToArigatou($userId, $logger = null)
    {
        $startTime = microtime(true);
        $animeList = AnimeUser::with('anime', 'watch_status')
            ->where('user_id', $userId)
            ->get();
        $total = count($animeList);
        $animeArray = [];

        foreach ($animeList as $animeUser) {
            $animeArray[] = [
                'title' => $animeUser->anime->title,
                'type' => $animeUser->anime->anime_type->type,
                'episodes' => $animeUser->anime->episodes,
                'watch_status' => $animeUser->watch_status?->status ?? 'PLAN-TO-WATCH',
                'score' => $animeUser->score,
                'progress' => $animeUser->progress,
                'notes' => $animeUser->notes,
                'sort_order' => $animeUser->sort_order,
                'display_in_list' => $animeUser->display_in_list,
                'show_anime_notes_publicly' => $animeUser->show_anime_notes_publicly
            ];
        }

        $formattedJson = json_encode(['animeList' => $animeArray], JSON_PRETTY_PRINT);
        $duration = microtime(true) - $startTime;
        return ["total" => $total, "duration" => $duration, "output" => $formattedJson];
    }
}
