<x-app-layout>
    <x-slot name="title">
        {{ config('app.name', 'Laravel') }} - User Anime List V2 for {{ $username }}
    </x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            User Anime List V2 for {{ $username }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-[1700px] mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
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
                    <form action="{{ route('user.anime.update.v2', ['username' => $username]) }}" method="POST">
                        @csrf
                        <table id="userAnimeTable" class="min-w-full">
                            <thead>
                                <tr>
                                    @if ($show_anime_list_number)
                                        <th>#</th>
                                    @endif
                                    <th>Picture</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Watch Status</th>
                                    <th>Progress</th>
                                    <th>Score</th>
                                    @if (auth()->user() != null && strtolower(auth()->user()->username) === strtolower($username))
                                        <th>Sort Order</th>
                                    @endif
                                    <th>Episodes</th>
                                    <th>Season</th>
                                    <th>Year</th>
                                    <th>Notes</th>
                                    @if (auth()->user() != null && strtolower(auth()->user()->username) === strtolower($username))
                                        <th>Delete</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTables will auto-populate this section based on the data returned from the server -->
                            </tbody>
                        </table>
                        @if (auth()->user() != null && strtolower(auth()->user()->username) === strtolower($username) && $userAnimeCount > 0)
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-4">
                                Save Changes
                            </button>
                            @if (session()->has('message'))
                                <span class="ml-2">{{ session()->get('message') }}</span>
                            @endif
                        @endif
                    </form>
                    @if (auth()->user() != null && strtolower(auth()->user()->username) === strtolower($username))
                        <form action="{{ route('user.anime.list.v2', ['username' => $username] + request()->query()) }}" method="GET" class="mb-3 mt-4">
                            <input type="checkbox" name="showallanime" value="1" onchange="this.form.submit()" {{ request('showallanime') ? 'checked' : '' }}> Show All Anime
                        </form>
                    @endif
                    @if (auth()->user() != null && strtolower(auth()->user()->username) === strtolower($username))
                        <div class="flex flex-col md:flex-row">
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
                                        Clear Anime List
                                    </button>
                                </form>
                            @endif
                            @if (auth()->user() != null && strtolower(auth()->user()->username) === strtolower($username))
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
    <script>
        function deleteAnime(animeId, event) {
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
        function swapAndSubmitSortOrder(animeId, direction) {
            event.preventDefault(); // Prevent default form submission
            const currentRow = document.getElementById(`row-${animeId}`);
            const adjacentRow = direction === 'up' ? currentRow.previousElementSibling : currentRow.nextElementSibling;
            const currentSortOrderInput = currentRow.querySelector('[name="sort_order[]"]');
            // If there's an adjacent row with a sort_order input, swap values
            if (adjacentRow && adjacentRow.querySelector('[name="sort_order[]"]') && adjacentRow.querySelector('[name="sort_order[]"]').value > 0) {
                const adjacentSortOrderInput = adjacentRow.querySelector('[name="sort_order[]"]');
                // Parse the sort_order values as integers for proper comparison
                const currentSortOrderValue = parseInt(currentSortOrderInput.value, 10);
                const adjacentSortOrderValue = parseInt(adjacentSortOrderInput.value, 10);
                    // Perform the swap
                    currentSortOrderInput.value = adjacentSortOrderValue;
                    adjacentSortOrderInput.value = currentSortOrderValue;
            } /*else { //Comment this out since we only want to swap if there is an adjacent row, otherwise we end up with a list of two with a sort_order 4 followed by 5, clicking up on sort_order 4 results in both getting 5.
                // Adjust the sort_order for the current row if there's no adjacent row
                if (direction === 'up') {
                    currentSortOrderInput.value = Math.max(1, (parseInt(currentSortOrderInput.value) || 0) + 1);
                } else { // Assuming 'down' direction should also not decrease below 1
                    currentSortOrderInput.value = Math.max(1, (parseInt(currentSortOrderInput.value) || 0) - 1);
                }
            }*/
            // Submit the form to update the server
            currentRow.closest('form').submit();
        }
    </script>
    <script type="module">
        import '/js/jquery.doubleScroll.js';
        import '/js/jquery.dataTables.yadcf.js';
        import '/js/dataTables.colReorder.min.js';
        $(document).ready(function() {
            $('.double-scroll').doubleScroll();
            var watchStatusMap = @json($watchStatusMap);
            let columns = [];
            if ("{{ $show_anime_list_number }}" == "1") {
                columns.push({ data: null, searchable: false, orderable: false, defaultContent: '', targets: 0 });
            }
            columns.push(
                { data: 'thumbnail', name: 'thumbnail', searchable: false, render: function(data, type, row) {
                    return '<span style="display:none">' + row.id  + '</span>' + '<img src="'+data+'" alt="'+row.title+' thumbnail" width="50" height="50" onerror="this.onerror=null; this.src=\'{{ asset('img/notfound.gif') }}\'">' + '<input type="hidden" name="anime_id[]" value="'+row.anime_id+'">';
                }},
                { data: 'title', name: 'title', render: function(data, type, row) {
                    return '<a href="/anime/' + row.anime_id + '">' + data + '</a>';
                }},
                { data: 'anime_type.type', name: 'anime_type.type', searchable: 'false' },  // Adjust based on actual returned data structure
                { data: 'anime_status.status', name: 'anime_status.status', searchable: 'false' }, // Adjust based on actual returned data structure
                { data: 'watch_status_id', name: 'watch_status_id', searchable: 'false', render: function(data, type, row) {
                    //console.log("watch_status_id data" + data);
                    if('{{ strtolower(optional(auth()->user())->username ?? '') }}' === '{{ strtolower($username) }}') {
                        var options = '';
                        options += options += '<option value="">Pick an option...</option>';
                        options += '@foreach ($watchStatuses as $status) <option value="{{ $status->id }}" ' + (data === {{ $status->id }} ? 'selected' : '') + '>{{ $status->status }}</option> @endforeach';
                        return '<select name="watch_status_id[]" class="border rounded w-[151px] py-2 px-3 dark:bg-gray-800" style="padding-right: 36px">' + options + '</select>';
                    } else {
                        return watchStatusMap[data] || 'UNKNOWN';
                    }
                }},
                {
                    data: 'progress',
                    name: 'progress',
                    searchable: false,
                    render: function(data, type, row) {
                        if('{{ strtolower(optional(auth()->user())->username ?? '') }}' === '{{ strtolower($username) }}') {
                            var options = '';
                            options += '<option value="">Pick an option...</option>';
                            for(var i = 1; i <= row.episodes; i++) {
                                options += '<option value="'+i+'" '+(data == i ? 'selected' : '')+'>'+i+'</option>';
                            }
                            return '<select name="progress[]" class="border rounded w-full py-2 px-3 dark:bg-gray-800" style="padding-right: 36px">' + options + '</select>';
                        } else {
                            return data || '0';
                        }
                    }
                },
                {
                    data: 'score',
                    name: 'score',
                    searchable: false,
                    render: function(data, type, row) {
                        if('{{ strtolower(optional(auth()->user())->username ?? '') }}' === '{{ strtolower($username) }}') {
                            var options = '';
                            options += '<option value="">Pick an option...</option>';
                            for(var i = 1; i <= 10; i++) {
                                options += '<option value="'+i+'" '+(data == i ? 'selected' : '')+'>'+i+'</option>';
                            }
                            return '<select name="score[]" class="border rounded w-[70px] py-2 px-3 dark:bg-gray-800" style="padding-right: 36px">' + options + '</select>';
                        } else {
                            return data || 'UNKNOWN';
                        }
                    }
                }
            );

            if ('{{ strtolower(optional(auth()->user())->username ?? '') }}' === '{{ strtolower($username) }}') {
                columns.push({
                    data: 'sort_order',
                    name: 'sort_order',
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return `
                            <div style="display: flex; align-items: center;">
                                <input type="number" min="1" name="sort_order[]" value="${data}" class="border rounded w-24 py-2 px-3 dark:bg-gray-800">
                                <!-- Up Arrow -->
                                <button onclick="swapAndSubmitSortOrder(${meta.row + 1}, 'up')" class="ml-2 text-gray-600 hover:text-gray-800">⬆️</button>
                                <!-- Down Arrow -->
                                <button onclick="swapAndSubmitSortOrder(${meta.row + 1}, 'down')" class="ml-2 text-gray-600 hover:text-gray-800">⬇️</button>
                            </div>
                        `;
                    }
                });
            }
            columns.push(
                {
                    data: 'episodes',
                    name: 'episodes',
                    searchable: false
                },
                {
                    data: 'season',
                    name: 'season',
                    searchable: false
                },
                {
                    data: 'year',
                    name: 'year',
                    searchable: false
                },
            );

            columns.push({
                data: 'notes', // assuming 'notes' is the key that holds the notes data
                name: 'notes',
                searchable: false,
                render: function(data, type, row) {
                    if('{{ strtolower(optional(auth()->user())->username ?? '') }}' === '{{ strtolower($username) }}') {
                        // If the user matches, make textarea editable
                        return '<textarea name="notes[]" class="border rounded py-2 px-3 dark:bg-gray-800">' + (data ? data : '') + '</textarea>';
                    } else {
                        // If the user doesn't match, make textarea read-only
                        return '<textarea name="notes[]" class="border rounded py-2 px-3 dark:bg-gray-800" readonly>' + (data ? data : '') + '</textarea>';
                    }
                }
            });

            if ('{{ strtolower(optional(auth()->user())->username ?? '') }}' === '{{ strtolower($username) }}') {
                columns.push({
                    data: 'anime_id',
                    name: 'delete',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        return '<button type="button" onclick="deleteAnime(' + data + ', event)" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded">Delete</button>';
                    }
                });
            }
            let rowCallback = "";
            if ("{{ $show_anime_list_number }}" == "1") {
                rowCallback = function(row, data, index) {
                    var info = $(this).DataTable().page.info();
                    var pageNo = info.page;
                    var length = info.length;
                    var realIndex = pageNo * length + index + 1;
                    $('td:eq(0)', row).html(realIndex);
                };
            }
            let colReorder = [];
            if ($(window).width() <= 640) {
                if ("{{ $show_anime_list_number }}" == "1") {
                    colReorder = [0, 1, 2, 7, 11, 10, 3, 4, 5, 6, 8, 9, 12];
                } else {
                    colReorder = [0, 1, 6, 10, 9, 2, 3, 4, 5, 7, 8, 11];
                }
            }
            //We cannot use datatables responsive for this because it injects additional rows which breaks updating our user anime list.
            let urlParams = new URLSearchParams(window.location.search);
            let showAllAnimeQueryParam = urlParams.get('showallanime') === '1';
            let isUserAuthenticatedAndMatching = '{{ Auth::check() && strtolower(Auth::user()->username) === strtolower($username) }}' === '1';
            let showAllAnime = showAllAnimeQueryParam && isUserAuthenticatedAndMatching ? '1' : '0';

            let customUrl = new URL('{{ route('user.anime.list.data.v2', ['username' => $username]) }}', window.location.origin);
            // Construct the URL with the showallanime parameter
            if (showAllAnime === '1') {
                customUrl.searchParams.set('showallanime', '1');
            }
            function initDataTable(scrollWidth) {
                return $('#userAnimeTable').DataTable({
                    processing: true,
                    serverSide: true,
                    order: [[7, 'asc'], [6, 'asc'], [1, 'asc']],
                    ajax: customUrl.toString(),
                    columns: columns,
                    initComplete: function() {
                        let resetBtn = $('<button type="button" id="resetFilters" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-4" onclick="location.reload()">Reset Filters</button>');
                        $('#userAnimeTable_filter').prepend(resetBtn);
                    },
                    rowCallback: rowCallback,
                    scrollX: scrollWidth,
                    bScrollCollapse: true,
                    colReorder: {
                        order: colReorder
                    },
                    createdRow: function(row, data, dataIndex) {
                        // Calculate the overall index based on the current page and data index
                        let pageIndex = $('#userAnimeTable').DataTable().page.info().page;
                        let pageSize = $('#userAnimeTable').DataTable().page.info().length;
                        let overallIndex = pageIndex * pageSize + dataIndex + 1;

                        // Assign the ID to the row
                        $(row).attr('id', 'row-' + overallIndex);
                    },
                });
            }

            // Initial DataTable initialization
            let initialScrollWidth = window.innerWidth < 992 ? "100%" : "";
            let dataTable = initDataTable(initialScrollWidth);

            // Resize event listener to reinitialize DataTable
            window.addEventListener("resize", function() {
                let newScrollWidth = window.innerWidth < 992 ? "100%" : "";

                // Check if scrollWidth needs to be updated
                if (newScrollWidth !== initialScrollWidth) {
                    // Destroy the current DataTable instance
                    dataTable.destroy();

                    // Reinitialize the DataTable with the new sScrollX value
                    dataTable = initDataTable(newScrollWidth);

                    // Update the initialScrollWidth for the next resize event
                    initialScrollWidth = newScrollWidth;
                }
            });
        });
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
    </script>
</x-app-layout>
