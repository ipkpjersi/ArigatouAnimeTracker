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
                        <img onerror="this.onerror=null; this.src='/img/notfound.gif';" class="rounded-lg shadow-md mb-3" style="width:150px; height: 150px;" src="{{ $user->avatar }}" alt="{{ $user->username }}" />
                        <p><strong>Joined:</strong> {{ \Carbon\Carbon::parse($user->created_at)->format('M d, Y') }}</p>
                        @if ($user->is_admin === 1)
                            <p><strong>Role:</strong> {{ 'Admin' }}</p>
                        @endif
                        <a href="/animelist/{{ $user->username }}" class="inline-block">
                            <button id="animeListButton" class="p-2 bg-blue-500 hover:bg-blue-700 text-white rounded-md mt-2">Anime List</button>
                        </a>
                    </div>

                    <!-- Right Column -->
                    <div class="w-full md:w-3/5 mt-0 flex flex-row flex-wrap">
                        <!-- Left sub-column for days watched and watch status -->
                        <div class="w-full md:w-56">
                            <h5 class="font-bold mb-2">Days Watched:</h5>
                            <p>{{ number_format($stats['totalDaysWatched'], 2) }} days</p>

                            <h5 class="font-bold mt-4 mb-2">Status Counts:</h5>
                            <ul>
                                @foreach($stats['animeStatusCounts'] as $status => $count)
                                    <li>{{ $status }}: {{ $count }}</li>
                                @endforeach
                            </ul>
                        </div>

                        <!-- Right sub-column for total completed, total episodes watched, and average score -->
                        <div class="w-full md:w-3/5">
                            <h4 class="font-bold mb-2">Statistics:</h4>
                            <p>Total Anime Completed: {{ $stats['totalCompleted'] }}</p>
                            <p>Total Episodes Watched: {{ $stats['totalEpisodes'] }}</p>
                            <p>Average Score: {{ number_format($stats['averageScore'], 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
