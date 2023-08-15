<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class AnimeController extends Controller
{
    public function getAnimeData(Request $request)
    {
        if (!request()->has(['start', 'length']) || request()->input('length') > 1000) {
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
                //We could add a junction table for anime and tags, but this is probably fine.
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


    public function userAnimeList($username) {
        $user = User::where('username', $username)->firstOrFail();

        $query = $user->anime()
                          ->with(['anime_type', 'anime_status'])
                          ->orderBy('sort_order', 'asc');
        if (config('config.user_anime_list_paginated')) {
            $userAnime = $query->paginate('15');
        } else {
           $userAnime = $query->get();
        }
        return view('userAnimeList', ['userAnime' => $userAnime]);
    }

    public function addToList($id)
    {
        $user = Auth::user();
        $anime = Anime::findOrFail($id);

        $user->anime()->attach($anime);

        return redirect()->back()->with('message', 'Anime added to your list!');
    }
}
