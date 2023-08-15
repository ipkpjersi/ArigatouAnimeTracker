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

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
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

            <x-input-label class="mt-4" for="avatar" :value="__('Avatar')" />
            <input id="avatar" name="avatar" type="file" class="mt-1 block w-full" value="old('avatar', $user->avatar)" autocomplete="avatar" />
            <x-input-error class="mt-2" :messages="$errors->get('avatar')" />

            <x-input-label class="mt-4" for="dark_mode" :value="__('Dark Mode')" />
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="dark_mode" type="radio" name="dark_mode" value="1" class="form-radio"
                           @if(old('dark_mode', $user->dark_mode) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="dark_mode" type="radio" name="dark_mode" value="0" class="form-radio"
                           @if(old('dark_mode', $user->dark_mode) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('dark_mode')" />

            <x-input-label class="mt-4" for="show_adult_content" :value="__('Show Adult Content')" />
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="show_adult_content" type="radio" name="show_adult_content" value="1" class="form-radio"
                           @if(old('show_adult_content', $user->show_adult_content) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="show_adult_content" type="radio" name="show_adult_content" value="0" class="form-radio"
                           @if(old('show_adult_content', $user->show_adult_content) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('show_adult_content')" />

            <x-input-label class="mt-4" for="anime_list_pagination_size" :value="__('Anime List Pagination Size')" />
            <x-text-input  id="anime_list_pagination_size"  name="anime_list_pagination_size"  type="number"  min="2"  max="250" class="mt-1 block w-full"  :value="old('anime_list_pagination_size', $user->anime_list_pagination_size)"
                required />
            <x-input-error class="mt-2" :messages="$errors->get('anime_list_pagination_size')" />

            <x-input-label class="mt-4" for="show_anime_list_number" :value="__('Show Number of Current Anime in List')" />
            <div class="mt-1 text-gray-800 dark:text-gray-200">
                <label class="inline-flex items-center">
                    <input id="show_anime_list_number" type="radio" name="show_anime_list_number" value="1" class="form-radio"
                           @if(old('show_anime_list_number', $user->show_anime_list_number) === 1) checked @endif>
                    <span class="ml-2">Yes</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input id="show_anime_list_number" type="radio" name="show_anime_list_number" value="0" class="form-radio"
                           @if(old('show_anime_list_number', $user->show_anime_list_number) !== 1) checked @endif>
                    <span class="ml-2">No</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('show_anime_list_number')" />


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
</section>
