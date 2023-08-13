<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Anime List') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <table style="width:100%" id="animeTable" class="display text-gray-900 dark:text-gray-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Picture</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Episodes</th>
                                <th>Status</th>
                                <th>Tags</th>
                                <th>Season</th>
                                <th>Year</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script type="module">
        import '/js/jquery.dataTables.yadcf.js';
        $(document).ready(function () {
            let initialSearch = new URLSearchParams(window.location.search).get('search') || "";
            let dataTable = $('#animeTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('anime.data') }}',
                order: [[7, 'desc'], [8, 'desc']],
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'thumbnail', name: 'picture', render: function(data, type, row) {
                        return '<img src="' + data + '" alt="Thumbnail" style="width:50px; max-height: 70px"  onerror="this.onerror=null; this.src=\'/img/notfound.gif\';" />';
                    }},
                    { data: 'title', name: 'title', render: function(data, type, row) {
                        return `<a href="/anime/${row.id}/${data.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')}">${data}</a>`;
                    }},
                    { data: 'anime_type.type', name: 'type', searchable: 'false' },
                    { data: 'episodes', name: 'episodes' },
                    { data: 'anime_status.status', name: 'status', searchable: 'false' },
                    { data: 'tags', name: 'tags', width:"15%", searchable: 'true' },
                    { data: 'season_display', name: 'season', width: "18%" },
                    { data: 'year', name: 'year', width: "11%", render: function(data, type, row) {
                        return data === null ? 'UNKNOWN' : data;
                    }},
                ],
                search: { search: initialSearch },
                initComplete: function() {
                    let resetBtn = $('<button id="resetFilters" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-4" onclick="window.location.href = \'/anime\'">Reset Filters</button>');
                    $('#animeTable_filter').prepend(resetBtn);
                }
            });
            yadcf.init(dataTable, [
                {
                    column_number: 2,
                    filter_type: "text"
                },
                {
                    column_number: 6,
                    filter_type: "text" //TODO: fix searching so any combination of tags can be found
                },
                {
                    column_number: 7,
                    filter_type: "select",
                    data: [
                        { value: "WINTER", label: "Winter" },
                        { value: "SPRING", label: "Spring" },
                        { value: "SUMMER", label: "Summer" },
                        { value: "FALL", label: "Fall" }
                    ],
                    filter_default_label: "All Seasons"
                },
                {
                    column_number: 8,
                    filter_type: "text"
                },
            ]);
        });
    </script>
    <style>
        #yadcf-filter--animeTable-2 {
            max-width: 160px;
        }

        #yadcf-filter--animeTable-6 {
            max-width: 100px;
        }

        #yadcf-filter--animeTable-7 {
            max-width: 138px;
            padding-right: 1rem;
        }

        #yadcf-filter--animeTable-8 {
            max-width: 62px;
        }
    </style>
</x-app-layout>
