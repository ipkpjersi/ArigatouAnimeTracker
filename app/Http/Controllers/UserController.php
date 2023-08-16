<?php

namespace App\Http\Controllers;

use App\Models\StaffActionLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
        return view('userdetail', compact('user'));
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

    public function unbanUIser(Request $request, $userId)
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
            'action' => 'unban'
        ]);

        return response()->json(['message' => 'User banned successfully']);
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

}
