<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        // If enabling the friends system is turned off, turn off all related settings
        if ($request->input('enable_friends_system') == false) {
            $request->user()->show_friends_on_profile_publicly = false;
            $request->user()->show_friends_on_profile_when_logged_in = false;
            $request->user()->show_friends_in_nav_dropdown = false;
            $request->user()->show_friends_on_others_profiles = false;
        }

        // If show_friends_on_profile_when_logged_in is turned off, turn off show_friends_on_profile_publicly too
        if ($request->input('show_friends_on_profile_when_logged_in') == false) {
            $request->user()->show_friends_on_profile_publicly = false;
        }

        // If enabling the friends system is turned off, turn off all related settings
        if ($request->input('enable_reviews_system') == false) {
            $request->user()->show_reviews_publicly = false;
            $request->user()->show_reviews_when_logged_in = false;
            $request->user()->show_reviews_in_nav_dropdown = false;
            $request->user()->show_others_reviews = false;
        }

        // If show_friends_on_profile_when_logged_in is turned off, turn off show_friends_on_profile_publicly too
        if ($request->input('show_reviews_when_logged_in') == false) {
            $request->user()->show_reviews_publicly = false;
        }

        // If enabling the score charts system is turned off, turn off all related settings
        if ($request->input('enable_score_charts_system') == false) {
            $request->user()->enable_score_charts_own_profile_when_logged_in = false;
            $request->user()->enable_score_charts_own_profile_publicly = false;
            $request->user()->enable_score_charts_other_profiles = false;
        }

        // If enable_score_charts_own_profile_when_logged_in is turned off, turn off enable_score_charts_own_profile_publicly too
        if ($request->input('enable_score_charts_own_profile_when_logged_in') == false) {
            $request->user()->enable_score_charts_own_profile_publicly = false;
        }

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $extension = $file->getClientOriginalExtension();

            $uniqueName = Str::uuid() . "." . $extension;

            $path = $file->move(public_path('img/avatars'), $uniqueName);
            $request->user()->update(['avatar' => '/img/avatars/' . $uniqueName]);
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
