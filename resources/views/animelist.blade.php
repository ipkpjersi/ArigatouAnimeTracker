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
            let dataTable = $('#animeTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('anime.data') }}',
                order: [[7, 'desc'], [6, 'desc']],
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'thumbnail', name: 'picture', render: function(data, type, row) {
                        return '<img src="' + data + '" alt="Thumbnail" style="width:50px; max-height: 70px"  onerror="this.onerror=null; this.src=\'/img/notfound.gif\';" />';
                    }},
                    { data: 'title', name: 'title' },
                    { data: 'anime_type.type', name: 'type', searchable: 'false' },
                    { data: 'episodes', name: 'episodes' },
                    { data: 'anime_status.status', name: 'status', searchable: 'false' },
                    { data: 'season', name: 'season', render: function(data, type, row) {
                        return data === 'UNDEFINED' ? 'UNKNOWN' : data;
                    }},
                    { data: 'year', name: 'year', render: function(data, type, row) {
                        return data === null ? 'UNKNOWN' : data;
                    }},
                ]
            });
            yadcf.init(dataTable, [
                {
                    column_number: 2,
                    filter_type: "text"
                },
            ]);
        });
    </script>
</x-app-layout>
