<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\User;
use App\Models\WatchStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $show_anime_list_number = $user->show_anime_list_number;
        $watchStatuses = DB::table('watch_status')->get();
        $watchStatusMap = $watchStatuses->pluck('status', 'id')->toArray();
        $userAnime = $user->anime()
                          ->with(['anime_type', 'anime_status'])
                          ->orderByRaw('ISNULL(sort_order) ASC, sort_order ASC')
                          ->paginate($user->anime_list_pagination_size ?? 15);

        return view('userAnimeList', ['userAnime' => $userAnime, 'username' => $username, 'show_anime_list_number' => $show_anime_list_number, 'watchStatuses' => $watchStatuses, 'watchStatusMap' => $watchStatusMap]);
    }

    public function userAnimeListV2($username)
    {
        $user = User::where('username', $username)->firstOrFail();
        $watchStatuses = WatchStatus::all();
        $watchStatusMap = $watchStatuses->pluck('status', 'id')->toArray();
        $userAnimeCount = $user->anime()
                          ->with(['anime_type', 'anime_status', 'watch_status'])->count();

        return view('userAnimeListV2', [
            'username' => $username,
            'watchStatuses' => $watchStatuses,
            'watchStatusMap' => $watchStatusMap,
            'userAnimeCount' => $userAnimeCount
        ]);
    }

    public function getUserAnimeDataV2($username, Request $request)
    {
        $user = User::where('username', $username)->firstOrFail();
        $query = $user->anime()
                      ->with(['anime_type', 'anime_status']);
                      //->orderByRaw('ISNULL(sort_order) ASC, sort_order ASC'); TODO: fix datatables pre-sorting

        return DataTables::of($query)
            ->addColumn('anime_id', function ($row) {
                return $row->anime_id;
            })
            ->make(true);
    }

    public function updateUserAnimeList(Request $request, $username) {
        $user = User::where('username', $username)->firstOrFail();

        if ($request->has('anime_ids') && is_array($request->anime_ids)) {
            foreach ($request->anime_ids as $index => $anime_id) {
                $score = $request->score[$index];
                $sortOrder = $request->sort_order[$index];
                $watchStatusId = $request->watch_status_id[$index] ? $request->watch_status_id[$index] : null;
                //Use syncWithoutDetaching to update the pivot data/junction table
                //without removing the user's other rows in the junction table.
                $user->anime()->syncWithoutDetaching([
                    $anime_id => [
                        'score' => $score ? $score : null,
                        'sort_order' => $sortOrder,
                        'watch_status_id' => $watchStatusId
                    ]
                ]);
            }
        }
        return redirect()->route('user.anime.list', ['username' => $username])->with('message', 'Your anime list has been updated!');
    }

    public function updateUserAnimeListV2(Request $request, $username) {
        $anime_ids = $request->input('anime_id');
        $count = count($anime_ids);
        $watch_status_ids = $request->input('watch_status_id');
        $scores = $request->input('score');
        $sort_orders = $request->input('sort_order');

        for ($i = 0; $i < $count; $i++) {
            DB::table('anime_user')->where('user_id', auth()->user()->id)
            ->where('anime_id', $anime_ids[$i])
            ->update([
                'watch_status_id' => $watch_status_ids[$i],
                'score' => $scores[$i] ?? null,
                'sort_order' => $sort_orders[$i] ?? null
            ]);
        }
        return redirect()->back()->with('message', 'Changes saved successfully!');
    }

    public function addToList($id, $redirect = true)
    {
        $user = Auth::user();
        $anime = Anime::findOrFail($id);

        $user->anime()->attach($anime);
        if ($redirect == true) {
            return redirect()->back()->with('message', 'Anime added to your list.');
        }
        return response()->json(['message' => 'Anime added to your list.'], 200);
    }

    public function removeFromList($animeId, $redirect = true)
    {
        $user = Auth::user();
        $user->anime()->detach($animeId);
        if ($redirect == true) {
            return redirect()->back()->with('message', 'Anime removed from your list.');
        }
        return response()->json(['message' => 'Anime removed from your list.'], 200);
    }
}
