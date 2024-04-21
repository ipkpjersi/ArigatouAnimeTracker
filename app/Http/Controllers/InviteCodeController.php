<?php

namespace App\Http\Controllers;

use App\Models\InviteCode;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\Artisan;

class InviteCodeController extends Controller
{
    public function generateInviteCodes(Request $request)
    {
        // Check for appropriate permissions
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Run artisan command to generate invite codes
        Artisan::call('app:generate-invite-codes');

        return response()->json(['message' => 'Invite codes generated successfully']);
    }

    public function revokeUnusedInviteCodes(Request $request)
    {
        // Check for appropriate permissions
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Run artisan command to revoke unused invite codes
        Artisan::call('app:revoke-unused-invite-codes');

        return response()->json(['message' => 'Unused invite codes revoked successfully']);
    }

    public function index()
    {
        return view('invitecodeslist');
    }

    public function data(Request $request)
    {
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $query = InviteCode::query();

        return DataTables::of($query)
            ->make(true);
    }
}
