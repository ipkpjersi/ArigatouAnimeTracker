<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            User Anime List V2 for {{ $username }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-[1550px] mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
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
                                    <th class="md:min-w-[90px]">Watch Status</th>
                                    <th>Progress</th>
                                    <th>Score</th>
                                    {{-- @if(auth()->user() != null && auth()->user()->username === $username) --}}
                                        <th>Sort Order</th>
                                    {{-- @endif --}}
                                    <th>Episodes</th>
                                    <th>Season</th>
                                    <th>Year</th>
                                    @if(auth()->user() != null && auth()->user()->username === $username)
                                        <th>Delete</th>
                                    @endif
                                    <!-- ... additional headers ... -->
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTables will auto-populate this section based on the data returned from the server -->
                            </tbody>
                        </table>
                        @if(auth()->user() != null && auth()->user()->username === $username && $userAnimeCount > 0)
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-4">
                                Save Changes
                            </button>
                            @if(session()->has('message'))
                                <span class="ml-2">{{ session()->get('message') }}</span>
                            @endif
                        @endif
                    </form>
                    @if(auth()->user() != null && auth()->user()->username === $username)
                        <a href="{{ route('import.animelist') }}">
                            <button type="button" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-4">
                                Import from MyAnimeList and More
                            </button>
                        </a>
                        <a href="{{ route('export.animelist') }}">
                            <button type="button" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-4">
                                Export to MyAnimeList and More
                            </button>
                        </a>
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
    </script>
    <script type="module">
        import '/js/jquery.doubleScroll.js';
        import '/js/jquery.dataTables.yadcf.js';
        $(document).ready(function() {
            $('.double-scroll').doubleScroll();
            var watchStatusMap = @json($watchStatusMap);
            let columns = [];
            if ("{{ $show_anime_list_number }}" == "1") {
                columns.push({ data: null, searchable: false, orderable: false, defaultContent: '', targets: 0 });
            }
            columns.push(
                { data: 'thumbnail', name: 'thumbnail', searchable: false, responsivePriority: 1, render: function(data, type, row) {
                    return '<span style="display:none">' + row.id  + '</span>' + '<img src="'+data+'" alt="'+row.title+' thumbnail" width="50" height="50" onerror="this.onerror=null; this.src=\'{{ asset('img/notfound.gif') }}\'">' + '<input type="hidden" name="anime_id[]" value="'+row.anime_id+'">';
                }},
                { data: 'title', name: 'title', responsivePriority: 2,  render: function(data, type, row) {
                    return '<a href="/anime/' + row.anime_id + '">' + data + '</a>';
                }},
                { data: 'anime_type.type', name: 'anime_type.type', searchable: 'false' },  // Adjust based on actual returned data structure
                { data: 'anime_status.status', name: 'anime_status.status', searchable: 'false' }, // Adjust based on actual returned data structure
                { data: 'watch_status_id', name: 'watch_status_id', searchable: 'false', render: function(data, type, row) {
                    //console.log("watch_status_id data" + data);
                    if('{{ auth()->user()->username ?? '' }}' === '{{ $username }}') {
                        var options = '';
                        options += options += '<option value="">Pick an option...</option>';
                        options += '@foreach ($watchStatuses as $status) <option value="{{ $status->id }}" ' + (data === {{ $status->id }} ? 'selected' : '') + '>{{ $status->status }}</option> @endforeach';
                        return '<select name="watch_status_id[]" class="border rounded w-full py-2 px-3 dark:bg-gray-800" style="padding-right: 36px">' + options + '</select>';
                    } else {
                        return watchStatusMap[data] || 'UNKNOWN';
                    }
                }},
                {
                    data: 'progress',
                    name: 'progress',
                    searchable: false,
                    render: function(data, type, row) {
                        if('{{ auth()->user()->username ?? '' }}' === '{{ $username }}') {
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
                    responsivePriority: 3,
                    render: function(data, type, row) {
                        if('{{ optional(auth()->user())->username ?? '' }}' === '{{ $username }}') {
                            var options = '';
                            options += '<option value="">Pick an option...</option>';
                            for(var i = 1; i <= 10; i++) {
                                options += '<option value="'+i+'" '+(data == i ? 'selected' : '')+'>'+i+'</option>';
                            }
                            return '<select name="score[]" class="border rounded w-full py-2 px-3 dark:bg-gray-800" style="padding-right: 36px">' + options + '</select>';
                        } else {
                            return data || 'UNKNOWN';
                        }
                    }
                }
            );
            //if ('{{ optional(auth()->user())->username ?? '' }}' === '{{ $username }}') {
                columns.push({
                    data: 'sort_order',
                    name: 'sort_order',
                    searchable: false,
                    render: function(data, type, row) {
                        return '<input type="number" min="1" name="sort_order[]" value="'+data+'" class="border rounded w-24 py-2 px-3 dark:bg-gray-800">';
                    }
                });
            //}
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
                    responsivePriority: 4,
                    searchable: false
                },
            );

            if ('{{ optional(auth()->user())->username ?? '' }}' === '{{ $username }}') {
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
            $('#userAnimeTable').DataTable({
                processing: true,
                serverSide: true,
                order: [[7, 'asc'], [6, 'asc'], [1, 'asc']],
                ajax: '{{ route('user.anime.list.data.v2', ['username' => $username]) }}',
                columns: columns,
                initComplete: function() {
                    let resetBtn = $('<button type="button" id="resetFilters" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-4" onclick="location.reload()">Reset Filters</button>');
                    $('#userAnimeTable_filter').prepend(resetBtn);
                },
                responsive: true,
                rowCallback: rowCallback
            });
        });
    </script>
</x-app-layout>
