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
            'anime_list_pagination_size' => ['integer', 'min:2', 'max:250'],
            'show_anime_list_number' => ['nullable', 'in:1,0'],
        ];
    }
}
