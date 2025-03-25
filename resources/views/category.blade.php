<x-app-layout>
    <x-slot name="title">
        {{ config('app.name', 'Laravel') }} - {{ ucwords($category) }} Anime
    </x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ ucwords($category) }} Anime
        </h2>
        <a href="{{ route('anime.category', ['category' => $category, 'view' => 'list'] + request()->query()) }}">List View</a> |
        <a href="{{ route('anime.category', ['category' => $category, 'view' => 'card'] + request()->query()) }}">Card View</a>

        <select id="sort-dropdown" class="ml-4 dark:bg-gray-800 bg-white rounded">
            <option value="mal_score" {{ request()->get('sort') == 'mal_score' ? 'selected' : '' }}>MAL Score</option>
            <option value="mal_members" {{ request()->get('sort') == 'mal_members' ? 'selected' : '' }}>MAL Members</option>
            <option value="newest" {{ request()->get('sort') == 'newest' ? 'selected' : '' }}>Newest</option>
            <option value="title" {{ request()->get('sort') == 'title' ? 'selected' : '' }}>Title</option>
        </select>
        @if ($isSeasonal)
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mt-4 mb-4 gap-4">
                <div class="flex flex-col space-y-2 sm:flex-row sm:space-x-4 sm:space-y-0">
                    @foreach ($paginationSeasons as $key => $seasonData)
                        <a href="{{ route('anime.category', ['category' => 'seasonal', 'season' => $seasonData['season'], 'year' => $seasonData['year'], 'view' => request('view')] + request()->except(['season', 'year'])) }}"
                           class="inline-flex w-fit px-3 py-1 rounded {{ $key === 'current' ? 'bg-blue-600 text-white' : 'bg-gray-300 dark:bg-gray-600 hover:bg-blue-500 hover:text-white' }}">
                            {{ ucfirst(strtolower($seasonData['season'])) }} {{ $seasonData['year'] }}
                        </a>
                    @endforeach
                    @if (!($currentSeason === $calendarSeason && $currentYear == $calendarYear))
                        <a href="{{ route('anime.category', ['category' => 'seasonal', 'view' => request('view')] + request()->except(['season', 'year'])) }}"
                           class="inline-flex w-fit px-3 py-1 rounded border border-gray-300 dark:border-gray-600 bg-gray-300 dark:bg-gray-600 text-black dark:text-white hover:bg-blue-500 hover:text-white transition">
                            Current Season
                        </a>
                    @endif
                </div>

                <div class="flex flex-col space-y-2 sm:flex-row sm:space-x-2 sm:space-y-0 mt-2 md:mt-0">
                    <a href="{{ route('anime.category', ['category' => 'seasonal', 'view' => request('view')] + request()->except('type')) }}"
                       class="inline-flex w-fit px-2 py-1 rounded {{ !$animeTypeId ? 'bg-blue-600 text-white' : 'bg-gray-300 dark:bg-gray-600 hover:bg-blue-500 hover:text-white' }}">
                        All
                    </a>
                    @foreach ($animeTypes as $type)
                        <a href="{{ route('anime.category', ['category' => 'seasonal', 'view' => request('view'), 'type' => $type->id] + request()->except('type')) }}"
                           class="inline-flex w-fit px-2 py-1 rounded {{ $animeTypeId == $type->id ? 'bg-blue-600 text-white' : 'bg-gray-300 dark:bg-gray-600 hover:bg-blue-500 hover:text-white' }}">
                            {{ $type->type }}
                        </a>
                    @endforeach
                </div>
            </div>
        @else
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mt-4 mb-4 gap-4">
                <div class="flex flex-col space-y-2 sm:flex-row sm:space-x-2 sm:space-y-0 mt-2 md:mt-0">
                    <a href="{{ route('anime.category', ['category' => request('category'), 'view' => request('view')] + request()->except('type')) }}"
                       class="inline-flex w-fit px-2 py-1 rounded {{ !$animeTypeId ? 'bg-blue-600 text-white' : 'bg-gray-300 dark:bg-gray-600 hover:bg-blue-500 hover:text-white' }}">
                        All
                    </a>
                    @foreach ($animeTypes as $type)
                        <a href="{{ route('anime.category', ['category' => request('category'), 'view' => request('view'), 'type' => $type->id] + request()->except('type')) }}"
                           class="inline-flex w-fit px-2 py-1 rounded {{ $animeTypeId == $type->id ? 'bg-blue-600 text-white' : 'bg-gray-300 dark:bg-gray-600 hover:bg-blue-500 hover:text-white' }}">
                            {{ $type->type }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
        @if ($category === 'seasonal' || $category === 'all')
            <div x-data="{ showFilters: false }" class="mb-4">
                <button @click="showFilters = !showFilters"
                    class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-black dark:text-white rounded hover:bg-blue-500 hover:text-white">
                    Filters
                </button>

                <form method="GET" id="category-filter-form" action="{{ route('anime.category', ['category' => $category, 'view' => request('view')]) }}"
                      x-show="showFilters"
                      x-transition
                      x-cloak
                      class="mt-4 flex flex-wrap gap-2">

                    @foreach ($allCategories as $cat)
                        <label class="inline-flex items-center space-x-1">
                            <input type="checkbox" class="category-checkbox text-blue-600 mr-1 ml-2" value="{{ $cat }}"
                                   {{ in_array($cat, explode(',', request()->get('categories', ''))) ? 'checked' : '' }}>
                            <span>{{ $cat }}</span>
                        </label>
                    @endforeach

                    @foreach (request()->except('categories') as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach

                    <input type="hidden" name="categories" id="category-string">

                    <button type="submit"
                            class="ml-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Filter
                    </button>
                </form>
            </div>
        @endif
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

                    @if ($view === 'list')
                        <table class="w-full">
                            <thead>
                                <tr class="text-left">
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Eps.</th>
                                    <th>Year</th>
                                    <th>Season</th>
                                    <th>MAL Score</th>
                                    <th>MAL Members</th>
                                    @if (Auth::user() != null)
                                        <th>Status</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($categoryAnime as $anime)
                                    <tr class="border-b-4 border-transparent">
                                        <td>
                                            <img src="{{ $anime->picture }}" alt="{{ $anime->title }}" class="inline-block" width="50" height="70" onerror="this.onerror=null; this.src='/img/notfound.gif';">
                                            <a href="{{ route('anime.detail', $anime->id) }}" class="ml-4">{{ $anime->title }}</a>
                                        </td>
                                        <td>{{ $anime->anime_type->type }}</td>
                                        <td>{{ $anime->episodes }}</td>
                                        <td>{{ $anime->year }}</td>
                                        <td>{{ $anime->season_display }}</td>
                                        <td>{{ $anime->mal_mean ?? "N/A" }}</td>
                                        <td>{{ $anime->mal_list_members ?? 0 }}</td>
                                        @if (Auth::user())
                                            <td>
                                                @php
                                                    $userAnime = $anime->user->firstWhere('id', Auth::id());
                                                    $watchStatusId = $userAnime?->pivot->watch_status_id ?? null;
                                                    $selectedStatus = $watchStatusId ? $watchStatuses[$watchStatusId] : null;
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
                                        <img src="{{ $anime->picture }}" alt="{{ $anime->title }}" width="100" height="140" class="rounded mb-4" onerror="this.onerror=null; this.src='/img/notfound.gif';">
                                        <p class="text-sm text-gray-600">{{ Str::limit($anime->description, 300) }}</p>
                                        </a>
                                    </div>
                                    <div class="flex items-center relative z-20">
                                        <span class="text-sm dark:text-gray-300">MAL Score: <span class="whitespace-nowrap">{{ $anime->mal_mean ?? "N/A" }}</span></span>
                                        <span class="text-sm dark:text-gray-300">MAL Members: <span class="whitespace-nowrap">{{ $anime->mal_list_members ?? "N/A" }}</span></span>
                                        @if (Auth::user())
                                            @php
                                                $userAnime = $anime->user->firstWhere('id', Auth::id());
                                                $watchStatusId = $userAnime?->pivot->watch_status_id ?? null;
                                                $selectedStatus = $watchStatusId ? $watchStatuses[$watchStatusId] : null;
                                            @endphp
                                        <div class="no_dropdown_arrow_blank_select-wrapper @if ($watchStatusId === null) bg-blue-500 @else bg-gray-500 @endif">
                                            <select
                                                class="text-sm text-white rounded p-1 flex-1 focus:outline-none z-50 update-anime-status no_dropdown_arrow_blank_nowidth"
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
        document.getElementById('sort-dropdown').addEventListener('change', function() {
            const selectedSort = this.value;
            const url = new URL(window.location.href);
            url.searchParams.set('sort', selectedSort);
            window.location.href = url.toString();
        });
        const filterForm = document.getElementById('category-filter-form');
        const filterCategoryString = document.getElementById('category-string');

        if (filterForm && filterCategoryString) {
            filterForm.addEventListener('submit', () => {
                const selected = Array.from(document.querySelectorAll('.category-checkbox:checked'))
                    .map(el => el.value);
                filterCategoryString.value = selected.join(',');
            });
        }
    </script>
    <style>
        /* Hide any x-cloak elements by default (on page load) */
        [x-cloak] { display: none !important; }
    </style>
</x-app-layout>
