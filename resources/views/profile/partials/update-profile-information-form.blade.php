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

        <div>
            <x-input-label for="username" :value="__('Username')" />
            <x-text-input id="username" name="username" type="text" class="mt-1 block w-full" :value="old('username', $user->username)" required autofocus autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('username')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="email" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            <div id="confirmation-modal" class="fixed inset-0 bg-opacity-50 flex items-center justify-center hidden">
                <div class="bg-gray-100 dark:bg-black p-4 rounded-lg text-center">
                    <p class="mb-4">Are you sure you want to delete your avatar?</p>
                    <div class="flex justify-around">
                        <button type="button" onclick="deleteAvatar()" class="px-4 py-2 bg-green-500 text-white rounded-lg">Yes</button>
                        <button type="button" onclick="closeModal()" class="px-4 py-2 bg-red-500 text-white rounded-lg">No</button>
                    </div>
                </div>
            </div>

            <x-input-label class="mt-4" for="avatar" :value="__('Avatar (Resized to 150x150)')" />
            <img id="current-avatar" src="{{ $user->avatar }}" alt="Current Avatar" @if (!$user->avatar) style="display:none; width:150px; height:150px" @endif class="rounded-lg w-24 h-24 mb-3" style="width:150px; height: 150px;">
            <input id="avatar" name="avatar" type="file" class="mt-1 block w-full" value="old('avatar', $user->avatar)" accept="image/*" autocomplete="avatar" />
            <button type="button" id="delete-avatar-button" class="mt-3 text-white bg-red-500 hover:bg-red-600 px-4 py-2 rounded shadow">{{ __('Delete Avatar') }}</button>
            <x-input-error class="mt-2" :messages="$errors->get('avatar')" />

            <x-input-label class="mt-4" for="dark_mode" :value="__('Dark Mode')" />
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
            <x-input-error class="mt-2" :messages="$errors->get('dark_mode')" />

            <x-input-label class="mt-4" for="show_adult_content" :value="__('Show Adult Content')" />
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
            <x-input-error class="mt-2" :messages="$errors->get('show_adult_content')" />

            <x-input-label class="mt-4" for="anime_list_pagination_size" :value="__('Anime List Pagination Size (Default: 15)')" />
            <x-text-input  id="anime_list_pagination_size"  name="anime_list_pagination_size"  type="number"  min="2"  max="250" class="mt-1 block w-full"  :value="old('anime_list_pagination_size', $user->anime_list_pagination_size)"
                required />
            <x-input-error class="mt-2" :messages="$errors->get('anime_list_pagination_size')" />

            <x-input-label class="mt-4" for="show_anime_list_number" :value="__('Show Number of Current Anime in List')" />
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="show_anime_list_number" type="radio" name="show_anime_list_number" value="1" class="form-radio"
                           @if (old('show_anime_list_number', $user->show_anime_list_number) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="show_anime_list_number" type="radio" name="show_anime_list_number" value="0" class="form-radio"
                           @if (old('show_anime_list_number', $user->show_anime_list_number) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('show_anime_list_number')" />

            <x-input-label class="mt-4" for="show_clear_anime_list_button" :value="__('Show Delete Anime List Button')" />
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="show_clear_anime_list_button" type="radio" name="show_clear_anime_list_button" value="1" class="form-radio"
                           @if (old('show_clear_anime_list_button', $user->show_clear_anime_list_button) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="show_clear_anime_list_button" type="radio" name="show_clear_anime_list_button" value="0" class="form-radio"
                           @if (old('show_clear_anime_list_button', $user->show_clear_anime_list_button) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('show_anime_list_number')" />

            <x-input-label class="mt-4" for="display_anime_cards" :value="__('Display Anime Cards instead of List on Categories pages')" />
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
            <x-input-error class="mt-2" :messages="$errors->get('show_anime_list_number')" />

            <x-input-label class="mt-4" for="enable_friends_system" :value="__('Enable Friends System')" />
                <div class="mt-1 text-gray-800 dark:text-gray-200">
                    <label class="inline-flex items-center">
                        <input id="enable_friends_system" type="radio" name="enable_friends_system" value="1" class="form-radio"
                               @if (old('enable_friends_system', $user->enable_friends_system) === 1) checked @endif>
                        <span class="ml-2">Yes</span>
                    </label>
                    <label class="inline-flex items-center ml-6">
                        <input id="enable_friends_system" type="radio" name="enable_friends_system" value="0" class="form-radio"
                               @if (old('enable_friends_system', $user->enable_friends_system) !== 1) checked @endif>
                        <span class="ml-2">No</span>
                    </label>
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('enable_friends_system')" />

            <x-input-label class="mt-4" for="show_friends_on_profile_publicly" :value="__('Show Friends on My Profile Publicly')" />
                <div class="mt-1 text-gray-800 dark:text-gray-200">
                    <label class="inline-flex items-center">
                        <input id="show_friends_on_profile_publicly" type="radio" name="show_friends_on_profile_publicly" value="1" class="form-radio"
                               @if (old('show_friends_on_profile_publicly', $user->show_friends_on_profile_publicly) === 1) checked @endif>
                        <span class="ml-2">Yes</span>
                    </label>
                    <label class="inline-flex items-center ml-6">
                        <input id="show_friends_on_profile_publicly" type="radio" name="show_friends_on_profile_publicly" value="0" class="form-radio"
                               @if (old('show_friends_on_profile_publicly', $user->show_friends_on_profile_publicly) !== 1) checked @endif>
                        <span class="ml-2">No</span>
                    </label>
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('show_friends_on_profile_publicly')" />

                <x-input-label class="mt-4" for="show_friends_on_profile_when_logged_in" :value="__('Show Friends on My Profile when I am Logged In')" />
                <div class="mt-1 text-gray-800 dark:text-gray-200">
                    <label class="inline-flex items-center">
                        <input id="show_friends_on_profile_when_logged_in" type="radio" name="show_friends_on_profile_when_logged_in" value="1" class="form-radio"
                               @if (old('show_friends_on_profile_when_logged_in', $user->show_friends_on_profile_when_logged_in) === 1) checked @endif>
                        <span class="ml-2">Yes</span>
                    </label>
                    <label class="inline-flex items-center ml-6">
                        <input id="show_friends_on_profile_when_logged_in" type="radio" name="show_friends_on_profile_when_logged_in" value="0" class="form-radio"
                               @if (old('show_friends_on_profile_when_logged_in', $user->show_friends_on_profile_when_logged_in) !== 1) checked @endif>
                        <span class="ml-2">No</span>
                    </label>
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('show_friends_on_profile_when_logged_in')" />

                <x-input-label class="mt-4" for="show_friends_in_nav_dropdown" :value="__('Show Friends Link in Navigation Dropdown')" />
                <div class="mt-1 text-gray-800 dark:text-gray-200">
                    <label class="inline-flex items-center">
                        <input id="show_friends_in_nav_dropdown" type="radio" name="show_friends_in_nav_dropdown" value="1" class="form-radio"
                               @if (old('show_friends_in_nav_dropdown', $user->show_friends_in_nav_dropdown) === 1) checked @endif>
                        <span class="ml-2">Yes</span>
                    </label>
                    <label class="inline-flex items-center ml-6">
                        <input id="show_friends_in_nav_dropdown" type="radio" name="show_friends_in_nav_dropdown" value="0" class="form-radio"
                               @if (old('show_friends_in_nav_dropdown', $user->show_friends_in_nav_dropdown) !== 1) checked @endif>
                        <span class="ml-2">No</span>
                    </label>
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('show_friends_in_nav_dropdown')" />

                <x-input-label class="mt-4" for="show_friends_on_others_profiles" :value="__('View Friends on Other Users\' Profiles')" />
                <div class="mt-1 text-gray-800 dark:text-gray-200">
                    <label class="inline-flex items-center">
                        <input id="show_friends_on_others_profiles" type="radio" name="show_friends_on_others_profiles" value="1" class="form-radio"
                               @if (old('show_friends_on_others_profiles', $user->show_friends_on_others_profiles) === 1) checked @endif>
                        <span class="ml-2">Yes</span>
                    </label>
                    <label class="inline-flex items-center ml-6">
                        <input id="show_friends_on_others_profiles" type="radio" name="show_friends_on_others_profiles" value="0" class="form-radio"
                               @if (old('show_friends_on_others_profiles', $user->show_friends_on_others_profiles) !== 1) checked @endif>
                        <span class="ml-2">No</span>
                    </label>
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('show_friends_on_others_profiles')" />

             <x-input-label class="mt-4" for="show_anime_notes_publicly" :value="__('Show Anime Notes Publicly')" />
                <div class="mt-1 text-gray-800 dark:text-gray-200">
                    <label class="inline-flex items-center">
                        <input id="show_anime_notes_publicly" type="radio" name="show_anime_notes_publicly" value="1" class="form-radio"
                               @if (old('show_anime_notes_publicly', $user->show_anime_notes_publicly) === 1) checked @endif>
                        <span class="ml-2">Yes</span>
                    </label>
                    <label class="inline-flex items-center ml-6">
                        <input id="show_anime_notes_publicly" type="radio" name="show_anime_notes_publicly" value="0" class="form-radio"
                               @if (old('show_anime_notes_publicly', $user->show_anime_notes_publicly) !== 1) checked @endif>
                        <span class="ml-2">No</span>
                    </label>
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('show_anime_notes_publicly')" />

            <!-- Enable Reviews System -->
            <x-input-label class="mt-4" for="enable_reviews_system" :value="__('Enable Reviews System')" />
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="enable_reviews_system" type="radio" name="enable_reviews_system" value="1" class="form-radio"
                           @if (old('enable_reviews_system', $user->enable_reviews_system) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="enable_reviews_system" type="radio" name="enable_reviews_system" value="0" class="form-radio"
                           @if (old('enable_reviews_system', $user->enable_reviews_system) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>

            <!-- Show Own Reviews When Logged In -->
            <x-input-label class="mt-4" for="show_reviews_when_logged_in" :value="__('Show Own Reviews When Logged In')" />
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="show_reviews_when_logged_in" type="radio" name="show_reviews_when_logged_in" value="1" class="form-radio"
                           @if (old('show_reviews_when_logged_in', $user->show_reviews_when_logged_in) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="show_reviews_when_logged_in" type="radio" name="show_reviews_when_logged_in" value="0" class="form-radio"
                           @if (old('show_reviews_when_logged_in', $user->show_reviews_when_logged_in) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>

            <!-- Show Own Reviews Publicly -->
            <x-input-label class="mt-4" for="show_reviews_publicly" :value="__('Show Own Reviews Publicly')" />
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="show_reviews_publicly" type="radio" name="show_reviews_publicly" value="1" class="form-radio"
                           @if (old('show_reviews_publicly', $user->show_reviews_publicly) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="show_reviews_publicly" type="radio" name="show_reviews_publicly" value="0" class="form-radio"
                           @if (old('show_reviews_publicly', $user->show_reviews_publicly) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>

            <!-- Show Others' Reviews -->
            <x-input-label class="mt-4" for="show_others_reviews" :value="__('Show Others\' Reviews')" />
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

            <!-- Show Reviews in Navigation Dropdown -->
            <x-input-label class="mt-4" for="show_reviews_in_nav_dropdown" :value="__('Show Reviews in Navigation Dropdown')" />
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="show_reviews_in_nav_dropdown" type="radio" name="show_reviews_in_nav_dropdown" value="1" class="form-radio"
                           @if (old('show_reviews_in_nav_dropdown', $user->show_reviews_in_nav_dropdown) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="show_reviews_in_nav_dropdown" type="radio" name="show_reviews_in_nav_dropdown" value="0" class="form-radio"
                           @if (old('show_reviews_in_nav_dropdown', $user->show_reviews_in_nav_dropdown) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>



            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
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
        document.getElementById('avatar').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
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

        document.getElementById('delete-avatar-button').addEventListener('click', function() {
            document.getElementById('confirmation-modal').classList.remove('hidden');
        });

        window.addEventListener("click", function(event) {
            if (event.target === document.getElementById("confirmation-modal")) {
                closeModal();
            }
        });

        window.addEventListener("keydown", function(e) {
            if (e.key === "Escape") {
                closeModal();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
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
                radio.addEventListener('change', function() {
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
                radio.addEventListener('change', function() {
                    setReviewFieldsDisabled(this.value === '0');
                });
            });

            // Initialize review settings on load
            setReviewFieldsDisabled(document.querySelector('input[name="enable_reviews_system"]:checked').value === '0');
        });

    </script>
</section>
