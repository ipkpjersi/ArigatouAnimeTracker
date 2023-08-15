<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            User Anime List
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
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-600">#</th>
                                    @endif
                                    <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-600">Name</th>
                                    <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-600">Type</th>
                                    <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-600">Status</th>
                                    <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-600">Score</th>
                                    @if(auth()->user()->username === $username)
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-600">Sort Order</th>
                                    @endif
                                    <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-600">Episodes</th>
                                    <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-600">Season</th>
                                    <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-600">Year</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($userAnime as $anime)
                                    <tr>
                                        <input type="hidden" name="anime_ids[]" value="{{ $anime->id }}">
                                        @if ($show_anime_list_number)
                                            <td class="py-2 px-4 border-b border-gray-200">{{ (($userAnime->currentPage() - 1) * $userAnime->perPage()) + $loop->iteration }}</td>
                                        @endif
                                        <td class="py-2 px-4 border-b border-gray-200">{{ $anime->title }}</td>
                                        <td class="py-2 px-4 border-b border-gray-200">{{ optional($anime->anime_type)->type }}</td>
                                        <td class="py-2 px-4 border-b border-gray-200">{{ optional($anime->anime_status)->status }}</td>
                                        <td class="py-2 px-4 border-b border-gray-200">
                                            @if(auth()->user()->username === $username)
                                                <select name="score[]" class="border rounded w-full py-2 px-3 dark:bg-gray-800" style="padding-right: 36px">
                                                    <option value="">Pick an option...</option>
                                                    @for ($i = 1; $i <= 10; $i++)
                                                        <option value="{{ $i }}" @if($anime->pivot->score == $i) selected @endif>{{ $i }}</option>
                                                    @endfor
                                                </select>
                                            @else
                                                {{ $anime->pivot->score ?? '' }}
                                            @endif
                                        </td>
                                        @if(auth()->user()->username === $username)
                                            <td class="py-2 px-4 border-b border-gray-200">
                                                <input type="number" min="1" name="sort_order[]" value="{{ $anime->pivot->sort_order }}" class="border rounded w-24 py-2 px-3 dark:bg-gray-800">
                                            </td>
                                        @endif
                                        <td class="py-2 px-4 border-b border-gray-200">{{ $anime->episodes }}</td>
                                        <td class="py-2 px-4 border-b border-gray-200">{{ $anime->season }}</td>
                                        <td class="py-2 px-4 border-b border-gray-200">{{ $anime->year }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if(auth()->user()->username === $username)
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-4">
                                Save Changes
                            </button>
                        @endif
                        <div id="paginationDiv" class="mt-4">
                            {{ $userAnime->links() }}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
