<x-app-layout>
    <x-slot name="title">
        {{ config('app.name', 'Laravel') }} - Top Anime by MAL Score
    </x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Top Anime by MAL Score
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div id="status-modal" class="hidden fixed top-0 left-0 w-full h-full bg-opacity-50 bg-black flex justify-center items-center z-50">
                      <div class="p-4 bg-white dark:bg-black rounded">
                        <p id="status-message"></p>
                      </div>
                    </div>
                    <div class="mb-4 flex border-b">
                        <a href="{{ route('anime.top', ['sort' => 'highest_rated']) }}" class="tab-button {{ request()->get('sort') == 'highest_rated' || !request()->get('sort') ? 'bg-blue-500 text-white' : 'dark:text-white' }} py-2 px-4">Highest Rated</a>
                        <a href="{{ route('anime.top', ['sort' => 'most_popular']) }}" class="tab-button {{ request()->get('sort') == 'most_popular' ? 'bg-blue-500 text-white' : 'dark:text-white' }} py-2 px-4">Most Popular</a>
                    </div>
                    <table class="w-full">
                        <thead>
                            <tr class="text-left">
                                <th>Rank</th>
                                <th>Title</th>
                                <th>Year</th>
                                <th>Season</th>
                                <th>MAL Score</th>
                                <th>MAL Members</th>
                                @if (Auth::user() != null)
                                    <th>My Score</th>
                                    <th>Status</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topAnime as $index => $anime)
                                <tr class="border-b-4 border-transparent">
                                    <td>{{ (($topAnime->currentPage() - 1 ) * $topAnime->perPage() ) + $loop->iteration }}</td>
                                    <td>
                                        <a href="{{ route('anime.detail', $anime->id) }}">
                                            <img src="{{ $anime->picture }}" alt="{{ $anime->title }}" class="inline-block" width="50" height="70">
                                            <span class="ml-4">{{ $anime->title }}</span>
                                        </a>
                                    </td>
                                    <td>{{ $anime->year }}</td>
                                    <td>{{ $anime->season_display }}</td>
                                    <td>{{ $anime->mal_mean ?? "N/A" }}</td>
                                    <td>{{ number_format($anime->mal_list_members ?? 0) }}</td>
                                    @if (Auth::user() != null)
                                        <td>{{ !empty($userScores[$anime->id]) ? $userScores[$anime->id] : 'N/A' ?? 'N/A' }}</td>
                                        <td>
                                            @if (Auth::user())
                                                @php
                                                    $watchStatusId = $userAnimeStatuses[$anime->id] ?? null;
                                                @endphp
                                                <div class="no_dropdown_arrow_blank_select-wrapper @if ($watchStatusId === null) bg-blue-500 @else bg-gray-500 @endif">
                                                    <select
                                                        class="text-sm text-white rounded p-1 flex-1 focus:outline-none z-50 update-anime-status no_dropdown_arrow_blank"
                                                        data-anime-id="{{ $anime->id }}"
                                                    >
                                                        <option value="0">{{ 'Add to List' }}</option>
                                                        @foreach ($watchStatuses as $id => $status)
                                                            <option value="{{ $id }}" {{ $watchStatusId == $id ? 'selected' : '' }}>{{ $status->status }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                    <div id="paginationDiv" class="mt-4">
                        {{ $topAnime->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        @if (Auth::user() != null) {
            document.addEventListener('DOMContentLoaded', () => {
                const statusSelects = document.querySelectorAll('.update-anime-status');

                statusSelects.forEach(select => {
                    select.addEventListener('change', async (event) => {
                        const animeId = event.target.getAttribute('data-anime-id');
                        const watchStatusId = event.target.value;
                        let username = '{{ Auth::user()->username }}';
                        // Make an AJAX request
                        const response = await fetch(`/animelist/${username}/update-anime-status`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}', // replace with actual CSRF token
                            },
                            body: JSON.stringify({ anime_id: animeId, watch_status_id: watchStatusId })
                        });

                        const data = await response.json();

                        // Display the modal or flash message
                        document.getElementById('status-message').innerText = data.message;
                        document.getElementById('status-modal').classList.remove('hidden');

                        // Hide the modal after 3 seconds
                        setTimeout(() => {
                            document.getElementById('status-modal').classList.add('hidden');
                        }, 3000);
                    });
                });
            });
        }
        @endif
    </script>
</x-app-layout>
