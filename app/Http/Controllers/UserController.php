<?php

namespace App\Http\Controllers;

use App\Models\AnimeReview;
use App\Models\StaffActionLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function getUserData()
    {
        if (! request()->has(['start', 'length']) || request()->input('length') > 1000) {
            return response()->json(['error' => 'Invalid request'], 400);
        }
        $query = User::select('id', 'avatar', 'username', 'is_admin', 'is_banned', 'created_at');
        if (Auth::user() === null || Auth::user()->is_admin !== 1) {
            $query->where('is_banned', '0');
        }

        return DataTables::of($query)
            ->editColumn('created_at', function ($user) {
                return Carbon::parse($user->created_at)->format('M d, Y');
            })
            ->make(true);
    }

    public function list()
    {
        return view('userlist');
    }

    public function detail(Request $request, $username)
    {
        $user = User::where(['username' => $username])->firstOrFail();
        if ($user->is_banned === 1 && (Auth::user() === null || Auth::user()->is_admin !== 1)) {
            abort(404);
        }
        $stats = $user->animeStatistics();
        $showPubliclyOnly = true;
        if ($request->has('showallfriends') && $request->input('showallfriends') === '1' && Auth::user() !== null && strtolower(Auth::user()->username) === strtolower($user->username)) {
            $showPubliclyOnly = false;
        }

        $friends = $user->friends()->when($showPubliclyOnly, function ($query) {
            return $query->where('user_friends.show_friend_publicly', true);
        })->paginate(2, ['*'], 'friendpage')->withQueryString();
        $currentUser = auth()->user();

        //Check if the user is viewing their own profile
        $isOwnProfile = strtolower($currentUser->username ?? '') === strtolower($user->username);

        if ($isOwnProfile) {
            //User is viewing their own profile
            $canViewFriends = $user->show_friends_on_profile_when_logged_in === 1;
            $canViewReviews = $user->show_reviews_when_logged_in === 1;
            $showChart = $user->enable_score_charts_own_profile_when_logged_in === 1;
        } else {
            //Viewing someone else's profile, check if we are logged in first
            if ($currentUser) {
                //Current user needs to have enabled viewing  on other profiles
                $canViewFriends = $currentUser->show_friends_on_others_profiles === 1 && $user->show_friends_on_profile_publicly === 1;
                $canViewReviews = $currentUser->show_others_reviews === 1 && $user->show_reviews_publicly === 1;
                $showChart = $currentUser->enable_score_charts_other_profiles === 1 && $user->enable_score_charts_own_profile_publicly === 1;
            } else {
                $canViewFriends = $user->show_friends_on_profile_publicly === 1;
                $canViewReviews = $user->show_reviews_publicly === 1;
                $showChart = $user->enable_score_charts_own_profile_publicly === 1;
            }
        }

        $enableFriendsSystem = auth()->user()->enable_friends_system === 1;
        $enableReviewsSystem = auth()->user()->enable_reviews_system === 1;
        $enableScoreCharts = auth()->user()->enable_score_charts_system === 1;

        $reviews = AnimeReview::where('user_id', $user->id)
            ->join('users', 'anime_reviews.user_id', '=', 'users.id')
            ->where('anime_reviews.show_review_publicly', true)
            ->when(! request('spoilers'), function ($query) {
                return $query->where('anime_reviews.contains_spoilers', false);
            })
            ->where('users.show_reviews_publicly', true)
            ->where('users.is_banned', false)
            ->where('anime_reviews.is_deleted', 0)
            ->latest('anime_reviews.created_at')
            ->paginate(2, ['anime_reviews.*', 'users.username', 'users.avatar', 'users.id as user_id'], 'reviewpage');

        $totalReviewsCount = AnimeReview::where('user_id', $user->id)
            ->join('users', 'anime_reviews.user_id', '=', 'users.id')
            ->where('anime_reviews.show_review_publicly', true)
            ->where('users.show_reviews_publicly', true)
            ->where('anime_reviews.is_deleted', 0)
            ->where('users.is_banned', false)->count();
        $friendUser = null;
        if (! $isOwnProfile && $currentUser) {
            $friendUser = $currentUser->friends()
                ->where('users.id', $user->id)
                ->first();
        }
        $userScoreDistribution = [];
        if ($user) {
            $userScoreDistribution = DB::table('anime_user')
                ->selectRaw('score, COUNT(*) as count')
                ->where('user_id', $user->id)
                ->whereNotNull('score')
                ->where('score', '>', '0')
                ->orderBy('score', 'desc')
                ->groupBy('score')
                ->pluck('count', 'score')
                ->toArray();
        }

        return view('userdetail', compact('user', 'stats', 'friends', 'canViewFriends', 'enableFriendsSystem', 'isOwnProfile', 'reviews', 'totalReviewsCount', 'canViewReviews', 'enableReviewsSystem', 'friendUser', 'userScoreDistribution', 'enableScoreCharts', 'showChart'));
    }

    public function banUser(Request $request, $userId)
    {
        if (auth()->user() == null || ! auth()->user()->isAdmin()) {
            return response()->json([], 404);
        }
        $user = User::findOrFail($userId);
        $user->is_banned = true;
        $user->save();

        StaffActionLog::create([
            'user_id' => auth()->id(),
            'target_id' => $user->id,
            'action' => 'ban',
        ]);

        return response()->json(['message' => 'User banned successfully']);
    }

    public function unbanUser(Request $request, $userId)
    {
        if (auth()->user() == null || ! auth()->user()->isAdmin()) {
            return response()->json([], 404);
        }
        $user = User::findOrFail($userId);
        $user->is_banned = false;
        $user->save();

        StaffActionLog::create([
            'user_id' => auth()->id(),
            'target_id' => $user->id,
            'action' => 'unban',
        ]);

        return response()->json(['message' => 'User unbanned successfully']);
    }

    public function removeReview(Request $request, $reviewId)
    {
        // Ensure only admins can remove reviews
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $review = AnimeReview::findOrFail($reviewId);
        $review->is_deleted = 1;
        $review->save();

        $anime = $review->anime()->first();
        $user = $review->user()->first();

        StaffActionLog::create([
            'user_id' => auth()->id(),
            'target_id' => $review->user_id,
            'action' => 'remove_review',
            'message' => 'Removed review of anime ' . $anime->title . ' (anime ID: ' . $anime->id . ') from user: ' . $user->username . ' (user ID: ' . $review->user_id . ')'
        ]);

        return response()->json(['message' => 'Review removed successfully']);
    }

    public function removeAvatar(Request $request, $userId)
    {
        if (auth()->user() == null || ! auth()->user()->isModerator()) {
            return response()->json([], 404);
        }
        $user = User::findOrFail($userId);
        $avatar = $user->avatar;
        $username = $user->username;
        $user->avatar = null;
        $user->save();

        StaffActionLog::create([
            'user_id' => auth()->id(),
            'target_id' => $user->id,
            'action' => 'remove_avatar',
            'message' => "Removed avatar $avatar for user $username (ID: $userId)",
        ]);

        return response()->json(['message' => 'Avatar removed successfully']);
    }

    public function deleteAvatar(Request $request, $userId)
    {
        if (auth()->user() == null || auth()->user()->id != $userId) {
            return response()->json([], 404);
        }
        $user = User::findOrFail($userId);
        $user->avatar = null;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Avatar deleted successfully']);
    }

    public function addFriend(Request $request, $friendId)
    {
        try {
            $user = Auth::user();
            $user->addFriend($friendId);

            return redirect()->back()->with('success', 'Friend added successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function removeFriend(Request $request, $friendId)
    {
        try {
            $user = Auth::user();
            $user->removeFriend($friendId);

            return redirect()->back()->with('success', 'Friend removed successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function toggleFriendPublicly(Request $request, $friendId)
    {
        try {
            $user = Auth::user();
            $user->toggleFriendPublicly($friendId);

            return redirect()->back()->with('success', 'Friend visibility toggled successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
