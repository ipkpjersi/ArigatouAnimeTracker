<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function getUserData()
    {
        $query = User::select('id', 'avatar', 'username', 'is_admin', 'created_at');

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
}