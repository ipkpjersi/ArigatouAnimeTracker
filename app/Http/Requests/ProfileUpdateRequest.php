<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['string', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'email' => ['email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'dark_mode' => ['nullable', 'in:1,0'],
            'show_adult_content' => ['nullable', 'in:1,0'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'anime_list_pagination_size' => ['integer', 'min:2', 'max:1000'],
            'show_anime_list_number' => ['nullable', 'in:1,0'],
            'show_clear_anime_list_button' => ['nullable', 'in:1,0'],
            'display_anime_cards' => ['nullable', 'in:1,0'],
            'enable_friends_system' => ['nullable', 'in:1,0'],
            'show_friends_on_profile_publicly' => ['nullable', 'in:1,0'],
            'show_friends_on_profile_when_logged_in' => ['nullable', 'in:1,0'],
            'show_friends_in_nav_dropdown' => ['nullable', 'in:1,0'],
            'show_friends_on_others_profiles' => ['nullable', 'in:1,0'],
            'show_anime_notes_publicly' => ['nullable', 'in:1,0'],
            'enable_reviews_system' => ['nullable', 'in:1,0'],
            'show_reviews_when_logged_in' => ['nullable', 'in:1,0'],
            'show_reviews_publicly' => ['nullable', 'in:1,0'],
            'show_others_reviews' => ['nullable', 'in:1,0'],
            'show_reviews_in_nav_dropdown' => ['nullable', 'in:1,0'],
            'enable_score_charts_system' => ['nullable', 'in:1,0'],
            'enable_score_charts_own_profile_when_logged_in' => ['nullable', 'in:1,0'],
            'enable_score_charts_own_profile_publicly' => ['nullable', 'in:1,0'],
            'enable_score_charts_other_profiles' => ['nullable', 'in:1,0'],
            'show_anime_list_publicly' => ['nullable', 'in:1,0'],
            'show_clear_anime_list_sort_orders_button' => ['nullable', 'in:1,0'],
            'modifying_sort_order_on_detail_page_sorts_entire_list' => ['nullable', 'in:1,0'],
            'enable_favourites_system' => ['nullable', 'in:1,0'],
            'show_own_favourites_when_logged_in' => ['nullable', 'in:1,0'],
            'show_favourites_publicly' => ['nullable', 'in:1,0'],
            'show_favourites_in_nav_dropdown' => ['nullable', 'in:1,0'],
            'show_others_favourites' => ['nullable', 'in:1,0'],
        ];
    }
}
