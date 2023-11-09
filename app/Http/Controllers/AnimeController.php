<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\User;
use App\Models\WatchStatus;
use App\Services\AnimeListExportService;
use App\Services\AnimeListImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
            END as season_sort');
        $defaultOrder = [
            ['column' => 7, 'dir' => 'desc'],
            ['column' => 8, 'dir' => 'desc']
        ];

        $orderData = $request->has('order') ? $request->input('order') : [];
        //Check if order count matches.
        $sortingMatchesDefault = count($defaultOrder) === count($orderData);

        //Check if all provided order conditions match the default
        foreach ($defaultOrder as $index => $default) {
            if (!isset($orderData[$index]) ||
                $orderData[$index]['column'] != $default['column'] ||
                $orderData[$index]['dir'] != $default['dir']) {
                $sortingMatchesDefault = false;
                break;
            }
        }

        if ($sortingMatchesDefault) {
            $query->orderBy('year', 'desc')->orderBy('season_sort', 'desc');
        }

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
        $watchStatuses = WatchStatus::all()->keyBy('id');

        // Initialize with null or default values
        $currentUserStatus = null;
        $currentUserProgress = null;
        $currentUserScore = null;
        $currentUserSortOrder = null;
        $currentUserNotes = null;
        $currentUserDisplayInList = true;

        $user = auth()->user();
        if($user) {
            // Get the pivot table data for the current user and this anime
            $animeUser = $user->anime()->where('anime_id', $id)->first();

            if ($animeUser) {
                // Pivot data can be accessed using the ->pivot property on the model
                $currentUserStatus = $animeUser->pivot->watch_status_id;
                $currentUserProgress = $animeUser->pivot->progress;
                $currentUserScore = $animeUser->pivot->score;
                $currentUserSortOrder = $animeUser->pivot->sort_order;
                $currentUserNotes = $animeUser->pivot->notes;
                $currentUserDisplayInList = $animeUser->pivot->display_in_list;
                $currentUserShowAnimeNotesPublicly = $animeUser->pivot->show_anime_notes_publicly;
            }
        }

        return view('animedetail', compact('anime', 'watchStatuses', 'currentUserStatus', 'currentUserProgress', 'currentUserScore', 'currentUserSortOrder', 'currentUserNotes', 'currentUserDisplayInList', 'currentUserShowAnimeNotesPublicly'));
    }



    public function topAnime(Request $request)
    {
        $query = Anime::with('anime_type', 'anime_status')->selectRaw('*,
            CASE WHEN season = "UNDEFINED" THEN "UNKNOWN" ELSE season END as season_display,
            CASE season
                WHEN "SPRING" THEN 1
                WHEN "SUMMER" THEN 2
                WHEN "FALL" THEN 3
                WHEN "WINTER" THEN 4
                ELSE 0
            END as season_sort');
        if (auth()->user() == null || auth()->user()->show_adult_content == false) {
            //No need for this to be plain-text, so we'll use rot13.
            $query = $query->where('tags', 'NOT LIKE', '%' . str_rot13('uragnv') . '%');
        }
        $sort = $request->get('sort', 'highest_rated');
        if ($sort === 'highest_rated') {
            $query->orderBy('mal_mean', 'desc');
        } else if ($sort === 'most_popular') {
            $query->orderBy('mal_list_members', 'desc');
        }
        $topAnime = $query->paginate(50);
        $userScores = [];
        if (Auth::check()) {
            $userScores = Auth::user()->anime->pluck('pivot.score', 'pivot.anime_id');
        }
        $watchStatuses = WatchStatus::all()->keyBy('id');
        $userAnimeStatuses = [];
        if (Auth::check()) {
            $userAnimeStatuses = Auth::user()->anime->pluck('pivot.watch_status_id', 'pivot.anime_id');
        }
        return view('topanime', [
            'topAnime' => $topAnime,
            'userScores' => $userScores,
            'watchStatuses' => $watchStatuses,
            'userAnimeStatuses' => $userAnimeStatuses
        ]);
    }

    public function categories() {
        $categories = [
            "Action",
            "Adventure",
            "Avant Garde",
            "Award Winning",
            "Boys Love",
            "Comedy",
            "Drama",
            "Fantasy",
            "Girls Love",
            "Gourmet",
            "Horror",
            "Mystery",
            "Romance",
            "Sci-Fi",
            "Slice of Life",
            "Sports",
            "Supernatural",
            "Suspense",
            "Adult Cast",
            "Anthropomorphic",
            "CGDCT",
            "Childcare",
            "Combat Sports",
            "Crossdressing",
            "Delinquents",
            "Detective",
            "Educational",
            "Gag Humor",
            "Gore",
            "Harem",
            "High Stakes Game",
            "Historical",
            "Idols (Female)",
            "Idols (Male)",
            "Isekai",
            "Iyashikei",
            "Love Polygon",
            "Magical Sex Shift",
            "Mahou Shoujo",
            "Martial Arts",
            "Mecha",
            "Medical",
            "Military",
            "Music",
            "Mythology",
            "Organized Crime",
            "Otaku Culture",
            "Parody",
            "Performing Arts",
            "Pets",
            "Psychological",
            "Racing",
            "Reincarnation",
            "Reverse Harem",
            "Romantic Subtext",
            "Samurai",
            "School",
            "Showbiz",
            "Space",
            "Strategy Game",
            "Super Power",
            "Survival",
            "Team Sports",
            "Time Travel",
            "Vampire",
            "Video Game",
            "Visual Arts",
            "Workplace"
        ];

        return view('categories', compact('categories'));
    }

    public function category(Request $request, $category, $view = 'card') {
        $category = strtolower($category);

        if (!$request->route('view') && auth()->user()) {
            $view = auth()->user()->display_anime_cards ? 'card' : 'list';
        }

        $query = Anime::where(function($query) use ($category) {
            $query->whereRaw('LOWER(tags) LIKE ?', ["%$category%"]);
        });

        $query = $query->selectRaw('*,
            CASE WHEN season = "UNDEFINED" THEN "UNKNOWN" ELSE season END as season_display,
            CASE season
                WHEN "SPRING" THEN 1
                WHEN "SUMMER" THEN 2
                WHEN "FALL" THEN 3
                WHEN "WINTER" THEN 4
                ELSE 0
            END as season_sort'
        );

        $sort = $request->get('sort', 'mal_mean');

        switch ($sort) {
            case 'mal_members':
                $query->orderBy('mal_list_members', 'desc');
                break;
            case 'newest':
                $query->orderBy('year', 'desc')->orderBy('season_sort', 'asc');
                break;
            case 'title':
                $query->orderBy('title', 'asc');
                break;
            default:
                $query->orderBy('mal_mean', 'desc');
                break;
        }

        if (auth()->user() == null || auth()->user()->show_adult_content == false) {
            //No need for this to be plain-text, so we'll use rot13.
            $query = $query->where('tags', 'NOT LIKE', '%' . str_rot13('uragnv') . '%');
        }

        $query->with(['user' => function($query) {
            if (auth()->user()) {
                $query->where('user_id', auth()->user()->id);
            }
        }]);

        $categoryAnime = $query->paginate(50)->appends(['sort' => $sort]);
        $watchStatuses = WatchStatus::all()->keyBy('id');
        return view('category', [
            'categoryAnime' => $categoryAnime,
            'category' => ucfirst($category),
            'viewType' => $view,
            'watchStatuses' => $watchStatuses
        ]);
    }




    public function userAnimeList($username) {
        $user = User::where('username', $username)->firstOrFail();
        $show_anime_list_number = $user->show_anime_list_number;
        $watchStatuses = DB::table('watch_status')->get();
        $watchStatusMap = $watchStatuses->pluck('status', 'id')->toArray();
        $userAnime = $user->anime()
                          ->join('users', 'anime_user.user_id', '=', 'users.id')
                          ->with(['anime_type', 'anime_status'])
                          ->selectRaw('
                              anime.*,
                              anime_user.watch_status_id,
                              anime_user.sort_order,
                              anime_user.score,
                              anime_user.progress,
                              anime_user.display_in_list,
                              anime_user.show_anime_notes_publicly,
                              CASE
                                WHEN anime_user.show_anime_notes_publicly = 1 AND users.show_anime_notes_publicly = 1 THEN  anime_user.notes
                                ELSE NULL
                              END as notes
                         ')
                          ->orderByRaw('ISNULL(sort_order) ASC, sort_order ASC, score DESC, anime_user.created_at ASC')
                          ->where('anime_user.display_in_list', '=', 1)
                          ->paginate($user->anime_list_pagination_size ?? 15);

        return view('userAnimeList', ['userAnime' => $userAnime, 'username' => $username, 'show_anime_list_number' => $show_anime_list_number, 'watchStatuses' => $watchStatuses, 'watchStatusMap' => $watchStatusMap]);
    }

    public function userAnimeListV2($username)
    {
        $user = User::where('username', $username)->firstOrFail();
        $show_anime_list_number = $user->show_anime_list_number;
        $watchStatuses = WatchStatus::all();
        $watchStatusMap = $watchStatuses->pluck('status', 'id')->toArray();
        $userAnimeCount = $user->anime()
                          ->with(['anime_type', 'anime_status', 'watch_status'])->count();

        return view('userAnimeListV2', [
            'username' => $username,
            'watchStatuses' => $watchStatuses,
            'watchStatusMap' => $watchStatusMap,
            'userAnimeCount' => $userAnimeCount,
            'show_anime_list_number' => $show_anime_list_number
        ]);
    }

    public function getUserAnimeDataV2($username, Request $request)
    {
        $user = User::where('username', $username)->firstOrFail();
        $query = $user->anime()
          ->join('users', 'anime_user.user_id', '=', 'users.id')
          ->with(['anime_type', 'anime_status', 'watch_status'])
          ->where('anime_user.display_in_list', '=', 1)
          ->selectRaw('
              anime.*,
              anime_user.watch_status_id,
              anime_user.sort_order,
              anime_user.score,
              anime_user.progress,
              anime_user.display_in_list,
              anime_user.show_anime_notes_publicly,
              CASE
                WHEN anime_user.show_anime_notes_publicly = 1 AND users.show_anime_notes_publicly = 1 THEN anime_user.notes
                ELSE NULL
              END as notes
          ');

        $defaultOrder = [
            ['column' => 7, 'dir' => 'asc'],
            ['column' => 6, 'dir' => 'asc'],
            ['column' => 1, 'dir' => 'asc']
        ];

        $orderData = $request->has('order') ? $request->input('order') : [];
        //Check if order count matches.
        $sortingMatchesDefault = count($defaultOrder) === count($orderData);

        //Check if all provided order conditions match the default
        foreach ($defaultOrder as $index => $default) {
            if (!isset($orderData[$index]) ||
                $orderData[$index]['column'] != $default['column'] ||
                $orderData[$index]['dir'] != $default['dir']) {
                $sortingMatchesDefault = false;
                break;
            }
        }
        //TODO: fix non-default sorting, for example sorting by episodes doesn't work. Maybe add in sorting manually?
        if ($sortingMatchesDefault) {
            $query->orderByRaw('ISNULL(sort_order) ASC, sort_order ASC, score DESC, anime_user.created_at ASC');
        }

        return DataTables::of($query)
            ->addColumn('anime_id', function ($row) {
                return $row->id;
            })
            ->make(true);
    }

    public function updateUserAnimeList(Request $request, $username, $redirectBack = false) {
        $user = User::where('username', $username)->firstOrFail();

        if ($request->has('anime_ids') && is_array($request->anime_ids)) {
            foreach ($request->anime_ids as $index => $anime_id) {
                $anime = Anime::find($anime_id);
                $currentPivotData = $user->anime()->where('anime_id', $anime_id)->first()->pivot;
                $currentDisplayInList = $currentPivotData->display_in_list ?? true;
                $currentShowAnimeNotesPublicly = $currentPivotData->show_anime_notes_publicly ?? true;
                $currentNotes = $currentPivotData->notes ?? null;
                $score = isset($request->score[$index]) ? $request->score[$index] : null;
                $sortOrder = isset($request->sort_order[$index]) ? $request->sort_order[$index] : null;
                $watchStatusId = $request->watch_status_id[$index] ? $request->watch_status_id[$index] : null;
                $progress = $request->progress[$index] ?? 0;
                if ($watchStatusId == WatchStatus::where('status', 'COMPLETED')->first()->id) {
                    $progress = $anime->episodes;
                } else if ($watchStatusId == WatchStatus::where('status', 'PLAN-TO-WATCH')->first()->id) {
                    $progress = 0;
                }
                $displayInList = $request->display_in_list[$index] ?? $currentDisplayInList;
                $showAnimeNotesPublicly = $request->show_anime_notes_publicly[$index] ?? $currentShowAnimeNotesPublicly;

                $syncData = [
                    $anime_id => [
                        'score' => $score ? $score : null,
                        'sort_order' => $sortOrder,
                        'watch_status_id' => $watchStatusId,
                        'progress' => $progress,
                        'display_in_list' => $displayInList,
                        'show_anime_notes_publicly' => $showAnimeNotesPublicly,
                    ]
                ];

                //Add notes to the sync array only if it's provided in the request
                if (array_key_exists('notes', $request->all())) {
                    $syncData[$anime_id]['notes'] = $request->notes[$index] ?? "";
                }
                //Use syncWithoutDetaching to update the pivot data/junction table
                //without removing the user's other rows in the junction table.
                $user->anime()->syncWithoutDetaching($syncData);
            }
        }
        if ($redirectBack) {
            return redirect()->back()->with('popup', 'Your anime list has been updated!');
        }
        return redirect()->route('user.anime.list', ['username' => $username])->with('message', 'Your anime list has been updated!');
    }

    public function updateUserAnimeListV2(Request $request, $username) {
        $anime_ids = $request->input('anime_id');
        $count = count($anime_ids);
        $watch_status_ids = $request->input('watch_status_id');
        $scores = $request->input('score');
        $sort_orders = $request->input('sort_order');
        $progresses = $request->input('progress');

        for ($i = 0; $i < $count; $i++) {
            $anime = Anime::find($anime_ids[$i]);
            $progress = $progresses[$i] ?? 0;
            if ($watch_status_ids[$i] == WatchStatus::where('status', 'COMPLETED')->first()->id) {
                $progress = $anime->episodes;
            } else if ($watch_status_ids[$i] == WatchStatus::where('status', 'PLAN-TO-WATCH')->first()->id) {
                $progress = 0;
            }
            DB::table('anime_user')->where('user_id', auth()->user()->id)
            ->where('anime_id', $anime_ids[$i])
            ->update([
                'watch_status_id' => $watch_status_ids[$i],
                'score' => $scores[$i] ?? null,
                'sort_order' => $sort_orders[$i] ?? null,
                'progress' => $progress
            ]);
        }
        return redirect()->back()->with('message', 'Changes saved successfully!');
    }

    public function updateAnimeStatus(Request $request, $username)
    {
        $user = User::where('username', $username)->firstOrFail();

        $anime_id = $request->input('anime_id');
        $watchStatusId = $request->input('watch_status_id');

        // If watchStatusId is 0, we remove the anime from the list
        if ($watchStatusId == 0) {
            $user->anime()->detach($anime_id);
            return response()->json(['message' => 'Removed from list']);
        }

        $anime = Anime::find($anime_id);

        if (!$anime) {
            return response()->json(['message' => 'Anime not found'], 404);
        }

        // Otherwise, insert or update the anime status
        $progress = $request->input('progress', 0);

        if ($watchStatusId == WatchStatus::where('status', 'COMPLETED')->first()->id) {
            $progress = $anime->episodes;
        } elseif ($watchStatusId == WatchStatus::where('status', 'PLAN-TO-WATCH')->first()->id) {
            $progress = 0;
        }

        // Use syncWithoutDetaching to update the pivot data/junction table
        // without removing the user's other rows in the junction table.
        $user->anime()->syncWithoutDetaching([
            $anime_id => [
                'watch_status_id' => $watchStatusId,
                'progress' => $progress
            ]
        ]);

        return response()->json(['message' => 'Your anime status has been updated']);
    }

    public function addToList($id, $redirect = true)
    {
        $user = Auth::user();
        $anime = Anime::findOrFail($id);

        $user->anime()->attach($anime, ['watch_status_id' => WatchStatus::where('status', 'PLAN-TO-WATCH')->first()->id]);
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

    public function clearAnimeList($username)
    {
        $user = User::where('username', $username)->firstOrFail();

        //Check if the logged-in user matches the username or is an admin
        if (auth()->user()->id === $user->id || auth()->user()->is_admin) {
            $user->anime()->detach();

            return redirect()->back()->with('message', 'Anime list cleared.');
        } else {
            return redirect()->back()->with('error', 'You do not have permission to clear this anime list.');
        }
    }

    public function importAnimeList(Request $request, AnimeListImportService $importer)
    {
        $importType = $request->input('import_type');
        $fileKey = 'anime_data_file';

        $commonRules = [
            $fileKey => 'required',
            'import_type' => 'required|in:myanimelist,arigatou',
        ];

        if ($importType === 'myanimelist') {
            $commonRules[$fileKey] = 'required|mimes:xml';
        } elseif ($importType === 'arigatou') {
            $commonRules[$fileKey] = 'required|mimes:json';
        }

        $request->validate($commonRules);

        $fileContent = file_get_contents($request->file($fileKey)->path());
        $userId = Auth::id();
        $result = $importer->import($fileContent, $importType, $userId);

        $duration = round($result['duration'], 2);
        return redirect()->back()->with('message', "Imported {$result['count']} out of {$result['total']} anime records successfully in {$duration} seconds");
    }

    public function importAnimeListView() {
        return view('importanimelist');
    }

    public function exportAnimeList(Request $request, AnimeListExportService $exporter)
    {
        $exportType = $request->input('export_type');

        // Validation
        $request->validate([
            'export_type' => 'required|in:myanimelist,arigatou',
        ]);

        $userId = Auth::id();
        $result = $exporter->export($exportType, $userId);

        // Generate filename with date
        $currentDateTime = Carbon::now()->format('Y-m-d_H-i-s');
        $fileNameBase = "AnimeList_{$currentDateTime}";

        if ($exportType === 'myanimelist') {
            $fileName = "{$fileNameBase}.xml";
            Storage::put('exports/'.$fileName, $result['output']);
        } elseif ($exportType === 'arigatou') {
            $fileName = "{$fileNameBase}.json";
            Storage::put('exports/'.$fileName, $result['output']);
        } else {
            return redirect()->back()->with('message', 'Export failed due to unknown file type.');
        }

        return response()->download(storage_path("app/exports/{$fileName}"));
    }

    public function exportAnimeListView()
    {
        return view('exportanimelist');
    }
}
