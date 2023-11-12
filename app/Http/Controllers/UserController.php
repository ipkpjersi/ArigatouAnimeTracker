<?php

namespace App\Http\Controllers;

use App\Models\StaffActionLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function getUserData()
    {
        if (!request()->has(['start', 'length']) || request()->input('length') > 1000) {
            return response()->json(['error' => 'Invalid request'], 400);
        }
        $query = User::select('id', 'avatar', 'username', 'is_admin', 'is_banned', 'created_at');

        return DataTables::of($query)
            ->editColumn('created_at', function($user) {
                return Carbon::parse($user->created_at)->format('M d, Y');
            })
            ->make(true);
    }

    public function list() {
        return view("userlist");
    }

    public function detail($username)
    {
        $user = User::where(['username' => $username])->firstOrFail();
        $stats = $user->animeStatistics();
        $friends = $user->friends()->paginate(4);
        $currentUser = auth()->user();

        //Check if the user is viewing their own profile
        $isOwnProfile = strtolower($currentUser->username ?? '') === strtolower($user->username);

        //Determine if the friends section should be displayed
        if ($isOwnProfile) {
            //User is viewing their own profile
            $canViewFriends = $user->show_friends_on_profile_when_logged_in === 1;
        } else {
            //Viewing someone else's profile, check if we are logged in first
            if ($currentUser) {
                //Current user needs to have enabled viewing friends on other profiles
                $canViewFriends = $currentUser->show_friends_on_others_profiles === 1 && $user->show_friends_on_profile_publicly === 1;
            } else {
                $canViewFriends = $user->show_friends_on_profile_publicly === 1;
            }
        }

        $enableFriendsSystem = auth()->user()->enable_friends_system === 1;
        return view('userdetail', compact('user', 'stats', 'friends', 'canViewFriends', 'enableFriendsSystem', 'isOwnProfile'));
    }

    public function banUser(Request $request, $userId)
    {
        if (auth()->user() == null || !auth()->user()->isAdmin()) {
            return response()->json([], 404);
        }
        $user = User::findOrFail($userId);
        $user->is_banned = true;
        $user->save();

        StaffActionLog::create([
            'user_id' => auth()->id(),
            'target_id' => $user->id,
            'action' => 'ban'
        ]);

        return response()->json(['message' => 'User banned successfully']);
    }

    public function unbanUser(Request $request, $userId)
    {
        if (auth()->user() == null || !auth()->user()->isAdmin()) {
            return response()->json([], 404);
        }
        $user = User::findOrFail($userId);
        $user->is_banned = false;
        $user->save();

        StaffActionLog::create([
            'user_id' => auth()->id(),
            'target_id' => $user->id,
            'action' => 'unban'
        ]);

        return response()->json(['message' => 'User unbanned successfully']);
    }

    public function removeAvatar(Request $request, $userId)
    {
        if (auth()->user() == null || !auth()->user()->isModerator()) {
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
            'message' => "Removed avatar $avatar for user $username (ID: $userId)"
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



}
