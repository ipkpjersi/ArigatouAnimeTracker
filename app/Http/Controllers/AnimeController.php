<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\AnimeReview;
use App\Models\AnimeType;
use App\Models\User;
use App\Models\WatchStatus;
use App\Services\AnimeListExportService;
use App\Services\AnimeListImportService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class AnimeController extends Controller
{
    public function getAnimeData(Request $request)
    {
        if (! request()->has(['start', 'length']) || request()->input('length') > 1000) {
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
            ['column' => 8, 'dir' => 'desc'],
        ];

        $orderData = $request->has('order') ? $request->input('order') : [];
        // Check if order count matches.
        $sortingMatchesDefault = count($defaultOrder) === count($orderData);

        // Check if all provided order conditions match the default
        foreach ($defaultOrder as $index => $default) {
            if (! isset($orderData[$index]) ||
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
            // No need for this to be plain-text, so we'll use rot13.
            $query = $query->where('tags', 'NOT LIKE', '%'.str_rot13('uragnv').'%');
        }

        return DataTables::of($query)
            ->filterColumn('season', function ($query, $keyword) {
                if (strtoupper($keyword) === 'UNKNOWN') {
                    $query->orWhere('season', 'UNDEFINED');
                } elseif (in_array(strtoupper($keyword), ['WINTER', 'SPRING', 'SUMMER', 'FALL'])) {
                    $query->orWhere('season', strtoupper($keyword));
                }
            })
            ->filterColumn('tags', function ($query, $keyword) {
                // We could add a junction table for anime and tags, but this is probably fine.
                $searchTags = collect(explode(',', $keyword))
                    ->map(fn ($tag) => trim(strtolower($tag)));
                foreach ($searchTags as $tag) {
                    $query->whereRaw('LOWER(tags) LIKE ?', ["%$tag%"]);
                }
            })
            ->filterColumn('synonyms', function ($query, $keyword) {
                // We could add a junction table for anime and synonyms, but this is probably fine.
                $searchTags = collect(explode(',', $keyword))
                    ->map(fn ($synonym) => trim(strtolower($synonym)));
                foreach ($searchTags as $synonym) {
                    $query->whereRaw('LOWER(synonyms) LIKE ?', ["%$synonym%"]);
                }
            })
            ->make(true);
    }

    public function list()
    {
        return view('animelist');
    }

    public function detail($id, $title = null)
    {
        $anime = Anime::with('anime_type', 'anime_status')->findOrFail($id);
        if ($anime->season === 'UNDEFINED') {
            $anime->season = 'UNKNOWN';
        }
        $watchStatuses = WatchStatus::all()->keyBy('id');

        // Initialize with null or default values
        $currentUserStatus = null;
        $currentUserProgress = null;
        $currentUserScore = null;
        $currentUserSortOrder = null;
        $currentUserNotes = null;
        $currentUserDisplayInList = true;
        $currentUserShowAnimeNotesPublicly = true;
        $favouriteSystemEnabled = false;
        $favourite = null;

        $user = auth()->user();
        $userHasReview = false;
        $userReview = null;
        if ($user) {
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
            $userReview = AnimeReview::where('anime_id', $id)
                ->where('user_id', $user->id)
                ->first();
            $userHasReview = $userReview != null;
            $favouriteSystemEnabled = auth()->user()->enable_favourites_system;
            $favourite = $favouriteSystemEnabled ? auth()->user()->favourites->where('id', $id)->first() : null;
        }

        $reviews = AnimeReview::where('anime_reviews.anime_id', $id)
            ->join('users', 'anime_reviews.user_id', '=', 'users.id')
            ->where('anime_reviews.show_review_publicly', true)
            ->when(! request('spoilers'), function ($query) {
                return $query->where('anime_reviews.contains_spoilers', false);
            })
            ->where('users.show_reviews_publicly', true)
            ->where('users.is_banned', false)
            ->where('anime_reviews.is_deleted', 0)
            ->latest('anime_reviews.created_at')
            ->paginate(2, ['anime_reviews.*', 'users.username', 'users.avatar', 'users.id as user_id'], 'reviewpage')
            ->withQueryString(); // We could manually appends() instead of using withQueryString() but withQueryString() is simpler.

        $totalReviewsCount = AnimeReview::where('anime_id', $id)
            ->join('users', 'anime_reviews.user_id', '=', 'users.id')
            ->where('users.show_reviews_publicly', true)
            ->where('anime_reviews.show_review_publicly', true)
            ->where('anime_reviews.is_deleted', 0)
            ->where('users.is_banned', false)->count();

        $aatScore = DB::table('anime_user')
            ->where('anime_id', $id)
            ->whereNotNull('score')
            ->where('score', '>', 0)
            ->avg('score');
        $aatScore = round($aatScore, 2);

        $aatMembers = DB::table('anime_user')
            ->where('anime_id', $id)
            ->count();

        $aatUsers = DB::table('anime_user')
            ->where('anime_id', $id)
            ->whereNotNull('score')
            ->count();

        $otherAnimeTags = [
            'Action', 'Adventure', 'Comedy', 'Drama', 'Fantasy', 'Horror', 'Mystery', 'Romance', 'Sci-Fi',
            'Slice of Life', 'Supernatural', 'Thriller', 'Romantic Comedy', 'Coming of Age', 'School', 'Sports',
            'Magic', 'Military', 'Mecha', 'Music', 'Historical', 'Psychological', 'Battle Royale',
            'Isekai', 'Post-Apocalyptic', 'Space', 'Time Travel', 'Virtual Reality', 'Superpower', 'Cyberpunk',
            'Mystery', 'Harem', 'Reverse Harem', 'Tsundere', 'Yandere', 'Parody', 'Ojou-Sama', 'Maids',
        ];

        $currentAnimeTags = array_map('strtolower', explode(', ', $anime->tags));
        $otherAnimeTags = array_map('strtolower', $otherAnimeTags);
        $filteredTags = array_intersect($currentAnimeTags, $otherAnimeTags);
        $otherAnime = [];
        if (! empty($filteredTags)) {
            $tagConditions = array_map(function ($tag) {
                return "tags LIKE '%".$tag."%'";
            }, $filteredTags);

            $tagConditions = implode(' OR ', $tagConditions);

            $otherAnime = DB::table('anime')
                ->select('anime.*', DB::raw('(
                    '.implode(' + ', array_map(function ($tag) {
                    return "IF(tags LIKE '%".$tag."%', 1, 0)";
                }, $filteredTags)).'
                ) as match_count'))
                ->where('id', '!=', $id)
                ->whereRaw("($tagConditions)")
                ->orderBy('match_count', 'desc')
                ->limit(500)
                ->get();

            $page = LengthAwarePaginator::resolveCurrentPage('otheranimepage');
            $perPage = 5;
            $offset = ($page * $perPage) - $perPage;
            $paginatedItems = $otherAnime->slice($offset, $perPage)->values();
            $otherAnime = new LengthAwarePaginator($paginatedItems, $otherAnime->count(), $perPage, $page, [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'otheranimepage',
            ]);
        }

        return view('animedetail', compact('anime', 'watchStatuses', 'currentUserStatus', 'currentUserProgress', 'currentUserScore', 'currentUserSortOrder', 'currentUserNotes', 'currentUserDisplayInList', 'currentUserShowAnimeNotesPublicly', 'reviews', 'userHasReview', 'userReview', 'totalReviewsCount', 'aatScore', 'aatMembers', 'aatUsers', 'otherAnime', 'favouriteSystemEnabled', 'favourite'));
    }

    public function addReview(Request $request)
    {
        $validatedData = $request->validate([
            'anime_id' => 'required|exists:anime,id',
            'title' => 'nullable|string|max:255',
            'body' => 'required|string',
            'contains_spoilers' => 'boolean',
            'show_review_publicly' => 'boolean',
            'recommendation' => 'required|in:recommended,mixed,not_recommended',
        ]);

        $review = new AnimeReview($validatedData);
        $review->user_id = auth()->id();
        $review->contains_spoilers = $request->has('contains_spoilers');
        $review->show_review_publicly = $request->has('show_review_publicly');
        $review->save();

        return redirect()->back()->with('reviewmessage', 'Review added successfully!');
    }

    public function updateReview(Request $request, $id)
    {
        $validatedData = $request->validate([
            'title' => 'nullable|string|max:255',
            'body' => 'required|string',
            'contains_spoilers' => 'boolean',
            'show_review_publicly' => 'boolean',
            'recommendation' => 'required|in:recommended,mixed,not_recommended',
        ]);

        $review = AnimeReview::where('anime_id', $id)->where('user_id', auth()->id())->firstOrFail();
        $review->contains_spoilers = $request->has('contains_spoilers');
        $review->show_review_publicly = $request->has('show_review_publicly');
        $review->update($validatedData);

        return redirect()->back()->with('reviewmessage', 'Review updated successfully!');
    }

    public function deleteReview($id)
    {
        $review = AnimeReview::where('anime_id', $id)->where('user_id', auth()->id())->firstOrFail();
        $review->delete();

        return redirect()->back()->with('reviewmessage', 'Review deleted successfully!');
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
            // No need for this to be plain-text, so we'll use rot13.
            $query = $query->where('tags', 'NOT LIKE', '%'.str_rot13('uragnv').'%');
        }
        $sort = $request->get('sort', 'highest_rated');
        if ($sort === 'highest_rated') {
            $query->orderBy('mal_mean', 'desc');
        } elseif ($sort === 'most_popular') {
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
            'userAnimeStatuses' => $userAnimeStatuses,
        ]);
    }

    public function categories()
    {
        $categories = Anime::categoriesList();

        return view('categories', compact('categories'));
    }

    public function category(Request $request, $category, $view = 'card')
    {
        $category = strtolower($category);
        $isSeasonal = $category === 'seasonal';
        $selectedCategories = $request->get('categories', []);
        $allCategories = Anime::categoriesList();

        if (!$request->route('view') && auth()->user()) {
            $view = auth()->user()->display_anime_cards ? 'card' : 'list';
        }

        // Determine current season & year
        $currentDate = Carbon::now();
        $currentYear = $request->get('year') ?? $currentDate->year;
        $currentSeason = strtoupper($request->get('season') ?? $this->getCurrentSeason($currentDate));
        $calendarYear = $currentDate->year;
        $calendarSeason = $this->getCurrentSeason($currentDate);

        $animeTypeId = $request->get('type');

        $query = Anime::query();

        if ($animeTypeId) {
            $query->where('anime_type_id', $animeTypeId);
        }

        if ($isSeasonal || $category === 'all') {
            if ($isSeasonal) {
                $query->where('season', $currentSeason)->where('year', $currentYear);
            }
            if ($selectedCategories) {
                if (is_string($selectedCategories)) {
                    $selectedCategories = explode(',', $selectedCategories);
                }
                foreach ($selectedCategories as $cat) {
                    $query->whereRaw('LOWER(tags) LIKE ?', ['%' . strtolower($cat) . '%']);
                }
            }
        } else {
            $query->whereRaw('LOWER(tags) LIKE ?', ["%$category%"]);
        }

        $query->selectRaw('*, CASE WHEN season = "UNDEFINED" THEN "UNKNOWN" ELSE season END as season_display')
              ->addSelect(DB::raw("CASE season
                  WHEN 'SPRING' THEN 1
                  WHEN 'SUMMER' THEN 2
                  WHEN 'FALL' THEN 3
                  WHEN 'WINTER' THEN 4
                  ELSE 0
              END as season_sort"));

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

        if (auth()->guest() || auth()->user()->show_adult_content == false) {
            $query->where('tags', 'NOT LIKE', '%'.str_rot13('uragnv').'%');
        }

        $query->with(['user' => function ($query) {
            if (auth()->user()) {
                $query->where('user_id', auth()->user()->id);
            }
        }]);

        $categoryAnime = $query->paginate(50)->appends($request->query());
        $watchStatuses = WatchStatus::all()->keyBy('id');
        $animeTypes = AnimeType::all();

        // For seasonal view: compute previous and next season/year
        $paginationSeasons = $isSeasonal ? $this->getSeasonPagination($currentSeason, $currentYear) : null;

        return view('category', compact(
            'categoryAnime',
            'category',
            'view',
            'watchStatuses',
            'animeTypes',
            'isSeasonal',
            'currentSeason',
            'currentYear',
            'animeTypeId',
            'paginationSeasons',
            'calendarSeason',
            'calendarYear',
            'allCategories'
        ));
    }

    private function getCurrentSeason($date): string
    {
        $month = $date->month;
        return match (true) {
            $month >= 3 && $month <= 5 => 'SPRING',
            $month >= 6 && $month <= 8 => 'SUMMER',
            $month >= 9 && $month <= 11 => 'FALL',
            default => 'WINTER',
        };
    }

    private function getSeasonPagination($season, $year)
    {
        $seasons = ['WINTER', 'SPRING', 'SUMMER', 'FALL'];
        $currentIndex = array_search($season, $seasons);

        $prevIndex = ($currentIndex - 1 + 4) % 4;
        $nextIndex = ($currentIndex + 1) % 4;

        $prevSeason = $seasons[$prevIndex];
        $nextSeason = $seasons[$nextIndex];

        $prevYear = ($prevSeason === 'FALL' && $season === 'WINTER') ? $year - 1 : $year;
        $nextYear = ($nextSeason === 'WINTER' && $season === 'FALL') ? $year + 1 : $year;

        return [
            'prev' => ['season' => $prevSeason, 'year' => $prevYear],
            'current' => ['season' => $season, 'year' => $year],
            'next' => ['season' => $nextSeason, 'year' => $nextYear],
        ];
    }

    public function userAnimeList(Request $request, $username)
    {
        $user = User::where('username', $username)->firstOrFail();
        if ($user->is_banned === 1 && (Auth::user() === null || Auth::user()->is_admin !== 1)) {
            abort(404);
        }
        $showAllAnime = false;
        if ($request->has('showallanime') && $request->input('showallanime') === '1' && Auth::user() !== null && strtolower(Auth::user()->username) === strtolower($user->username)) {
            $showAllAnime = true;
        }
        $pagination = Auth::user()?->anime_list_pagination_size ?? $user->anime_list_pagination_size ?? 15;
        if ($request->has('size') || $request->has('pageSize') || $request->has('pagesize')) {
            $pagination = $request->get('size') ?? $request->get('pageSize') ?? $request->get('pagesize');
            $pagination = max(1, min((int) $pagination, 1000)); // Clamp between 1 and 1000
        }
        $show_anime_list_number = Auth::user() != null && Auth::user()->show_anime_list_number == 1;
        $watchStatuses = DB::table('watch_status')->get();
        $watchStatusMap = $watchStatuses->pluck('status', 'id')->toArray();
        $query = $user->anime()
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
                                WHEN (anime_user.show_anime_notes_publicly = 1 AND users.show_anime_notes_publicly = 1) OR anime_user.user_id = ? THEN anime_user.notes
                                ELSE NULL
                              END as notes
                         ', [Auth::user()->id ?? -1])
            ->orderByRaw('ISNULL(sort_order) ASC, sort_order ASC, score DESC, anime_user.created_at ASC');
        if (! $showAllAnime) {
            $query = $query->where('anime_user.display_in_list', '=', 1);
        }
        if ($user->show_anime_list_publicly === 0 && (Auth::user() === null || strtolower(Auth::user()->username) !== strtolower($user->username))) {
            $query = $query->where('anime_user.display_in_list', '=', Anime::$HIDE_ALL_ANIME_PUBLICLY_ID);
        }
        if ($request->has('status')) {
            $status = $request->input('status');
            $watchStatusId = array_search(strtoupper($status), $watchStatusMap);
            if ($watchStatusId !== false) {
                $query = $query->where('anime_user.watch_status_id', $watchStatusId);
            }
        }
        $userAnime = $query->paginate($pagination ?? 15)->withQueryString();

        return view('userAnimeList', ['userAnime' => $userAnime, 'username' => $username, 'show_anime_list_number' => $show_anime_list_number, 'watchStatuses' => $watchStatuses, 'watchStatusMap' => $watchStatusMap]);
    }

    public function userAnimeListV2($username)
    {
        $user = User::where('username', $username)->firstOrFail();
        if ($user->is_banned === 1 && (Auth::user() === null || Auth::user()->is_admin !== 1)) {
            abort(404);
        }
        $show_anime_list_number = Auth::user() != null && Auth::user()->show_anime_list_number == 1;
        $watchStatuses = WatchStatus::all();
        $watchStatusMap = $watchStatuses->pluck('status', 'id')->toArray();
        $userAnimeCount = $user->anime()
            ->with(['anime_type', 'anime_status', 'watch_status'])->count();

        return view('userAnimeListV2', [
            'username' => $username,
            'watchStatuses' => $watchStatuses,
            'watchStatusMap' => $watchStatusMap,
            'userAnimeCount' => $userAnimeCount,
            'show_anime_list_number' => $show_anime_list_number,
        ]);
    }

    public function getUserAnimeDataV2($username, Request $request)
    {
        $user = User::where('username', $username)->firstOrFail();
        $showAllAnime = false;
        if ($request->has('showallanime') && $request->input('showallanime') === '1' && Auth::user() !== null && strtolower(Auth::user()->username) === strtolower($user->username)) {
            $showAllAnime = true;
        }
        $query = $user->anime()
            ->join('users', 'anime_user.user_id', '=', 'users.id')
            ->with(['anime_type', 'anime_status', 'watch_status'])
            ->selectRaw('
              anime.*,
              anime_user.watch_status_id,
              anime_user.sort_order,
              anime_user.score,
              anime_user.progress,
              anime_user.display_in_list,
              anime_user.show_anime_notes_publicly,
              CASE
                WHEN (anime_user.show_anime_notes_publicly = 1 AND users.show_anime_notes_publicly = 1) OR anime_user.user_id = ? THEN anime_user.notes
                ELSE NULL
              END as notes
          ', [Auth::user()->id ?? -1]);
        if (! $showAllAnime) {
            $query = $query->where('anime_user.display_in_list', '=', 1);
        }
        if ($user->show_anime_list_publicly === 0 && (Auth::user() === null || strtolower(Auth::user()->username) !== strtolower($user->username))) {
            $query = $query->where('anime_user.display_in_list', '=', Anime::$HIDE_ALL_ANIME_PUBLICLY_ID);
        }
        // We need to match the order we have in DataTables frontend.
        if (Auth::user() != null && Auth::user()->show_anime_list_number == 1) {
            $defaultOrder = [
                ['column' => 8, 'dir' => 'asc'],
                ['column' => 7, 'dir' => 'asc'],
                ['column' => 1, 'dir' => 'asc'],
            ];
        } else {
            $defaultOrder = [
                ['column' => 7, 'dir' => 'asc'],
                ['column' => 6, 'dir' => 'asc'],
                ['column' => 0, 'dir' => 'asc'],
            ];
        }

        $orderData = $request->has('order') ? $request->input('order') : [];
        // Check if order count matches.
        $sortingMatchesDefault = count($defaultOrder) === count($orderData);

        // Check if all provided order conditions match the default
        foreach ($defaultOrder as $index => $default) {
            if (! isset($orderData[$index]) ||
                $orderData[$index]['column'] != $default['column'] ||
                $orderData[$index]['dir'] != $default['dir']) {
                $sortingMatchesDefault = false;
                break;
            }
        }
        // TODO: fix non-default sorting, for example sorting by episodes doesn't work. Maybe add in sorting manually?
        if ($sortingMatchesDefault) {
            $query->orderByRaw('ISNULL(sort_order) ASC, sort_order ASC, score DESC, anime_user.created_at ASC');
        }
        if ($request->has('status')) {
            $status = $request->input('status');
            $watchStatusId = array_search(strtoupper($status), WatchStatus::all()->pluck('status', 'id')->toArray());
            if ($watchStatusId !== false) {
                $query = $query->where('anime_user.watch_status_id', $watchStatusId);
            }
        }

        return DataTables::of($query)
            ->addColumn('anime_id', function ($row) {
                return $row->id;
            })
            ->make(true);
    }

    /**
     * Used for updating both the v1 of the Anime User List page and also the individual Anime Detail page.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateUserAnimeList(Request $request, $username, $redirectBack = false)
    {
        $user = User::where('username', $username)->firstOrFail();

        if ($request->has('anime_ids') && is_array($request->anime_ids)) {
            // Is the user updating from the anime detail page (or possibly from the list page if their list has only one entry)?
            $updatingFromAnimeDetailPage = count($request->anime_ids) === 1;
            if ($updatingFromAnimeDetailPage) {
                $animeId = $request->anime_ids[0];
                $sortOrder = $request->sort_order[0] ?? null;
                // Check if the user has the setting enabled and a sort_order is provided
                if ($user->modifying_sort_order_on_detail_page_sorts_entire_list && $sortOrder !== null) {
                    $currentEntry = $user->anime()->where('anime_id', $animeId)->first();
                    $currentSortOrder = $currentEntry ? $currentEntry->pivot->sort_order : null;
                    if ($currentSortOrder === null) {
                        // New entry, insert and shift existing entries
                        $user->anime()->where('sort_order', '>=', $sortOrder)->increment('sort_order');
                    } else {
                        if ($currentSortOrder < $sortOrder) {
                            // Moving downwards
                            $user->anime()->whereBetween('sort_order', [$currentSortOrder + 1, $sortOrder])
                                ->decrement('sort_order');
                        } elseif ($currentSortOrder > $sortOrder) {
                            // Moving upwards
                            $user->anime()->whereBetween('sort_order', [$sortOrder, $currentSortOrder - 1])
                                ->increment('sort_order');
                        }
                    }
                }
            }
            foreach ($request->anime_ids as $index => $anime_id) {
                $anime = Anime::find($anime_id);
                $currentPivotData = $user->anime()->where('anime_id', $anime_id)->first()->pivot;
                $currentDisplayInList = $currentPivotData->display_in_list ?? true;
                $currentShowAnimeNotesPublicly = $currentPivotData->show_anime_notes_publicly ?? true;
                $score = isset($request->score[$index]) ? $request->score[$index] : null;
                $sortOrder = isset($request->sort_order[$index]) ? $request->sort_order[$index] : null;
                $watchStatusId = $request->watch_status_id[$index] ? $request->watch_status_id[$index] : null;
                $progress = $request->progress[$index] ?? 0;
                if ($watchStatusId == WatchStatus::where('status', 'COMPLETED')->first()->id) {
                    $progress = $anime->episodes;
                } elseif ($watchStatusId == WatchStatus::where('status', 'PLAN-TO-WATCH')->first()->id) {
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
                    ],
                ];

                // Add notes to the sync array only if it's provided in the request
                if (array_key_exists('notes', $request->all())) {
                    $syncData[$anime_id]['notes'] = $request->notes[$index] ?? '';
                }
                // Use syncWithoutDetaching to update the pivot data/junction table
                // without removing the user's other rows in the junction table.
                $user->anime()->syncWithoutDetaching($syncData);
            }
        }
        if ($redirectBack) {
            return redirect()->back()->with('popup', 'Your anime list has been updated!');
        }

        return redirect()->route('user.anime.list', ['username' => $username])->with('message', 'Your anime list has been updated!');
    }

    /**
     * Used only for updating the Anime User List v2 page.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateUserAnimeListV2(Request $request, $username)
    {
        $anime_ids = $request->input('anime_id');
        $count = count($anime_ids);
        $watch_status_ids = $request->input('watch_status_id');
        $scores = $request->input('score');
        $sort_orders = $request->input('sort_order');
        $progresses = $request->input('progress');
        $notes = $request->input('notes');

        for ($i = 0; $i < $count; $i++) {
            $anime = Anime::find($anime_ids[$i]);
            $progress = $progresses[$i] ?? 0;
            if ($watch_status_ids[$i] == WatchStatus::where('status', 'COMPLETED')->first()->id) {
                $progress = $anime->episodes;
            } elseif ($watch_status_ids[$i] == WatchStatus::where('status', 'PLAN-TO-WATCH')->first()->id) {
                $progress = 0;
            }
            DB::table('anime_user')->where('user_id', auth()->user()->id)
                ->where('anime_id', $anime_ids[$i])
                ->update([
                    'watch_status_id' => $watch_status_ids[$i],
                    'score' => $scores[$i] ?? null,
                    'sort_order' => $sort_orders[$i] ?? null,
                    'progress' => $progress,
                    'notes' => $notes[$i] ?? null,
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

        if (! $anime) {
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
                'progress' => $progress,
            ],
        ]);

        return response()->json(['message' => 'Your anime status has been updated']);
    }

    public function addToList($id, $redirect = true)
    {
        $user = Auth::user();
        $anime = Anime::findOrFail($id);

        $user->anime()->attach($anime, ['watch_status_id' => WatchStatus::where('status', 'PLAN-TO-WATCH')->first()->id]);
        if ($redirect == true) {
            return redirect()->back()->with('popup', 'Anime added to your list.');
        }

        return response()->json(['popup' => 'Anime added to your list.'], 200);
    }

    public function removeFromList($animeId, $redirect = true)
    {
        $user = Auth::user();
        $user->anime()->detach($animeId);
        if ($redirect == true) {
            return redirect()->back()->with('popup', 'Anime removed from your list.');
        }

        return response()->json(['popup' => 'Anime removed from your list.'], 200);
    }

    public function clearAnimeList($username)
    {
        $user = User::where('username', $username)->firstOrFail();

        // Check if the logged-in user matches the username or is an admin
        if (auth()->user()->id === $user->id || auth()->user()->is_admin) {
            $user->anime()->detach();

            return redirect()->back()->with('message', 'Anime list cleared.');
        } else {
            return redirect()->back()->with('error', 'You do not have permission to clear this anime list.');
        }
    }

    public function clearAnimeListSortOrders($username)
    {
        $user = User::where('username', $username)->firstOrFail();

        // Check if the logged-in user matches the username or is an admin
        if (auth()->user()->id === $user->id || auth()->user()->is_admin) {
            // Assuming 'anime' is the name of the relationship and 'anime_user' is the pivot table
            // Here, we update the 'sort_order' field to null for all anime list entries of the user
            $user->anime()->updateExistingPivot($user->anime->pluck('id'), ['sort_order' => null]);

            return redirect()->back()->with('message', 'Anime list sort orders cleared.');
        } else {
            return redirect()->back()->with('error', 'You do not have permission to clear these sort orders.');
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

    public function importAnimeListView()
    {
        return view('importanimelist');
    }

    public function exportAnimeList(Request $request, AnimeListExportService $exporter)
    {
        $exportType = $request->input('export_type');

        // Validation
        $request->validate([
            'export_type' => 'required|in:myanimelist,arigatou,myanimelistcss',
        ]);

        $userId = Auth::id();
        $username = Auth::user()->username;
        $result = $exporter->export($exportType, $userId);

        // Generate filename with date
        $currentDateTime = Carbon::now()->format('Y-m-d_H-i-s');
        $fileNameBase = "AnimeList_{$username}_{$currentDateTime}";

        if ($exportType === 'myanimelist') {
            $fileName = "{$fileNameBase}.xml";
            Storage::put('exports/'.$fileName, $result['output']);
        } elseif ($exportType === 'arigatou') {
            $fileName = "{$fileNameBase}.json";
            Storage::put('exports/'.$fileName, $result['output']);
        } elseif ($exportType === 'myanimelistcss') {
            $fileName = "{$fileNameBase}.css";
            Storage::put('exports/'.$fileName, $result['output']);
        } else {
            return redirect()->back()->with('message', 'Export failed due to unknown file type.');
        }

        return response()->download(storage_path("app/private/exports/{$fileName}"));
    }

    public function exportAnimeListView()
    {
        return view('exportanimelist');
    }

    public function testLocalImageUrl($id)
    {
        $anime = Anime::find($id);

        if (!$anime) {
            return response()->json(['error' => 'Anime not found'], 404);
        }

        return response()->json([
            'local_picture_url' => $anime->getLocalPictureUrl(),
            'local_thumbnail_url' => $anime->getLocalThumbnailUrl(),
        ]);
    }
}
