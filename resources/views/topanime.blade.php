<x-app-layout>
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
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Title</th>
                                <th>MAL Score</th>
                                @if (Auth::user() != null)
                                    <th>My Score</th>
                                    <th>Status</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topAnime as $index => $anime)
                                <tr>
                                    <td>{{ (($topAnime->currentPage() - 1 ) * $topAnime->perPage() ) + $loop->iteration }}</td>
                                    <td>
                                        <img src="{{ $anime->thumbnail }}" alt="{{ $anime->title }}" class="inline-block" width="50" height="70">
                                        <a href="{{ route('anime.detail', $anime->id) }}" class="ml-4">{{ $anime->title }}</a>
                                    </td>
                                    <td>{{ $anime->mal_mean }}</td>
                                    @if (Auth::user() != null)
                                        <td>{{ $userScores[$anime->id] ?? 'N/A' }}</td>
                                        <td>
                                            @if (Auth::user())
                                                @php
                                                    $watchStatusId = $userAnimeStatuses[$anime->id] ?? null;
                                                @endphp
                                                <div class="no_dropdown_arrow_blank_select-wrapper bg-blue-500">
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
