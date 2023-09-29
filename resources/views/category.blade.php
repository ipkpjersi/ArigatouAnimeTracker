<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $category }} Anime
        </h2>
        <a href="{{ route('anime.category', ['category' => $category, 'view' => 'list'] + request()->query()) }}">List View</a> |
        <a href="{{ route('anime.category', ['category' => $category, 'view' => 'card'] + request()->query()) }}">Card View</a>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="dark:bg-gray-800 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div id="status-modal" class="hidden fixed top-0 left-0 w-full h-full bg-opacity-50 bg-black flex justify-center items-center z-50">
                      <div class="p-4 bg-white dark:bg-black rounded">
                        <p id="status-message"></p>
                      </div>
                    </div>

                    @if ($viewType === 'list')
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Eps.</th>
                                    <th>MAL Score</th>
                                    <th>Start Date</th>
                                    <th>MAL List Members</th>
                                    @if (Auth::user() != null)
                                        <th>Status</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($categoryAnime as $anime)
                                    <tr>
                                        <td>
                                            <img src="{{ $anime->thumbnail }}" alt="{{ $anime->title }}" class="inline-block" width="50" height="70">
                                            <a href="{{ route('anime.detail', $anime->id) }}" class="ml-4">{{ $anime->title }}</a>
                                        </td>
                                        <td>{{ $anime->anime_type->type }}</td>
                                        <td>{{ $anime->episodes }}</td> <!-- Assuming column name is episode_count -->
                                        <td>{{ $anime->mal_mean }}</td>
                                        <td>{{ Carbon\Carbon::parse($anime->start_date)->format('Y-m-d') }}</td> <!-- Assuming start_date is a Carbon instance -->
                                        <td>{{ $anime->mal_list_members }}</td>
                                        @if (Auth::user())
                                            <td>
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
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-4">
                            @foreach ($categoryAnime as $anime)
                                <div class="m-2 p-4 rounded-lg shadow-lg bg-gray-100 dark:bg-gray-700 flex flex-col justify-between min-h-[300px] relative">
                                    <div class="relative z-20">
                                        <a href="{{ route("anime.detail", $anime->id) }}">
                                        <h3 class="text-xl font-semibold mb-2">{{ $anime->title }}</h3>
                                        <img src="{{ $anime->picture }}" alt="{{ $anime->title }}" width="100" height="140" class="rounded mb-4">
                                        <p class="text-sm text-gray-600">{{ Str::limit($anime->description, 300) }}</p>
                                        </a>
                                    </div>
                                    <div class="flex items-center relative z-20">
                                        <span class="text-sm dark:text-gray-300 flex-1">MAL Score: {{ $anime->mal_mean ?? "N/A" }}</span>
                                        <span class="text-sm dark:text-gray-300 flex-1">MAL Members: {{ $anime->mal_list_members ?? "N/A" }}</span>
                                        @if (Auth::user())
                                            @php
                                                $userAnime = $anime->user->firstWhere('id', Auth::id());
                                                $watchStatusId = optional($userAnime)->pivot->watch_status_id ?? null;
                                                $selectedStatus = $watchStatusId ? $watchStatuses[$watchStatusId] : null;
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
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            <div id="paginationDiv" class="mt-4">
                {{ $categoryAnime->links() }}
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
