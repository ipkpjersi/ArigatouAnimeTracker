<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AnimeController extends Controller
{
    public function getAnimeData(Request $request)
    {
        if (!request()->has(['start', 'length'])) {
            return response()->json(['error' => 'Invalid request'], 400);
        }
        $query = Anime::with('anime_type', 'anime_status')->selectRaw('*,
            CASE WHEN season = "UNDEFINED" THEN "UNKNOWN" ELSE season END as season_display,
            CASE season
                WHEN "SPRING" THEN 1
                WHEN "SUMMER" THEN 2
                WHEN "FALL" THEN 3
                WHEN "WINTER" THEN 4
                ELSE 0
            END as season_sort')
        ->orderBy('year', 'desc')
        ->orderBy('season_sort', 'desc');

        if (auth()->user() == null || auth()->user()->show_adult_content == false) {
            //No need for this to be plain-text, so we'll use rot13.
            $query = $query->where('tags', 'NOT LIKE', '%' . str_rot13('uragnv') . '%');
        }

        return DataTables::of($query)
            ->filterColumn('season', function($query, $keyword) {
                if (strtoupper($keyword) === 'UNKNOWN') {
                    $query->orWhere('season', 'UNDEFINED');
                } elseif (in_array(strtoupper($keyword), ["WINTER", "SPRING", "SUMMER", "FALL"])) {
                    $query->orWhere('season', strtoupper($keyword));
                }
            })
            ->filterColumn('tags', function($query, $keyword) {
                $searchTags = collect(explode(',', $keyword))
                            ->map(fn($tag) => trim(strtolower($tag)));

                foreach ($searchTags as $tag) {
                    $query->whereRaw('LOWER(tags) LIKE ?', ["%$tag%"]);
                }
            })
            ->make(true);
    }

    public function list() {
        return view("animelist");
    }

    public function detail($id, $title = null)
    {
        $anime = Anime::with('anime_type', 'anime_status')->findOrFail($id);
        if ($anime->season === "UNDEFINED") {
            $anime->season = "UNKNOWN";
        }
        return view('animedetail', compact('anime'));
    }
}
