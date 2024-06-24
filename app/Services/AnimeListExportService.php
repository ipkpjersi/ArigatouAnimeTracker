<?php

namespace App\Services;

use App\Models\AnimeUser;
use SimpleXMLElement;

class AnimeListExportService
{
    public function export($fileType, $userId, $logger = null)
    {
        if ($fileType === 'myanimelist') {
            return $this->exportToMyAnimeList($userId, $logger);
        } elseif ($fileType === 'arigatou') {
            return $this->exportToArigatou($userId, $logger);
        } elseif ($fileType === 'myanimelistcss') {
            return $this->exportToMyAnimeListCss($userId, $logger);
        } else {
            // Unknown file type
            return '';
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
        $myinfo->addChild('user_id', ''); //TODO: maybe fill these in from some user profile settings?
        $myinfo->addChild('user_name', ''); //TODO: maybe fill these in from some user profile settings?
        $myinfo->addChild('user_export_type', '1');
        $myinfo->addChild('user_total_anime', $animeList->count());

        $dom = new \DOMDocument('1.0');
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
            $anime->addChild('my_status', str_replace('-', ' ', strtolower($animeUser->watch_status?->status ?? 'PLAN-TO-WATCH')));
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

        return ['total' => $total, 'duration' => $duration, 'output' => $formattedXml];
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
                'season' => $animeUser->anime->season,
                'year' => $animeUser->anime->year,
                'episodes' => $animeUser->anime->episodes,
                'duration' => $animeUser->anime->duration ?? 'UNKNOWN',
                'rating' => $animeUser->anime->rating ?? 'UNKNOWN',
                'status' => $animeUser->anime_status?->status ?? 'UNKNOWN',
                'watch_status' => $animeUser->watch_status?->status ?? 'PLAN-TO-WATCH',
                'score' => $animeUser->score,
                'progress' => $animeUser->progress,
                'notes' => $animeUser->notes,
                'sort_order' => $animeUser->sort_order,
                'display_in_list' => $animeUser->display_in_list,
                'show_anime_notes_publicly' => $animeUser->show_anime_notes_publicly,
            ];
        }

        $formattedJson = json_encode(['animeList' => $animeArray], JSON_PRETTY_PRINT);
        $duration = microtime(true) - $startTime;

        return ['total' => $total, 'duration' => $duration, 'output' => $formattedJson];
    }

    private function exportToMyAnimeListCss($userId, $logger = null)
    {
        $startTime = microtime(true);
        $animeList = AnimeUser::with('anime')
            ->where('user_id', $userId)
            ->whereHas('watch_status', function ($query) {
                $query->where('status', 'COMPLETED');
            })
            ->where('sort_order', '>=', 1)
            ->orderBy('sort_order', 'ASC')
            ->get();
        $total = count($animeList);
        $cssOutput = '';
        //These seem to be the min and max orders MAL allows, starting from -1000 and going up to 3000.
        $minOrder = -1000;
        $maxOrder = 3000;
        $currentOrder = $minOrder;
        foreach ($animeList as $animeUser) {
            $source = collect(explode(', ', $animeUser->anime->sources))->first(function ($source) {
                return str_contains($source, 'myanimelist.net/anime/');
            });
            if ($source) {
                preg_match('/myanimelist\.net\/anime\/(\d+)/', $source, $matches);
                $animeId = $matches[1] ?? null;
                if ($animeId) {
                    //If we wanted to use just the provided sort order, from 1 to 3000, we could just use sort_order.
                    //$order = $animeUser->sort_order;
                    //Technically, by default the order is limited from 1 to 3000, maybe we want more anime total, so let's scale it from min to max for a total of 4000 anime.
                    $order = $currentOrder;
                    $cssOutput .= ".list-container .list-block .completed table.list-table > tbody.list-item:has(a[href*=\"anime/{$animeId}/\"]) {\n";
                    $cssOutput .= "  visibility: unset;\n";
                    $cssOutput .= "  height: unset;\n";
                    $cssOutput .= "  order: {$order};\n";
                    $cssOutput .= "}\n";
                    $currentOrder++;
                }
            }
            if ($currentOrder > $maxOrder) {
                exit("You have exceeded the maximum total anime for MAL custom sorting, congratulations! This isn't very easily achievable.");
            }
        }

        $duration = microtime(true) - $startTime;

        return ['total' => $total, 'duration' => $duration, 'output' => $cssOutput];
    }
}
