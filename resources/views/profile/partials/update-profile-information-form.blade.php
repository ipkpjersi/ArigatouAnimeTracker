<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" enctype="multipart/form-data" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <!-- Username -->
        <div>
            <x-input-label for="username" :value="__('Username')"/>
            <x-text-input id="username" name="username" type="text" class="mt-1 block w-full"
                          :value="old('username', $user->username)" required autofocus autocomplete="username"/>
            <x-input-error class="mt-2" :messages="$errors->get('username')"/>
        </div>

        <!-- Email -->
        <div>
            <x-input-label for="email" :value="__('Email')"/>
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                          :value="old('email', $user->email)" required autocomplete="email"/>
            <x-input-error class="mt-2" :messages="$errors->get('email')"/>

            <div id="confirmation-modal" class="fixed inset-0 bg-opacity-50 flex items-center justify-center hidden">
                <div class="bg-gray-100 dark:bg-black p-4 rounded-lg text-center">
                    <p class="mb-4">Are you sure you want to delete your avatar?</p>
                    <div class="flex justify-around">
                        <button type="button" onclick="deleteAvatar()"
                                class="px-4 py-2 bg-green-500 text-white rounded-lg">Yes
                        </button>
                        <button type="button" onclick="closeModal()" class="px-4 py-2 bg-red-500 text-white rounded-lg">
                            No
                        </button>
                    </div>
                </div>
            </div>

            <!-- Current Avatar -->
            <x-input-label class="mt-4" for="avatar" :value="__('Avatar (Resized to 150x150)')"/>
            <img id="current-avatar" src="{{ $user->avatar }}" alt="Current Avatar"
                 @if (!$user->avatar) style="display:none; width:150px; height:150px"
                 @endif class="rounded-lg w-24 h-24 mb-3" style="width:150px; height: 150px;">
            <input id="avatar" name="avatar" type="file" class="mt-1 block w-full" value="old('avatar', $user->avatar)"
                   accept="image/*" autocomplete="avatar"/>
            <button type="button" id="delete-avatar-button"
                    class="mt-3 text-white bg-red-500 hover:bg-red-600 px-4 py-2 rounded shadow">{{ __('Delete Avatar') }}</button>
            <x-input-error class="mt-2" :messages="$errors->get('avatar')"/>

            <!-- Dark Mode -->
            <x-input-label class="mt-4" for="dark_mode" :value="__('Dark Mode')"/>
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="dark_mode" type="radio" name="dark_mode" value="1" class="form-radio"
                           @if (old('dark_mode', $user->dark_mode) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="dark_mode" type="radio" name="dark_mode" value="0" class="form-radio"
                           @if (old('dark_mode', $user->dark_mode) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('dark_mode')"/>

            <!-- Show Adult Content -->
            <x-input-label class="mt-4" for="show_adult_content" :value="__('Show Adult Content')"/>
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="show_adult_content" type="radio" name="show_adult_content" value="1" class="form-radio"
                           @if (old('show_adult_content', $user->show_adult_content) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="show_adult_content" type="radio" name="show_adult_content" value="0" class="form-radio"
                           @if (old('show_adult_content', $user->show_adult_content) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('show_adult_content')"/>

            <!-- Anime List Pagination Size (Default: 15) -->
            <x-input-label class="mt-4" for="anime_list_pagination_size"
                           :value="__('Anime List Pagination Size (Default: 15)')"/>
            <x-text-input id="anime_list_pagination_size" name="anime_list_pagination_size" type="number" min="2"
                          max="1000" class="mt-1 block w-full"
                          :value="old('anime_list_pagination_size', $user->anime_list_pagination_size)"
                          required/>
            <x-input-error class="mt-2" :messages="$errors->get('anime_list_pagination_size')"/>

            <!-- Show Anime List Publicly -->
            <x-input-label class="mt-4" for="show_anime_list_publicly" :value="__('Show Anime List Publicly')"/>
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="show_anime_list_publicly" type="radio" name="show_anime_list_publicly" value="1"
                           class="form-radio"
                           @if (old('show_anime_list_publicly', $user->show_anime_list_publicly) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="show_anime_list_publicly" type="radio" name="show_anime_list_publicly" value="0"
                           class="form-radio"
                           @if (old('show_anime_list_publicly', $user->show_anime_list_publicly) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('show_anime_list_publicly')"/>

            <!-- Show Number of Current Anime in List -->
            <x-input-label class="mt-4" for="show_anime_list_number"
                           :value="__('Show Number of Current Anime in List')"/>
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="show_anime_list_number" type="radio" name="show_anime_list_number" value="1"
                           class="form-radio"
                           @if (old('show_anime_list_number', $user->show_anime_list_number) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="show_anime_list_number" type="radio" name="show_anime_list_number" value="0"
                           class="form-radio"
                           @if (old('show_anime_list_number', $user->show_anime_list_number) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('show_anime_list_number')"/>

            <!-- Show Delete Anime List Button -->
            <x-input-label class="mt-4" for="show_clear_anime_list_button"
                           :value="__('Show Delete Anime List Button')"/>
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="show_clear_anime_list_button" type="radio" name="show_clear_anime_list_button" value="1"
                           class="form-radio"
                           @if (old('show_clear_anime_list_button', $user->show_clear_anime_list_button) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="show_clear_anime_list_button" type="radio" name="show_clear_anime_list_button" value="0"
                           class="form-radio"
                           @if (old('show_clear_anime_list_button', $user->show_clear_anime_list_button) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('show_clear_anime_list_button')"/>

            <!-- Show Delete Anime List Sort Orders Button  -->
            <x-input-label class="mt-4" for="show_clear_sort_orders_button"
                           :value="__('Show Delete Anime List Sort Orders Button')"/>
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="show_clear_anime_list_sort_orders_button" type="radio"
                           name="show_clear_anime_list_sort_orders_button" value="1" class="form-radio"
                           @if (old('show_clear_anime_list_sort_orders_button', $user->show_clear_anime_list_sort_orders_button) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="show_clear_anime_list_sort_orders_button" type="radio"
                           name="show_clear_anime_list_sort_orders_button" value="0" class="form-radio"
                           @if (old('show_clear_anime_list_sort_orders_button', $user->show_clear_anime_list_sort_orders_button) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('show_clear_anime_list_sort_orders_button')"/>

            <!-- Display Anime Cards instead of List on Categories pages -->
            <x-input-label class="mt-4" for="display_anime_cards"
                           :value="__('Display Anime Cards instead of List on Categories pages')"/>
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="display_anime_cards" type="radio" name="display_anime_cards" value="1" class="form-radio"
                           @if (old('display_anime_cards', $user->display_anime_cards) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="display_anime_cards" type="radio" name="display_anime_cards" value="0" class="form-radio"
                           @if (old('display_anime_cards', $user->display_anime_cards) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('display_anime_cards')"/>

            <!-- Enable Friends System -->
            <x-input-label class="mt-4" for="enable_friends_system" :value="__('Enable Friends System')"/>
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="enable_friends_system" type="radio" name="enable_friends_system" value="1"
                           class="form-radio"
                           @if (old('enable_friends_system', $user->enable_friends_system) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="enable_friends_system" type="radio" name="enable_friends_system" value="0"
                           class="form-radio"
                           @if (old('enable_friends_system', $user->enable_friends_system) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('enable_friends_system')"/>

            <!-- Show Friends on My Profile Publicly -->
            <x-input-label class="mt-4" for="show_friends_on_profile_publicly"
                           :value="__('Show Friends on My Profile Publicly')"/>
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="show_friends_on_profile_publicly" type="radio" name="show_friends_on_profile_publicly"
                           value="1" class="form-radio"
                           @if (old('show_friends_on_profile_publicly', $user->show_friends_on_profile_publicly) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="show_friends_on_profile_publicly" type="radio" name="show_friends_on_profile_publicly"
                           value="0" class="form-radio"
                           @if (old('show_friends_on_profile_publicly', $user->show_friends_on_profile_publicly) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('show_friends_on_profile_publicly')"/>

            <!-- Show Friends on My Profile when I am Logged In -->
            <x-input-label class="mt-4" for="show_friends_on_profile_when_logged_in"
                           :value="__('Show Friends on My Profile when I am Logged In')"/>
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="show_friends_on_profile_when_logged_in" type="radio"
                           name="show_friends_on_profile_when_logged_in" value="1" class="form-radio"
                           @if (old('show_friends_on_profile_when_logged_in', $user->show_friends_on_profile_when_logged_in) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="show_friends_on_profile_when_logged_in" type="radio"
                           name="show_friends_on_profile_when_logged_in" value="0" class="form-radio"
                           @if (old('show_friends_on_profile_when_logged_in', $user->show_friends_on_profile_when_logged_in) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('show_friends_on_profile_when_logged_in')"/>

            <!-- Show Friends Link in Navigation Dropdown -->
            <x-input-label class="mt-4" for="show_friends_in_nav_dropdown"
                           :value="__('Show Friends Link in Navigation Dropdown')"/>
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="show_friends_in_nav_dropdown" type="radio" name="show_friends_in_nav_dropdown" value="1"
                           class="form-radio"
                           @if (old('show_friends_in_nav_dropdown', $user->show_friends_in_nav_dropdown) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="show_friends_in_nav_dropdown" type="radio" name="show_friends_in_nav_dropdown" value="0"
                           class="form-radio"
                           @if (old('show_friends_in_nav_dropdown', $user->show_friends_in_nav_dropdown) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('show_friends_in_nav_dropdown')"/>

            <!-- View Friends on Other Users' Profiles -->
            <x-input-label class="mt-4" for="show_friends_on_others_profiles"
                           :value="__('View Friends on Other Users\' Profiles')"/>
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="show_friends_on_others_profiles" type="radio" name="show_friends_on_others_profiles"
                           value="1" class="form-radio"
                           @if (old('show_friends_on_others_profiles', $user->show_friends_on_others_profiles) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="show_friends_on_others_profiles" type="radio" name="show_friends_on_others_profiles"
                           value="0" class="form-radio"
                           @if (old('show_friends_on_others_profiles', $user->show_friends_on_others_profiles) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('show_friends_on_others_profiles')"/>

            <!-- Show Anime Notes Publicly -->
            <x-input-label class="mt-4" for="show_anime_notes_publicly" :value="__('Show Anime Notes Publicly')"/>
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="show_anime_notes_publicly" type="radio" name="show_anime_notes_publicly" value="1"
                           class="form-radio"
                           @if (old('show_anime_notes_publicly', $user->show_anime_notes_publicly) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="show_anime_notes_publicly" type="radio" name="show_anime_notes_publicly" value="0"
                           class="form-radio"
                           @if (old('show_anime_notes_publicly', $user->show_anime_notes_publicly) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('show_anime_notes_publicly')"/>

            <!-- Enable Reviews System -->
            <x-input-label class="mt-4" for="enable_reviews_system" :value="__('Enable Reviews System')"/>
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="enable_reviews_system" type="radio" name="enable_reviews_system" value="1"
                           class="form-radio"
                           @if (old('enable_reviews_system', $user->enable_reviews_system) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="enable_reviews_system" type="radio" name="enable_reviews_system" value="0"
                           class="form-radio"
                           @if (old('enable_reviews_system', $user->enable_reviews_system) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('enable_reviews_system')"/>

            <!-- Show Own Reviews When Logged In -->
            <x-input-label class="mt-4" for="show_reviews_when_logged_in"
                           :value="__('Show Own Reviews When Logged In')"/>
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="show_reviews_when_logged_in" type="radio" name="show_reviews_when_logged_in" value="1"
                           class="form-radio"
                           @if (old('show_reviews_when_logged_in', $user->show_reviews_when_logged_in) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="show_reviews_when_logged_in" type="radio" name="show_reviews_when_logged_in" value="0"
                           class="form-radio"
                           @if (old('show_reviews_when_logged_in', $user->show_reviews_when_logged_in) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('show_reviews_when_logged_in')"/>

            <!-- Show Own Reviews Publicly -->
            <x-input-label class="mt-4" for="show_reviews_publicly" :value="__('Show Own Reviews Publicly')"/>
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="show_reviews_publicly" type="radio" name="show_reviews_publicly" value="1"
                           class="form-radio"
                           @if (old('show_reviews_publicly', $user->show_reviews_publicly) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="show_reviews_publicly" type="radio" name="show_reviews_publicly" value="0"
                           class="form-radio"
                           @if (old('show_reviews_publicly', $user->show_reviews_publicly) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('show_reviews_publicly')"/>

            <!-- Show Others' Reviews -->
            <x-input-label class="mt-4" for="show_others_reviews" :value="__('Show Others\' Reviews')"/>
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="show_others_reviews" type="radio" name="show_others_reviews" value="1" class="form-radio"
                           @if (old('show_others_reviews', $user->show_others_reviews) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="show_others_reviews" type="radio" name="show_others_reviews" value="0" class="form-radio"
                           @if (old('show_others_reviews', $user->show_others_reviews) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('show_others_reviews')"/>

            <!-- Show Reviews in Navigation Dropdown -->
            <x-input-label class="mt-4" for="show_reviews_in_nav_dropdown"
                           :value="__('Show Reviews in Navigation Dropdown')"/>
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="show_reviews_in_nav_dropdown" type="radio" name="show_reviews_in_nav_dropdown" value="1"
                           class="form-radio"
                           @if (old('show_reviews_in_nav_dropdown', $user->show_reviews_in_nav_dropdown) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="show_reviews_in_nav_dropdown" type="radio" name="show_reviews_in_nav_dropdown" value="0"
                           class="form-radio"
                           @if (old('show_reviews_in_nav_dropdown', $user->show_reviews_in_nav_dropdown) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('show_reviews_in_nav_dropdown')"/>

            <!-- Enable Score Charts System -->
            <x-input-label class="mt-4" for="enable_score_charts_system" :value="__('Enable Score Charts System')"/>
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="enable_score_charts_system" type="radio" name="enable_score_charts_system" value="1"
                           class="form-radio"
                           @if (old('enable_score_charts_system', $user->enable_score_charts_system) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="enable_score_charts_system" type="radio" name="enable_score_charts_system" value="0"
                           class="form-radio"
                           @if (old('enable_score_charts_system', $user->enable_score_charts_system) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('enable_score_charts_system')"/>

            <!-- Enable Score Charts on Own Profile When Logged In -->
            <x-input-label class="mt-4" for="enable_score_charts_own_profile_when_logged_in"
                           :value="__('Enable Score Charts on Own Profile When Logged In')"/>
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="enable_score_charts_own_profile_when_logged_in" type="radio"
                           name="enable_score_charts_own_profile_when_logged_in" value="1" class="form-radio"
                           @if (old('enable_score_charts_own_profile_when_logged_in', $user->enable_score_charts_own_profile_when_logged_in) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="enable_score_charts_own_profile_when_logged_in" type="radio"
                           name="enable_score_charts_own_profile_when_logged_in" value="0" class="form-radio"
                           @if (old('enable_score_charts_own_profile_when_logged_in', $user->enable_score_charts_own_profile_when_logged_in) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('enable_score_charts_own_profile_when_logged_in')"/>

            <!-- Enable Score Charts on Own Profile Publicly -->
            <x-input-label class="mt-4" for="enable_score_charts_own_profile_publicly"
                           :value="__('Enable Score Charts on Own Profile Publicly')"/>
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="enable_score_charts_own_profile_publicly" type="radio"
                           name="enable_score_charts_own_profile_publicly" value="1" class="form-radio"
                           @if (old('enable_score_charts_own_profile_publicly', $user->enable_score_charts_own_profile_publicly) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="enable_score_charts_own_profile_publicly" type="radio"
                           name="enable_score_charts_own_profile_publicly" value="0" class="form-radio"
                           @if (old('enable_score_charts_own_profile_publicly', $user->enable_score_charts_own_profile_publicly) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('enable_score_charts_own_profile_publicly')"/>

            <!-- Enable Score Charts on Other Profiles -->
            <x-input-label class="mt-4" for="enable_score_charts_other_profiles"
                           :value="__('Enable Score Charts on Other Profiles')"/>
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="enable_score_charts_other_profiles" type="radio"
                           name="enable_score_charts_other_profiles" value="1" class="form-radio"
                           @if (old('enable_score_charts_other_profiles', $user->enable_score_charts_other_profiles) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="enable_score_charts_other_profiles" type="radio"
                           name="enable_score_charts_other_profiles" value="0" class="form-radio"
                           @if (old('enable_score_charts_other_profiles', $user->enable_score_charts_other_profiles) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('enable_score_charts_other_profiles')"/>

            <!-- Enable Modifying Sort Order on Anime Detail Page Sorts Entire User Anime List -->
            <x-input-label class="mt-4" for="modifying_sort_order_on_detail_page_sorts_entire_list"
                           :value="__('Enable Modifying Sort Order on Anime Detail Page Sorts Entire User Anime List (Recommended to leave enabled)')"/>
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="modifying_sort_order_on_detail_page_sorts_entire_list" type="radio"
                           name="modifying_sort_order_on_detail_page_sorts_entire_list" value="1" class="form-radio"
                           @if (old('modifying_sort_order_on_detail_page_sorts_entire_list', $user->modifying_sort_order_on_detail_page_sorts_entire_list) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="modifying_sort_order_on_detail_page_sorts_entire_list" type="radio"
                           name="modifying_sort_order_on_detail_page_sorts_entire_list" value="0" class="form-radio"
                           @if (old('modifying_sort_order_on_detail_page_sorts_entire_list', $user->modifying_sort_order_on_detail_page_sorts_entire_list) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('modifying_sort_order_on_detail_page_sorts_entire_list')"/>

            <!-- Enable Favourites System -->
            <x-input-label class="mt-4" for="enable_favourites_system" :value="__('Enable Favourites System')" />
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="enable_favourites_system" type="radio" name="enable_favourites_system" value="1" class="form-radio"
                           @if (old('enable_favourites_system', $user->enable_favourites_system) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="enable_favourites_system" type="radio" name="enable_favourites_system" value="0" class="form-radio"
                           @if (old('enable_favourites_system', $user->enable_favourites_system) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('enable_favourites_system')" />

            <!-- Show Own Favourites When Logged In -->
            <x-input-label class="mt-4" for="show_own_favourites_when_logged_in" :value="__('Show Own Favourites When Logged In')" />
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="show_own_favourites_when_logged_in" type="radio" name="show_own_favourites_when_logged_in" value="1" class="form-radio"
                           @if (old('show_own_favourites_when_logged_in', $user->show_own_favourites_when_logged_in) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="show_own_favourites_when_logged_in" type="radio" name="show_own_favourites_when_logged_in" value="0" class="form-radio"
                           @if (old('show_own_favourites_when_logged_in', $user->show_own_favourites_when_logged_in) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('show_own_favourites_when_logged_in')" />

            <!-- Show Favourites Publicly -->
            <x-input-label class="mt-4" for="show_favourites_publicly" :value="__('Show Favourites Publicly')" />
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="show_favourites_publicly" type="radio" name="show_favourites_publicly" value="1" class="form-radio"
                           @if (old('show_favourites_publicly', $user->show_favourites_publicly) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="show_favourites_publicly" type="radio" name="show_favourites_publicly" value="0" class="form-radio"
                           @if (old('show_favourites_publicly', $user->show_favourites_publicly) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('show_favourites_publicly')" />

            <!-- Show Others' Favourites -->
            <x-input-label class="mt-4" for="show_others_favourites" :value="__('Show Others\' Favourites')" />
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="show_others_favourites" type="radio" name="show_others_favourites" value="1" class="form-radio"
                           @if (old('show_others_favourites', $user->show_others_favourites) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="show_others_favourites" type="radio" name="show_others_favourites" value="0" class="form-radio"
                           @if (old('show_others_favourites', $user->show_others_favourites) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('show_others_favourites')" />

            <!-- Show Favourites in Navigation Dropdown -->
            <x-input-label class="mt-4" for="show_favourites_in_nav_dropdown" :value="__('Show Favourites in Navigation Dropdown')" />
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="show_favourites_in_nav_dropdown" type="radio" name="show_favourites_in_nav_dropdown" value="1" class="form-radio"
                           @if (old('show_favourites_in_nav_dropdown', $user->show_favourites_in_nav_dropdown) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="show_favourites_in_nav_dropdown" type="radio" name="show_favourites_in_nav_dropdown" value="0" class="form-radio"
                           @if (old('show_favourites_in_nav_dropdown', $user->show_favourites_in_nav_dropdown) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('show_favourites_in_nav_dropdown')" />

            <!-- Sort Favourites on Own Profile -->
            <x-input-label class="mt-4" for="favourites_sort_own" :value="__('Sort Favourites on Own Profile By')" />
            <div class="mt-1">
                <select name="favourites_sort_own" id="favourites_sort_own" class="form-select  text-gray-800 dark:text-gray-900">
                    <option value="title" @if (old('favourites_sort_own', $user->favourites_sort_own) === 'title') selected @endif>Title</option>
                    <option value="episodes" @if (old('favourites_sort_own', $user->favourites_sort_own) === 'episodes') selected @endif>Episodes</option>
                    <option value="year" @if (old('favourites_sort_own', $user->favourites_sort_own) === 'year') selected @endif>Year</option>
                    <option value="type" @if (old('favourites_sort_own', $user->favourites_sort_own) === 'type') selected @endif>Type</option>
                    <option value="status" @if (old('favourites_sort_own', $user->favourites_sort_own) === 'status') selected @endif>Status</option>
                    <option value="date_added" @if (old('favourites_sort_own', $user->favourites_sort_own) === 'date_added') selected @endif>Date Added</option>
                    <option value="sort_order" @if (old('favourites_sort_own', $user->favourites_sort_own) === 'sort_order') selected @endif>Sort Order</option>
                    <option value="random" @if (old('favourites_sort_own', $user->favourites_sort_own) === 'random') selected @endif>Random</option>
                </select>
            </div>
            <x-input-label class="mt-4" for="favourites_sort_own_order" :value="__('Sort Order')" />
            <div class="mt-1">
                <select name="favourites_sort_own_order" id="favourites_sort_own_order" class="form-select text-gray-800 dark:text-gray-900">
                    <option value="asc" @if (old('favourites_sort_own_order', $user->favourites_sort_own_order) === 'asc') selected @endif>Ascending</option>
                    <option value="desc" @if (old('favourites_sort_own_order', $user->favourites_sort_own_order) === 'desc') selected @endif>Descending</option>
                </select>
            </div>

            <!-- Sort Favourites on Others' Profiles -->
            <x-input-label class="mt-4" for="favourites_sort_others" :value="__('Sort Favourites on Others\' Profiles By')" />
            <div class="mt-1">
                <select name="favourites_sort_others" id="favourites_sort_others" class="form-select text-gray-800 dark:text-gray-900">
                    <option value="title" @if (old('favourites_sort_others', $user->favourites_sort_others) === 'title') selected @endif>Title</option>
                    <option value="episodes" @if (old('favourites_sort_others', $user->favourites_sort_others) === 'episodes') selected @endif>Episodes</option>
                    <option value="year" @if (old('favourites_sort_others', $user->favourites_sort_others) === 'year') selected @endif>Year</option>
                    <option value="type" @if (old('favourites_sort_others', $user->favourites_sort_others) === 'type') selected @endif>Type</option>
                    <option value="status" @if (old('favourites_sort_others', $user->favourites_sort_others) === 'status') selected @endif>Status</option>
                    <option value="date_added" @if (old('favourites_sort_others', $user->favourites_sort_others) === 'date_added') selected @endif>Date Added</option>
                    <option value="sort_order" @if (old('favourites_sort_others', $user->favourites_sort_others) === 'sort_order') selected @endif>Sort Order</option>
                    <option value="random" @if (old('favourites_sort_others', $user->favourites_sort_others) === 'random') selected @endif>Random</option>
                </select>
            </div>
            <x-input-label class="mt-4" for="favourites_sort_others_order" :value="__('Sort Order')" />
            <div class="mt-1">
                <select name="favourites_sort_others_order" id="favourites_sort_others_order" class="form-select text-gray-800 dark:text-gray-900">
                    <option value="asc" @if (old('favourites_sort_others_order', $user->favourites_sort_others_order) === 'asc') selected @endif>Ascending</option>
                    <option value="desc" @if (old('favourites_sort_others_order', $user->favourites_sort_others_order) === 'desc') selected @endif>Descending</option>
                </select>
            </div>

            <!-- ADD MORE USERS COLUMNS HERE AS NECESSARY -->


            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification"
                                class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
    <script>
        document.getElementById('avatar').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('current-avatar').style.display = '';
                    document.getElementById('current-avatar').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        function deleteAvatar() {
            fetch('{{ route('avatar.delete', ['userId' => $user->id]) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('current-avatar').style.display = 'none';
                        document.getElementById('current-avatar').src = '';
                        document.getElementById('avatar').value = '';
                        closeModal();
                    }
                });
        }

        function closeModal() {
            document.getElementById('confirmation-modal').classList.add('hidden');
        }

        document.getElementById('delete-avatar-button').addEventListener('click', function () {
            document.getElementById('confirmation-modal').classList.remove('hidden');
        });

        window.addEventListener("click", function (event) {
            if (event.target === document.getElementById("confirmation-modal")) {
                closeModal();
            }
        });

        window.addEventListener("keydown", function (e) {
            if (e.key === "Escape") {
                closeModal();
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            // Function to disable or enable related fields
            function setRelatedFriendsFieldsDisabled(disabled) {
                ['show_friends_on_profile_publicly', 'show_friends_on_profile_when_logged_in', 'show_friends_in_nav_dropdown', 'show_friends_on_others_profiles'].forEach(name => {
                    document.querySelectorAll(`input[name="${name}"]`).forEach(radio => {
                        radio.disabled = disabled;
                        if (disabled) {
                            // Set to 'No' if disabled
                            radio.checked = radio.value === '0';
                        }
                    });
                });
            }

            // Event listener for 'Enable Friends System' radio buttons
            document.querySelectorAll('input[name="enable_friends_system"]').forEach(radio => {
                radio.addEventListener('change', function () {
                    setRelatedFriendsFieldsDisabled(this.value === '0');
                });
            });

            // Function to ensure logical consistency
            function adjustFriendsPubliclySetting() {
                const showWhenLoggedIn = document.querySelector('input[name="show_friends_on_profile_when_logged_in"]:checked').value;
                const showPubliclyRadios = document.querySelectorAll('input[name="show_friends_on_profile_publicly"]');
                if (showWhenLoggedIn === '0') {
                    // If 'Show when logged in' is 'No', 'Show publicly' must also be 'No'
                    showPubliclyRadios.forEach(radio => radio.checked = radio.value === '0');
                }
                // Disable 'Show publicly' radios if 'Show when logged in' is 'No'
                showPubliclyRadios.forEach(radio => radio.disabled = showWhenLoggedIn === '0');
            }

            // Event listener for 'Show Friends on Profile When Logged In' radio buttons
            document.querySelectorAll('input[name="show_friends_on_profile_when_logged_in"]').forEach(radio => {
                radio.addEventListener('change', adjustFriendsPubliclySetting);
            });

            // Initialize state on load
            setRelatedFriendsFieldsDisabled(document.querySelector('input[name="enable_friends_system"]:checked').value === '0');
            adjustFriendsPubliclySetting();

            // Function to disable or enable review related fields
            function setReviewFieldsDisabled(disabled) {
                ['show_reviews_when_logged_in', 'show_reviews_publicly', 'show_others_reviews', 'show_reviews_in_nav_dropdown'].forEach(name => {
                    document.querySelectorAll(`input[name="${name}"]`).forEach(radio => {
                        radio.disabled = disabled;
                        if (disabled) {
                            // Set to 'No' if disabled
                            radio.checked = radio.value === '0';
                        }
                    });
                });
            }

            // Event listener for 'Enable Reviews System' radio buttons
            document.querySelectorAll('input[name="enable_reviews_system"]').forEach(radio => {
                radio.addEventListener('change', function () {
                    setReviewFieldsDisabled(this.value === '0');
                });
            });

            // Initialize review settings on load
            setReviewFieldsDisabled(document.querySelector('input[name="enable_reviews_system"]:checked').value === '0');

            function setScoreChartsFieldsDisabled(disabled) {
                ['enable_score_charts_own_profile_when_logged_in', 'enable_score_charts_own_profile_publicly', 'enable_score_charts_other_profiles'].forEach(name => {
                    document.querySelectorAll(`input[name="${name}"]`).forEach(radio => {
                        radio.disabled = disabled;
                        if (disabled) {
                            radio.checked = radio.value === '0';
                        }
                    });
                });
            }

            document.querySelectorAll('input[name="enable_score_charts"]').forEach(radio => {
                radio.addEventListener('change', function () {
                    setScoreChartsFieldsDisabled(this.value === '0');
                });
            });

            function adjustScoreChartsPubliclySetting() {
                const showWhenLoggedIn = document.querySelector('input[name="enable_score_charts_own_profile_when_logged_in"]:checked').value;
                const showPubliclyRadios = document.querySelectorAll('input[name="enable_score_charts_own_profile_publicly"]');
                if (showWhenLoggedIn === '0') {
                    showPubliclyRadios.forEach(radio => radio.checked = radio.value === '0');
                }
                showPubliclyRadios.forEach(radio => radio.disabled = showWhenLoggedIn === '0');
            }

            document.querySelectorAll('input[name="enable_score_charts_own_profile_when_logged_in"]').forEach(radio => {
                radio.addEventListener('change', adjustScoreChartsPubliclySetting);
            });

            setScoreChartsFieldsDisabled(document.querySelector('input[name="enable_score_charts"]:checked').value === '0');
            adjustScoreChartsPubliclySetting();

            // Function to disable or enable related fields for the favourites system
            function setFavouritesFieldsDisabled(disabled) {
                ['show_own_favourites_when_logged_in', 'show_favourites_publicly', 'show_favourites_in_nav_dropdown', 'show_others_favourites'].forEach(name => {
                    document.querySelectorAll(`input[name="${name}"]`).forEach(radio => {
                        radio.disabled = disabled;
                        if (disabled) {
                            // Set to 'No' if disabled
                            radio.checked = radio.value === '0';
                        }
                    });
                });
            }

            // Event listener for 'Enable Favourites System' radio buttons
            document.querySelectorAll('input[name="enable_favourites_system"]').forEach(radio => {
                radio.addEventListener('change', function () {
                    setFavouritesFieldsDisabled(this.value === '0');
                });
            });

            // Function to ensure logical consistency for the favourites system
            function adjustFavouritesPubliclySetting() {
                const showWhenLoggedIn = document.querySelector('input[name="show_own_favourites_when_logged_in"]:checked').value;
                const showPubliclyRadios = document.querySelectorAll('input[name="show_favourites_publicly"]');
                if (showWhenLoggedIn === '0') {
                    // If 'Show Own Favourites When Logged In' is 'No', 'Show Favourites Publicly' must also be 'No'
                    showPubliclyRadios.forEach(radio => radio.checked = radio.value === '0');
                }
                // Disable 'Show Favourites Publicly' radios if 'Show Own Favourites When Logged In' is 'No'
                showPubliclyRadios.forEach(radio => radio.disabled = showWhenLoggedIn === '0');
            }

            // Event listener for 'Show Own Favourites When Logged In' radio buttons
            document.querySelectorAll('input[name="show_own_favourites_when_logged_in"]').forEach(radio => {
                radio.addEventListener('change', adjustFavouritesPubliclySetting);
            });

            // Initialize favourites settings on load
            setFavouritesFieldsDisabled(document.querySelector('input[name="enable_favourites_system"]:checked').value === '0');
            adjustFavouritesPubliclySetting();
        });

    </script>
</section>
