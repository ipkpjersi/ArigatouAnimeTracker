<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            User Anime List for {{ $username }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('user.anime.update', ['username' => $username]) }}" method="POST">
                        @csrf
                        <table class="min-w-full">
                            <thead>
                                <tr>
                                    @if ($show_anime_list_number)
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">#</th>
                                    @endif
                                    <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Picture</th>
                                    <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Name</th>
                                    <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Type</th>
                                    <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Status</th>
                                    <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Watch Status</th>
                                    <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Score</th>
                                    @if(auth()->user() != null && auth()->user()->username === $username)
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Sort Order</th>
                                    @endif
                                    <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Episodes</th>
                                    <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Season</th>
                                    <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Year</th>
                                    @if(auth()->user() != null && auth()->user()->username === $username)
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-200">Delete</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($userAnime as $anime)
                                    <tr>
                                        <input type="hidden" name="anime_ids[]" value="{{ $anime->id }}">
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
                                            @if(auth()->user() != null && auth()->user()->username === $username)
                                                <select name="watch_status_id[]" class="border rounded w-full py-2 px-3 dark:bg-gray-800" style="padding-right: 36px">
                                                    <option value="">Pick a status...</option>
                                                    @foreach ($watchStatuses as $status)
                                                        <option value="{{ $status->id }}" @if($anime->pivot->watch_status_id == $status->id) selected @endif>
                                                            {{ $status->status }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @else
                                                {{ $watchStatusMap[$anime->pivot->watch_status_id] ?? 'UNKNOWN' }}
                                            @endif
                                        </td>

                                        <td class="py-2 px-4 border-b border-gray-200">
                                            @if(auth()->user() != null && auth()->user()->username === $username)
                                                <select name="score[]" class="border rounded w-full py-2 px-3 dark:bg-gray-800" style="padding-right: 36px">
                                                    <option value="">Pick an option...</option>
                                                    @for ($i = 1; $i <= 10; $i++)
                                                        <option value="{{ $i }}" @if($anime->pivot->score == $i) selected @endif>{{ $i }}</option>
                                                    @endfor
                                                </select>
                                            @else
                                                {{ $anime->pivot->score ?? 'UNKNOWN' }}
                                            @endif
                                        </td>
                                        @if(auth()->user() != null && auth()->user()->username === $username)
                                            <td class="py-2 px-4 border-b border-gray-200">
                                                <input type="number" min="1" name="sort_order[]" value="{{ $anime->pivot->sort_order }}" class="border rounded w-24 py-2 px-3 dark:bg-gray-800">
                                            </td>
                                        @endif
                                        <td class="py-2 px-4 border-b border-gray-200">{{ $anime->episodes }}</td>
                                        <td class="py-2 px-4 border-b border-gray-200">{{ $anime->season }}</td>
                                        <td class="py-2 px-4 border-b border-gray-200">{{ $anime->year }}</td>
                                        <td class="py-2 px-4 border-b border-gray-200">
                                            @if(auth()->user() != null && auth()->user()->username === $username)
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
                        @if(auth()->user() != null && auth()->user()->username === $username && $userAnime->isNotEmpty())
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-4">
                                Save Changes
                            </button>
                            @if(session()->has('message'))
                                <span class="ml-2">{{ session()->get('message') }}</span>
                            @endif
                        @endif
                        <div id="paginationDiv" class="mt-4">
                            {{ $userAnime->links() }}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
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
    </script>
</x-app-layout>
