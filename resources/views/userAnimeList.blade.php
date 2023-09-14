<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            User Anime List for {{ $username }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-[1550px] mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div id="clearModal" class="fixed top-0 left-0 w-full h-full bg-opacity-50 bg-black flex justify-center items-center z-50 hidden">
                    <div class="bg-white dark:bg-gray-700 rounded relative w-96 h-64">
                        <button id="closeModal" class="absolute top-2 right-4 bg-red-500 text-white p-2 pl-4 pr-4 mb-2 rounded">X</button>
                        <div class="p-4 mt-10">
                            <p>Are you sure you want to delete your anime list?</p>
                            <div class="flex items-center mt-2"> <!-- Flex container -->
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
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Picture</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Name</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Type</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200 sm:">Status</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200 min-w-[165px]">Watch Status</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Progress</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200 min-w-[70px]">Score</th>
                                        @if (auth()->user() != null && auth()->user()->username === $username)
                                            <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Sort Order</th>
                                        @endif
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Episodes</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Season</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Year</th>
                                        @if (auth()->user() != null && auth()->user()->username === $username)
                                            <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Delete</th>
                                        @endif
                                    </tr>
                                    <!-- mobile design -->
                                    <tr class="md:hidden">
                                        @if ($show_anime_list_number)
                                            <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">#</th>
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
                                        @if (auth()->user() != null && auth()->user()->username === $username)
                                            <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Sort Order</th>
                                        @endif
                                        @if (auth()->user() != null && auth()->user()->username === $username)
                                            <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Delete</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- desktop design -->
                                    @foreach ($userAnime as $anime)
                                        <tr class="hidden md:table-row">
                                            <input type="hidden" class="desktop-anime-ids desktop-only" name="anime_ids[]" value="{{ $anime->id }}">
                                            @if ($show_anime_list_number)
                                                <td class="py-2 px-4 border-b border-gray-200">{{ (($userAnime->currentPage() - 1) * $userAnime->perPage()) + $loop->iteration }}</td>
                                            @endif
                                            <td class="py-2 px-4 border-b border-gray-200">
                                                <img src="{{ $anime->thumbnail }}" alt="{{ $anime->title }} thumbnail" width="50" height="50" onerror="this.onerror=null; this.src='{{ asset('img/notfound.gif') }}'">
                                            </td>
                                            <td class="py-2 px-4 border-b border-gray-200"><a href="/anime/{{$anime->id}}">{{ $anime->title }}</a></td>
                                            <td class="py-2 px-4 border-b border-gray-200">{{ optional($anime->anime_type)->type }}</td>
                                            <td class="py-2 px-4 border-b border-gray-200">{{ optional($anime->anime_status)->status }}</td>
                                            <td class="py-2 px-4 border-b border-gray-200">
                                                @if (auth()->user() != null && auth()->user()->username === $username)
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
                                                @if (auth()->user() != null && auth()->user()->username === $username)
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
                                                @if (auth()->user() != null && auth()->user()->username === $username)
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
                                            @if (auth()->user() != null && auth()->user()->username === $username)
                                                <td class="py-2 px-4 border-b border-gray-200">
                                                    <input type="number" min="1" name="sort_order[]" value="{{ $anime->pivot->sort_order }}" class="border rounded w-24 py-2 px-3 dark:bg-gray-800 desktop-only">
                                                </td>
                                            @endif
                                            <td class="py-2 px-4 border-b border-gray-200">{{ $anime->episodes }}</td>
                                            <td class="py-2 px-4 border-b border-gray-200">{{ $anime->season }}</td>
                                            <td class="py-2 px-4 border-b border-gray-200">{{ $anime->year }}</td>
                                            <td class="py-2 px-4 border-b border-gray-200">
                                                @if (auth()->user() != null && auth()->user()->username === $username)
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
                                    <!-- mobile design -->
                                    @foreach ($userAnime as $anime)
                                        <tr class="md:hidden mobile-only">
                                            <input type="hidden" class="mobile-anime-ids mobile-only" name="anime_ids[]" value="{{ $anime->id }}">
                                            @if ($show_anime_list_number)
                                                <td class="py-2 px-4 border-b border-gray-200">{{ (($userAnime->currentPage() - 1) * $userAnime->perPage()) + $loop->iteration }}</td>
                                            @endif
                                            <td class="py-2 px-4 border-b border-gray-200">
                                                <img src="{{ $anime->thumbnail }}" alt="{{ $anime->title }} thumbnail" width="50" height="50" onerror="this.onerror=null; this.src='{{ asset('img/notfound.gif') }}'">
                                            </td>
                                            <td class="py-2 px-4 border-b border-gray-200"><a href="/anime/{{$anime->id}}">{{ $anime->title }}</a></td>
                                            <td class="py-2 px-4 border-b border-gray-200 min-w-[70px]">
                                                @if (auth()->user() != null && auth()->user()->username === $username)
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
                                            <td class="py-2 px-4 border-b border-gray-200">{{ optional($anime->anime_type)->type }}</td>
                                            <td class="py-2 px-4 border-b border-gray-200">{{ optional($anime->anime_status)->status }}</td>
                                            <td class="py-2 px-4 border-b border-gray-200 min-w-[150px]">
                                                @if (auth()->user() != null && auth()->user()->username === $username)
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
                                                @if (auth()->user() != null && auth()->user()->username === $username)
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
                                            @if (auth()->user() != null && auth()->user()->username === $username)
                                                <td class="py-2 px-4 border-b border-gray-200">
                                                    <input type="number" min="1" name="sort_order[]" value="{{ $anime->pivot->sort_order }}" class="border rounded w-24 py-2 px-3 dark:bg-gray-800 mobile-only">
                                                </td>
                                            @endif
                                            <td class="py-2 px-4 border-b border-gray-200">
                                                @if (auth()->user() != null && auth()->user()->username === $username)
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
                        @if (auth()->user() != null && auth()->user()->username === $username && $userAnime->isNotEmpty())
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
                    @if (auth()->user() != null && auth()->user()->username === $username)
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
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
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
        });
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
            const clearModal = document.getElementById("clearModal");
            const confirmClear = document.getElementById("confirmClear");
            const cancelClear = document.getElementById("cancelClear");
            const clearForm = document.getElementById("clearForm");
            const confirmUsername = document.getElementById("confirmUsername");
            const confirmCheckbox = document.getElementById("confirmCheckbox");
            const errorText = document.getElementById("errorText");
            const closeModal = document.getElementById("closeModal")

            var username = @json(Auth::user()->username ?? "whyisitnull");

            let errorTimeout;

            clearListBtn.addEventListener("click", function(event) {
                event.preventDefault();
                clearModal.classList.remove("hidden");
            });

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

            cancelClear.addEventListener("click", function() {
                clearModal.classList.add("hidden");
                errorText.classList.add("hidden");
            });

            window.addEventListener("click", function(event) {
                if (event.target === clearModal) {
                    clearModal.classList.add("hidden");
                    errorText.classList.add("hidden");
                }
            });

            window.addEventListener("keydown", function(event) {
                if (event.key === "Escape") {
                    clearModal.classList.add("hidden");
                    errorText.classList.add("hidden");
                }
            });

            function showError(message) {
                errorText.textContent = message;
                errorText.classList.remove("hidden");
                errorTimeout = setTimeout(() => {
                    errorText.classList.add("hidden");
                }, 3000); // Hide the error message after 3 seconds
            }
            closeModal.addEventListener("click", function() {
                clearModal.classList.add("hidden");
                errorText.classList.add("hidden");
            });
        });
    </script>
</x-app-layout>
