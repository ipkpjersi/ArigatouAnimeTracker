<x-app-layout>
    <x-slot name="title">
        {{ config('app.name', 'Laravel') }} - User Anime List for {{ $username }}
    </x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            User Anime List for {{ $username }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-[1700px] mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div id="clearModal" class="fixed top-0 left-0 w-full h-full bg-opacity-50 bg-black flex justify-center items-center z-50 hidden overflow-y-auto">
                    <div class="bg-white dark:bg-gray-700 rounded relative w-128 h-64">
                        <button id="closeModal" class="absolute top-2 right-4 bg-red-500 text-white p-2 pl-4 pr-4 mb-2 rounded">X</button>
                        <div class="p-4 mt-10">
                            <p id="clearAnimeText"></p>
                            <div class="flex items-center mt-2">
                              <input type="checkbox" id="confirmCheckbox">
                              <label for="confirmCheckbox" class="ml-2">I understand the consequences</label>
                            </div>
                            <input type="text" id="confirmUsername" placeholder="Enter your username to confirm" class="bg-white dark:bg-gray-800 mt-3 w-full">
                            <button id="confirmClear" class="bg-red-500 text-white p-2 pl-6 pr-6 rounded mt-4">Yes</button>
                            <button id="cancelClear" class="bg-gray-500 text-white p-2 pl-6 pr-6 rounded mt-4 ml-3">No</button>
                            <div id="errorText" class="text-red-500 hidden mt-1"></div>
                        </div>
                    </div>
                </div>
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('user.anime.update', ['username' => $username]) }}" method="POST">
                        @csrf
                        <div class="overflow-x-auto double-scroll">
                            <table class="min-w-full">
                                <thead>
                                    <!-- desktop design -->
                                    <tr class="hidden md:table-row">
                                        @if ($show_anime_list_number)
                                            <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">#</th>
                                        @endif
                                        @if (auth()->user() != null && strtolower(auth()->user()->username) === strtolower($username))
                                            <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200 w-8"></th>
                                        @endif
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Picture</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Name</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Type</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200 sm:">Status</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200 min-w-[165px]">Watch Status</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Progress</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200 min-w-[70px]">Score</th>
                                        @if (auth()->user() != null && strtolower(auth()->user()->username) === strtolower($username))
                                            <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Sort Order</th>
                                        @endif
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Episodes</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Season</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Year</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Notes</th>
                                        @if (auth()->user() != null && strtolower(auth()->user()->username) === strtolower($username))
                                            <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Delete</th>
                                        @endif
                                    </tr>
                                    <!-- mobile design -->
                                    <tr class="md:hidden">
                                        @if ($show_anime_list_number)
                                            <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">#</th>
                                        @endif
                                        @if (auth()->user() != null && strtolower(auth()->user()->username) === strtolower($username))
                                            <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200 w-8"></th>
                                        @endif
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Picture</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Name</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200 min-w-[70px]">Score</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Episodes</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Season</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Year</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Type</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200 sm:">Status</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200 min-w-[165px]">Watch Status</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Progress</th>
                                        @if (auth()->user() != null && strtolower(auth()->user()->username) === strtolower($username))
                                            <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Sort Order</th>
                                        @endif
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Notes</th>
                                        @if (auth()->user() != null && strtolower(auth()->user()->username) === strtolower($username))
                                            <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Delete</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody id="desktop-tbody">
                                    <!-- desktop design -->
                                    @foreach ($userAnime as $anime)
                                        <tr id="desktop-row-{{ (($userAnime->currentPage() - 1) * $userAnime->perPage()) + $loop->iteration }}" class="hidden md:table-row" data-anime-id="{{ $anime->id }}" data-watch-status="{{ $anime->pivot->watch_status_id }}">
                                            <input type="hidden" class="desktop-anime-ids desktop-only" name="anime_ids[]" value="{{ $anime->id }}">
                                            @if ($show_anime_list_number)
                                                <td class="py-2 px-4 border-b border-gray-200">{{ (($userAnime->currentPage() - 1) * $userAnime->perPage()) + $loop->iteration }}</td>
                                            @endif
                                            @if (auth()->user() != null && strtolower(auth()->user()->username) === strtolower($username))
                                                @php
                                                    $completedStatusId = $watchStatuses->where('status', 'COMPLETED')->first()?->id;
                                                    $isCompleted = $anime->pivot->watch_status_id == $completedStatusId;
                                                @endphp
                                                <td class="py-2 px-4 border-b border-gray-200 {{ $isCompleted ? 'drag-handle cursor-move hover:text-gray-400 text-gray-600 dark:text-gray-400 dark:hover:text-gray-300' : 'cursor-not-allowed text-gray-400 dark:text-gray-600' }}">
                                                    <span class="text-xl">{{ $isCompleted ? '⋮⋮' : '•' }}</span>
                                                </td>
                                            @endif
                                            <td class="py-2 px-4 border-b border-gray-200">
                                                <img src="{{ $anime->picture }}" alt="{{ $anime->title }} thumbnail" width="50" height="50" onerror="this.onerror=null; this.src='{{ asset('img/notfound.gif') }}'">
                                            </td>
                                            <td class="py-2 px-4 border-b border-gray-200"><a href="/anime/{{$anime->id}}">{{ $anime->title }}</a></td>
                                            <td class="py-2 px-4 border-b border-gray-200">{{ $anime->anime_type?->type }}</td>
                                            <td class="py-2 px-4 border-b border-gray-200">{{ $anime->anime_status?->status }}</td>
                                            <td class="py-2 px-4 border-b border-gray-200">
                                                @if (auth()->user() != null && strtolower(auth()->user()->username) === strtolower($username))
                                                    <select name="watch_status_id[]" class="border rounded w-full py-2 px-3 dark:bg-gray-800  min-w-[100px] desktop-only" style="padding-right: 36px">
                                                        <option value="">Pick a status...</option>
                                                        @foreach ($watchStatuses as $status)
                                                            <option value="{{ $status->id }}" @if ($anime->pivot->watch_status_id == $status->id) selected @endif>
                                                                {{ $status->status }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    {{ $watchStatusMap[$anime->pivot->watch_status_id] ?? 'UNKNOWN' }}
                                                @endif
                                            </td>
                                            <td class="py-2 px-4 border-b border-gray-200">
                                                @if (auth()->user() != null && strtolower(auth()->user()->username) === strtolower($username))
                                                    <select name="progress[]" class="border rounded w-full py-2 px-3 dark:bg-gray-800 desktop-only" style="padding-right: 36px">
                                                        <option value="">Pick an option...</option>
                                                        @for ($i = 1; $i <= $anime->episodes; $i++)
                                                            <option value="{{ $i }}" @if ($anime->pivot->progress == $i) selected @endif>
                                                                {{ $i }}
                                                            </option>
                                                        @endfor
                                                    </select>
                                                @else
                                                    {{ $anime->progress ?? '0' }}
                                                @endif
                                            </td>
                                            <td class="py-2 px-4 border-b border-gray-200 min-w-[70px]">
                                                @if (auth()->user() != null && strtolower(auth()->user()->username) === strtolower($username))
                                                    <select name="score[]" class="border rounded w-full py-2 px-3 dark:bg-gray-800 min-w-[70px] desktop-only" style="padding-right: 36px">
                                                        <option value="">Pick an option...</option>
                                                        @for ($i = 1; $i <= 10; $i++)
                                                            <option value="{{ $i }}" @if ($anime->pivot->score == $i) selected @endif>{{ $i }}</option>
                                                        @endfor
                                                    </select>
                                                @else
                                                    {{ $anime->pivot->score ?? 'UNKNOWN' }}
                                                @endif
                                            </td>
                                            @if (auth()->user() != null && strtolower(auth()->user()->username) === strtolower($username))
                                                <td class="py-2 px-4 border-b border-gray-200">
                                                    <div class="flex items-center desktop-only">
                                                        <input type="number" min="1" name="sort_order[]" value="{{ $anime->pivot->sort_order }}" class="border rounded w-24 py-2 px-3 dark:bg-gray-800">
                                                        <!-- Up Arrow -->
                                                        <button onclick="swapAndSubmitSortOrder({{ (($userAnime->currentPage() - 1) * $userAnime->perPage()) + $loop->iteration }}, 'up')" class="ml-2 text-gray-600 hover:text-gray-800">
                                                            ⬆️
                                                        </button>
                                                        <!-- Down Arrow -->
                                                        <button onclick="swapAndSubmitSortOrder({{ (($userAnime->currentPage() - 1) * $userAnime->perPage()) + $loop->iteration }}, 'down')" class="ml-2 text-gray-600 hover:text-gray-800">
                                                            ⬇️
                                                        </button>
                                                    </div>
                                                </td>
                                            @endif
                                            <td class="py-2 px-4 border-b border-gray-200">{{ $anime->episodes }}</td>
                                            <td class="py-2 px-4 border-b border-gray-200">{{ $anime->season }}</td>
                                            <td class="py-2 px-4 border-b border-gray-200">{{ $anime->year }}</td>
                                            <td class="py-2 px-4 border-b border-gray-200">
                                                <textarea name="notes[]" class="border rounded w-full py-2 px-3 dark:bg-gray-800 desktop-only" {{ (auth()->user() === null || strtolower(auth()->user()->username) !== strtolower($username)) ? 'readonly' : '' }}>{{ $anime->notes ?? '' }}</textarea>
                                            </td>
                                            <td class="py-2 px-4 border-b border-gray-200">
                                                @if (auth()->user() != null && strtolower(auth()->user()->username) === strtolower($username))
                                                    <button
                                                        onclick="deleteAnime({{ $anime->id }}, event)"
                                                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded"
                                                    >
                                                        Delete
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tbody id="mobile-tbody">
                                    <!-- mobile design -->
                                    @foreach ($userAnime as $anime)
                                        <tr id="mobile-row-{{ (($userAnime->currentPage() - 1) * $userAnime->perPage()) + $loop->iteration }}" class="md:hidden mobile-only" data-anime-id="{{ $anime->id }}" data-watch-status="{{ $anime->pivot->watch_status_id }}">
                                            <input type="hidden" class="mobile-anime-ids mobile-only" name="anime_ids[]" value="{{ $anime->id }}">
                                            @if ($show_anime_list_number)
                                                <td class="py-2 px-4 border-b border-gray-200">{{ (($userAnime->currentPage() - 1) * $userAnime->perPage()) + $loop->iteration }}</td>
                                            @endif
                                            @if (auth()->user() != null && strtolower(auth()->user()->username) === strtolower($username))
                                                @php
                                                    $completedStatusId = $watchStatuses->where('status', 'COMPLETED')->first()?->id;
                                                    $isCompleted = $anime->pivot->watch_status_id == $completedStatusId;
                                                @endphp
                                                <td class="py-2 px-4 border-b border-gray-200 {{ $isCompleted ? 'drag-handle cursor-move hover:text-gray-400 text-gray-600 dark:text-gray-400 dark:hover:text-gray-300' : 'cursor-not-allowed text-gray-400 dark:text-gray-600' }}">
                                                    <span class="text-xl">{{ $isCompleted ? '⋮⋮' : '•' }}</span>
                                                </td>
                                            @endif
                                            <td class="py-2 px-4 border-b border-gray-200">
                                                <img src="{{ $anime->picture }}" alt="{{ $anime->title }} thumbnail" width="50" height="50" onerror="this.onerror=null; this.src='{{ asset('img/notfound.gif') }}'">
                                            </td>
                                            <td class="py-2 px-4 border-b border-gray-200"><a href="/anime/{{$anime->id}}">{{ $anime->title }}</a></td>
                                            <td class="py-2 px-4 border-b border-gray-200 min-w-[70px]">
                                                @if (auth()->user() != null && strtolower(auth()->user()->username) === strtolower($username))
                                                    <select name="score[]" class="border rounded w-full py-2 px-3 dark:bg-gray-800 min-w-[70px] mobile-only" style="padding-right: 36px">
                                                        <option value="">Pick an option...</option>
                                                        @for ($i = 1; $i <= 10; $i++)
                                                            <option value="{{ $i }}" @if ($anime->pivot->score == $i) selected @endif>{{ $i }}</option>
                                                        @endfor
                                                    </select>
                                                @else
                                                    {{ $anime->pivot->score ?? 'UNKNOWN' }}
                                                @endif
                                            </td>
                                            <td class="py-2 px-4 border-b border-gray-200">{{ $anime->episodes }}</td>
                                            <td class="py-2 px-4 border-b border-gray-200">{{ $anime->season }}</td>
                                            <td class="py-2 px-4 border-b border-gray-200">{{ $anime->year }}</td>
                                            <td class="py-2 px-4 border-b border-gray-200">{{ $anime->anime_type?->type }}</td>
                                            <td class="py-2 px-4 border-b border-gray-200">{{ $anime->anime_status?->status }}</td>
                                            <td class="py-2 px-4 border-b border-gray-200 min-w-[150px]">
                                                @if (auth()->user() != null && strtolower(auth()->user()->username) === strtolower($username))
                                                    <select name="watch_status_id[]" class="border rounded w-full py-2 px-3 dark:bg-gray-800 min-w-[150px] mobile-only" style="padding-right: 36px">
                                                        <option value="">Pick a status...</option>
                                                        @foreach ($watchStatuses as $status)
                                                            <option value="{{ $status->id }}" @if ($anime->pivot->watch_status_id == $status->id) selected @endif>
                                                                {{ $status->status }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    {{ $watchStatusMap[$anime->pivot->watch_status_id] ?? 'UNKNOWN' }}
                                                @endif
                                            </td>
                                            <td class="py-2 px-4 border-b border-gray-200">
                                                @if (auth()->user() != null && strtolower(auth()->user()->username) === strtolower($username))
                                                    <select name="progress[]" class="border rounded w-full py-2 px-3 dark:bg-gray-800 mobile-only" style="padding-right: 36px">
                                                        <option value="">Pick an option...</option>
                                                        @for ($i = 1; $i <= $anime->episodes; $i++)
                                                            <option value="{{ $i }}" @if ($anime->pivot->progress == $i) selected @endif>
                                                                {{ $i }}
                                                            </option>
                                                        @endfor
                                                    </select>
                                                @else
                                                    {{ $anime->progress ?? '0' }}
                                                @endif
                                            </td>
                                            @if (auth()->user() != null && strtolower(auth()->user()->username) === strtolower($username))
                                                <td class="py-2 px-4 border-b border-gray-200">
                                                    <div class="flex items-center mobile-only">
                                                        <input type="number" min="1" name="sort_order[]" value="{{ $anime->pivot->sort_order }}" class="border rounded w-24 py-2 px-3 dark:bg-gray-800">
                                                        <!-- Up Arrow -->
                                                        <button onclick="swapAndSubmitSortOrder({{ (($userAnime->currentPage() - 1) * $userAnime->perPage()) + $loop->iteration }}, 'up')" class="ml-2 text-gray-600 hover:text-gray-800">
                                                            ⬆️
                                                        </button>
                                                        <!-- Down Arrow -->
                                                        <button onclick="swapAndSubmitSortOrder({{ (($userAnime->currentPage() - 1) * $userAnime->perPage()) + $loop->iteration }}, 'down')" class="ml-2 text-gray-600 hover:text-gray-800">
                                                            ⬇️
                                                        </button>
                                                    </div>
                                                </td>
                                            @endif
                                            <td class="py-2 px-4 border-b border-gray-200">
                                                <textarea name="notes[]" class="border rounded w-full py-2 px-3 dark:bg-gray-800 mobile-only min-w-[90px]" {{ (auth()->user() === null || strtolower(auth()->user()->username) !== strtolower($username)) ? 'readonly' : '' }}>{{ $anime->pivot->notes ?? '' }}</textarea>
                                            </td>
                                            <td class="py-2 px-4 border-b border-gray-200">
                                                @if (auth()->user() != null && strtolower(auth()->user()->username) === strtolower($username))
                                                    <button
                                                        onclick="deleteAnime({{ $anime->id }}, event)"
                                                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded"
                                                    >
                                                        Delete
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if (auth()->user() != null && strtolower(auth()->user()->username) === strtolower($username) && $userAnime->isNotEmpty())
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-4">
                                Save Changes
                            </button>
                            @if (session()->has('message'))
                                <span class="ml-2">{{ session()->get('message') }}</span>
                            @endif
                        @endif
                        <div id="paginationDiv" class="mt-4">
                            {{ $userAnime->links() }}
                        </div>
                    </form>
                    @if (auth()->user() != null && strtolower(auth()->user()->username) === strtolower($username) && $userAnime->isNotEmpty())
                        <form action="{{ route('user.anime.list', ['username' => $username] + request()->query()) }}" method="GET" class="mb-3">
                            @foreach(request()->except('showallanime') as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <input type="checkbox" name="showallanime" value="1" onchange="this.form.submit()" {{ request('showallanime') ? 'checked' : '' }}> Show All Anime
                        </form>
                    @endif
                    @if (auth()->user() != null && strtolower(auth()->user()->username) === strtolower($username))
                        <div class="md:flex">
                            <a href="{{ route('import.animelist') }}">
                                <button type="button" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-4">
                                    Import from MyAnimeList and More
                                </button>
                            </a>
                            <a href="{{ route('export.animelist') }}">
                                <button type="button" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-4 md:ml-2">
                                    Export to MyAnimeList and More
                                </button>
                            </a>
                            @if (auth()->user()->show_clear_anime_list_button)
                                <form id="clearForm" class="inline-block" action="{{ route('user.anime.clear', ['username' => $username]) }}" method="post">
                                    @csrf
                                    <button type="button" id="clearListBtn" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded mt-4 md:ml-2">
                                        Delete Anime List
                                    </button>
                                </form>
                            @endif
                            @if (auth()->user()->show_clear_anime_list_sort_orders_button)
                                <form id="clearSortOrdersForm" class="inline-block" action="{{ route('user.anime.clearSortOrders', ['username' => $username]) }}" method="post">
                                    @csrf
                                    <button type="button" id="clearSortOrdersBtn" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded mt-4 md:ml-2">
                                        Delete Anime List Sort Orders
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <style>
        .sortable-ghost {
            opacity: 0.4;
            background: #f3f4f6;
        }
        .sortable-drag {
            cursor: grabbing !important;
        }
        .drag-handle {
            touch-action: none;
        }
    </style>
    <script type="module">
        import '/js/jquery.doubleScroll.js';

        $(document).ready(function() {
            $('.double-scroll').doubleScroll();

            //Check viewport width and clear hidden inputs accordingly
            function toggleHiddenInputs() {
                const isDesktop = window.matchMedia("(min-width: 768px)").matches;

                const desktopElements = document.querySelectorAll('.desktop-only');
                const mobileElements = document.querySelectorAll('.mobile-only');

                if (isDesktop) {
                    desktopElements.forEach(function(element) {
                        element.disabled = false;
                    });

                    mobileElements.forEach(function(element) {
                        element.disabled = true;
                    });
                } else {
                    desktopElements.forEach(function(element) {
                        element.disabled = true;
                    });

                    mobileElements.forEach(function(element) {
                        element.disabled = false;
                    });
                }
            }
            toggleHiddenInputs(); //Run on document ready
            //Re-run when the window is resized
            $(window).resize(function() {
                toggleHiddenInputs();
            });

            // Initialize drag-drop functionality
            @if (auth()->user() != null && strtolower(auth()->user()->username) === strtolower($username))
                initializeDragDrop();
            @endif
        });

        // Drag and drop functionality
        let isDragging = false;
        let isUpdating = false;

        function initializeDragDrop() {
            const desktopTbody = document.getElementById('desktop-tbody');
            const mobileTbody = document.getElementById('mobile-tbody');

            if (!desktopTbody || !mobileTbody) return;

            const sortableOptions = {
                animation: 150,
                handle: '.drag-handle',
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                onStart: function() {
                    isDragging = true;
                },
                onMove: function(evt) {
                    // Prevent dragging COMPLETED anime into non-completed sections
                    const completedStatusId = getCompletedStatusId();
                    const draggedStatus = evt.dragged.dataset.watchStatus;
                    const relatedStatus = evt.related.dataset.watchStatus;

                    // If dragging a completed anime, only allow dropping it next to other completed anime
                    if (draggedStatus == completedStatusId && relatedStatus != completedStatusId) {
                        return false;
                    }

                    return true;
                },
                onEnd: function(evt) {
                    isDragging = false;
                    const draggedTbody = evt.from;
                    const isDraggingDesktop = draggedTbody.id === 'desktop-tbody';
                    handleDragEnd(isDraggingDesktop);
                }
            };

            // Create Sortable instances for both desktop and mobile
            // Only completed anime have the .drag-handle class, so only they will be draggable
            new window.Sortable(desktopTbody, sortableOptions);
            new window.Sortable(mobileTbody, sortableOptions);
        }

        function getCompletedStatusId() {
            // Try to find a row with the drag-handle class (which only completed anime have)
            const firstCompletedRow = document.querySelector('tr[data-watch-status]');
            if (!firstCompletedRow) return null;

            // Get all watch status options from the select dropdown
            const selectElement = document.querySelector('select[name="watch_status_id[]"]');
            if (!selectElement) return null;

            // Find the COMPLETED option
            const completedOption = Array.from(selectElement.options).find(
                option => option.text.trim().toUpperCase() === 'COMPLETED'
            );

            return completedOption ? completedOption.value : null;
        }

        function handleDragEnd(isDraggingDesktop) {
            if (isUpdating) return;
            isUpdating = true;

            try {
                // Get the tbody that was dragged
                const sourceTbody = isDraggingDesktop
                    ? document.getElementById('desktop-tbody')
                    : document.getElementById('mobile-tbody');

                // Get the other tbody
                const targetTbody = isDraggingDesktop
                    ? document.getElementById('mobile-tbody')
                    : document.getElementById('desktop-tbody');

                // Get new order of anime IDs from the dragged tbody (all anime, not just completed)
                const rows = Array.from(sourceTbody.querySelectorAll('tr'));
                const animeIds = rows.map(row => parseInt(row.dataset.animeId));

                // Get completed status ID for filtering
                const completedStatusId = getCompletedStatusId();

                // Filter to only include completed anime IDs for saving
                const completedAnimeIds = rows
                    .filter(row => row.dataset.watchStatus == completedStatusId)
                    .map(row => parseInt(row.dataset.animeId));

                // Synchronize the other tbody
                synchronizeTbody(targetTbody, animeIds);

                // Update sort_order inputs in both tbodies (only for completed anime)
                updateSortOrderInputs(sourceTbody, completedAnimeIds);
                updateSortOrderInputs(targetTbody, completedAnimeIds);

                // Send AJAX request to backend (only completed anime)
                saveNewOrder(completedAnimeIds);
            } finally {
                isUpdating = false;
            }
        }

        function synchronizeTbody(tbody, animeIds) {
            // Create a map of anime ID to row element
            const rowMap = new Map();
            Array.from(tbody.querySelectorAll('tr')).forEach(row => {
                rowMap.set(parseInt(row.dataset.animeId), row);
            });

            // Reorder rows to match the animeIds order
            animeIds.forEach(animeId => {
                const row = rowMap.get(animeId);
                if (row) {
                    tbody.appendChild(row); // Moves the row to the end
                }
            });
        }

        function updateSortOrderInputs(tbody, completedAnimeIds) {
            const currentPage = {{ $userAnime->currentPage() }};
            const perPage = {{ $userAnime->perPage() }};
            const pageOffset = (currentPage - 1) * perPage;

            // Get all rows in their current order
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const completedStatusId = getCompletedStatusId();

            // Counter for completed anime position
            let completedPosition = 0;

            rows.forEach((row) => {
                const animeId = parseInt(row.dataset.animeId);
                const watchStatus = row.dataset.watchStatus;
                const sortOrderInput = row.querySelector('[name="sort_order[]"]');

                if (sortOrderInput && watchStatus == completedStatusId) {
                    // Only update sort_order for completed anime
                    sortOrderInput.value = pageOffset + completedPosition + 1;
                    completedPosition++;
                }
            });
        }

        function saveNewOrder(animeIds) {
            const currentPage = {{ $userAnime->currentPage() }};
            const perPage = {{ $userAnime->perPage() }};
            const pageOffset = (currentPage - 1) * perPage;
            const username = '{{ $username }}';

            // Show loading indicator (optional)
            const saveBtn = document.querySelector('button[type="submit"]');
            const originalBtnText = saveBtn ? saveBtn.textContent : '';
            if (saveBtn) {
                saveBtn.textContent = 'Saving...';
                saveBtn.disabled = true;
            }

            axios.post(`/animelist/${username}/reorder`, {
                anime_ids: animeIds,
                page_offset: pageOffset,
                _token: '{{ csrf_token() }}'
            })
            .then(function(response) {
                // Success - show brief success message
                if (saveBtn) {
                    saveBtn.textContent = 'Saved!';
                    setTimeout(() => {
                        saveBtn.textContent = originalBtnText;
                        saveBtn.disabled = false;
                    }, 2000);
                }
            })
            .catch(function(error) {
                console.error('Error updating order:', error);
                alert('Failed to save new order. Please try again or refresh the page.');

                // Restore button
                if (saveBtn) {
                    saveBtn.textContent = originalBtnText;
                    saveBtn.disabled = false;
                }

                // Optionally reload the page to revert to server state
                // location.reload();
            });
        }

        // Make functions available globally for inline onclick handlers
        window.getCompletedStatusId = getCompletedStatusId;
        window.updateSortOrderInputs = updateSortOrderInputs;
        window.saveNewOrder = saveNewOrder;
    </script>
    <script>
        function deleteAnime(animeId, event) {
            //Prevent update form submission
            event.preventDefault();
            axios.post(`/anime/${animeId}/delete-from-list/false`, {
                _token: '{{ csrf_token() }}',
                _method: 'DELETE'
            })
            .then(function (response) {
                location.reload();
            })
            .catch(function (error) {
                alert('Error removing anime. Please try again: ' + error);
                console.log('Error removing anime. Please try again: ' + error);
            });
        }
        document.addEventListener("DOMContentLoaded", function() {
            const clearListBtn = document.getElementById("clearListBtn");
            const clearSortOrdersBtn = document.getElementById("clearSortOrdersBtn");
            const clearModal = document.getElementById("clearModal");
            const confirmClear = document.getElementById("confirmClear");
            const cancelClear = document.getElementById("cancelClear");
            const clearForm = document.getElementById("clearForm");
            const clearSortOrdersForm = document.getElementById("clearSortOrdersForm");
            const confirmUsername = document.getElementById("confirmUsername");
            const confirmCheckbox = document.getElementById("confirmCheckbox");
            const errorText = document.getElementById("errorText");
            const closeModal = document.getElementById("closeModal");

            var username = @json(Auth::user()->username ?? "whyisitnull");

            let errorTimeout;
            let focusableElements = 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';
            let firstFocusableElement = clearModal.querySelectorAll(focusableElements)[0];
            let focusableContent = clearModal.querySelectorAll(focusableElements);
            let lastFocusableElement = focusableContent[focusableContent.length - 1];
            if (clearListBtn) {
                clearListBtn.addEventListener("click", function(event) {
                    event.preventDefault();
                    document.getElementById("clearAnimeText").innerText = "Are you sure you want to delete your anime list?";
                    document.body.style.overflow = 'hidden';
                    clearModal.classList.remove("hidden");
                    confirmClear.onclick = function() {
                        if (!confirmCheckbox.checked) {
                            showError("Please check the confirmation box.");
                            return;
                        }
                        if (confirmUsername.value !== username) {
                            showError("Username does not match.");
                            return;
                        }
                        // Submit the clearSortOrdersForm instead of clearForm
                        clearForm.submit();
                    }
                    firstFocusableElement.focus(); // Set focus on the first focusable element
                });
            }

            if (clearSortOrdersBtn) {
                clearSortOrdersBtn.addEventListener("click", function(event) {
                    event.preventDefault();
                    document.getElementById("clearAnimeText").innerText = "Are you sure you want to delete your anime list sort orders?";
                    document.body.style.overflow = 'hidden';
                    clearModal.classList.remove("hidden");
                    confirmClear.onclick = function() {
                        if (!confirmCheckbox.checked) {
                            showError("Please check the confirmation box.");
                            return;
                        }
                        if (confirmUsername.value !== username) {
                            showError("Username does not match.");
                            return;
                        }
                        // Submit the clearSortOrdersForm instead of clearForm
                        clearSortOrdersForm.submit();
                    }
                    firstFocusableElement.focus(); // Set focus on the first focusable element
                });
            }

            if (confirmClear) {
                confirmClear.addEventListener("click", function() {
                    clearTimeout(errorTimeout); // Clear previous timeout if exists

                    if (!confirmCheckbox.checked) {
                        showError("Please check the confirmation box.");
                        return;
                    }

                    if (confirmUsername.value !== username) {
                        showError("Username does not match.");
                        return;
                    }

                    clearForm.submit();
                });
            }

            if (cancelClear) {
                cancelClear.addEventListener("click", closeModalAction);
            }

            window.addEventListener("click", function(event) {
                if (event.target === clearModal) {
                    closeModalAction();
                }
            });

            window.addEventListener("keydown", function(e) {
                if (e.key === "Escape") {
                    closeModalAction();
                }

                // Focus trapping logic
                let isTabPressed = e.key === 'Tab' || e.keyCode === 9;

                if (!isTabPressed) {
                    return;
                }

                if (e.shiftKey) {
                    if (document.activeElement === firstFocusableElement) {
                        lastFocusableElement.focus();
                        e.preventDefault();
                    }
                } else {
                    if (document.activeElement === lastFocusableElement) {
                        firstFocusableElement.focus();
                        e.preventDefault();
                    }
                }
            });

            function closeModalAction() {
                document.body.style.overflow = 'auto';
                clearModal.classList.add("hidden");
                errorText.classList.add("hidden");
            }

            function showError(message) {
                errorText.textContent = message;
                errorText.classList.remove("hidden");
                errorTimeout = setTimeout(() => {
                    errorText.classList.add("hidden");
                }, 3000);
            }

            if (closeModal) {
                closeModal.addEventListener("click", closeModalAction);
            }
        });
        function swapAndSubmitSortOrder(animeId, direction) {
            event.preventDefault(); // Prevent default form submission

            const desktopRow = document.getElementById(`desktop-row-${animeId}`);
            const mobileRow = document.getElementById(`mobile-row-${animeId}`);

            if (!desktopRow) return;

            const completedStatusId = window.getCompletedStatusId();
            const currentWatchStatus = desktopRow.dataset.watchStatus;

            // Only allow swapping for completed anime
            if (currentWatchStatus != completedStatusId) {
                return;
            }

            // Determine which tbody to work with
            const tbody = desktopRow.parentElement;
            const currentRow = desktopRow;
            const adjacentRow = direction === 'up' ? currentRow.previousElementSibling : currentRow.nextElementSibling;

            // If there's no adjacent row, do nothing
            if (!adjacentRow) return;

            // Only swap with completed anime
            const adjacentWatchStatus = adjacentRow.dataset.watchStatus;
            if (adjacentWatchStatus != completedStatusId) {
                return;
            }

            // Swap the rows in the DOM
            if (direction === 'up') {
                tbody.insertBefore(currentRow, adjacentRow);
            } else {
                tbody.insertBefore(adjacentRow, currentRow);
            }

            // Get the corresponding mobile tbody and perform the same swap
            const mobileTbody = document.getElementById('mobile-tbody');
            const currentMobileAnimeId = currentRow.dataset.animeId;
            const adjacentMobileAnimeId = adjacentRow.dataset.animeId;

            const currentMobileRow = Array.from(mobileTbody.querySelectorAll('tr')).find(
                row => row.dataset.animeId === currentMobileAnimeId
            );
            const adjacentMobileRow = Array.from(mobileTbody.querySelectorAll('tr')).find(
                row => row.dataset.animeId === adjacentMobileAnimeId
            );

            if (currentMobileRow && adjacentMobileRow) {
                if (direction === 'up') {
                    mobileTbody.insertBefore(currentMobileRow, adjacentMobileRow);
                } else {
                    mobileTbody.insertBefore(adjacentMobileRow, currentMobileRow);
                }
            }

            // Get new order and update (only completed anime)
            const desktopTbody = document.getElementById('desktop-tbody');
            const rows = Array.from(desktopTbody.querySelectorAll('tr'));
            const completedAnimeIds = rows
                .filter(row => row.dataset.watchStatus == completedStatusId)
                .map(row => parseInt(row.dataset.animeId));

            // Update sort_order inputs (only for completed anime)
            window.updateSortOrderInputs(desktopTbody, completedAnimeIds);
            window.updateSortOrderInputs(mobileTbody, completedAnimeIds);

            // Save to server via AJAX (only completed anime)
            window.saveNewOrder(completedAnimeIds);
        }
    </script>
</x-app-layout>
