<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $user->username }}'s Profile
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 flex flex-wrap">
                    <!-- Left Column -->
                    <div class="w-full md:w-56 mb-6 md:mb-0 md:mr-6 flex-none mt-0">
                        <h3 class="font-bold mb-1">{{ $user->username }}</h3>
                        <img onerror="this.onerror=null; this.src='/img/notfound.gif';" class="rounded-lg shadow-md mb-3" src="{{ $user->avatar }}" alt="{{ $user->username }}" />
                        <p><strong>Joined:</strong> {{ \Carbon\Carbon::parse($user->created_at)->format('M d, Y') }}</p>
                        @if ($user->is_admin === 1)
                            <p><strong>Role:</strong> {{ 'Admin' }}</p>
                        @endif
                        <button id="animeListButton" class="p-2 bg-blue-500 text-white rounded-md mt-2">Anime List</button>
                    </div>

                    <!-- Right Column -->
                    <div class="w-full md:w-3/5 mt-0">
                        <h4 class="font-bold mb-2">Statistics:</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
