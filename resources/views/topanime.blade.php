<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Top Anime by MAL Score
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Title</th>
                                <th>MAL Score</th>
                                <th>My Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topAnime as $index => $anime)
                                <tr>
                                    <td>{{ (($topAnime->currentPage() - 1 ) * $topAnime->perPage() ) + $loop->iteration }}</td>
                                    <td>
                                        <img src="{{ $anime->thumbnail }}" alt="{{ $anime->title }}" class="inline-block" width="50" height="70">
                                        <a href="{{ route('anime.detail', $anime->id) }}" class="ml-4">{{ $anime->title }}</a>
                                    </td>
                                    <td>{{ $anime->mal_mean }}</td>
                                    <td>{{ $userScores[$anime->id] ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div id="paginationDiv" class="mt-4">
                        {{ $topAnime->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
