<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            User Anime List V2 for {{ $username }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('user.anime.update', ['username' => $username]) }}" method="POST">
                        @csrf
                        <table id="userAnimeTable" class="min-w-full">
                            <thead>
                                <tr>
                                    <!-- Adjust your table headers accordingly -->
                                    <th>Picture</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Watch Status</th>
                                    <!-- ... additional headers ... -->
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTables will auto-populate this section based on the data returned from the server -->
                            </tbody>
                        </table>
                        @if(auth()->user() != null && auth()->user()->username === $username && $userAnime->isNotEmpty())
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-4">
                                Save Changes
                            </button>
                            @if(session()->has('message'))
                                <span class="ml-2">{{ session()->get('message') }}</span>
                            @endif
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            var watchStatusMap = @json($watchStatusMap);
            $('#userAnimeTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('anime.getUserAnimeData', ['username' => $username]) }}',
                columns: [
                    { data: 'thumbnail', name: 'thumbnail', render: function(data, type, row) {
                        return '<img src="'+data+'" alt="'+row.title+' thumbnail" width="50" height="50" onerror="this.onerror=null; this.src=\'{{ asset('img/notfound.gif') }}\'">';
                    }},
                    { data: 'title', name: 'title', render: function(data, type, row) {
                        return '<a href="/anime/' + row.id + '">' + data + '</a>';
                    }},
                    { data: 'anime_type.type', name: 'anime_type.type' },  // Adjust based on actual returned data structure
                    { data: 'anime_status.status', name: 'anime_status.status' }, // Adjust based on actual returned data structure
                    { data: 'pivot.watch_status_id', name: 'pivot.watch_status_id', render: function(data, type, row) {
                        if('{{ auth()->user()->username ?? '' }}' === '{{ $username }}') {
                            var options = '@foreach ($watchStatuses as $status) <option value="{{ $status->id }}" ' + (data === "{{ $status->id }}" ? 'selected' : '') + '>{{ $status->status }}</option> @endforeach';
                            return '<select name="watch_status_id[]" class="border rounded w-full py-2 px-3 dark:bg-gray-800" style="padding-right: 36px">' + options + '</select>';
                        } else {
                            return watchStatusMap[data] || 'UNKNOWN';
                        }
                    }}
                    // ... Add other columns data as needed ...
                ]
            });
        });
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
    </script>
</x-app-layout>
