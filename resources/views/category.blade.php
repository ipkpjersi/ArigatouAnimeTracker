<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $category }} Anime
        </h2>
        <a href="{{ route('anime.category', ['category' => $category, 'view' => 'list'] + request()->query()) }}">List View</a> |
        <a href="{{ route('anime.category', ['category' => $category, 'view' => 'card'] + request()->query()) }}">Card View</a>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="dark:bg-gray-800 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if ($viewType === 'list')
                        <!-- Table View Here -->
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-4">
                            @foreach ($categoryAnime as $anime)
                                <div class="m-2 p-4 rounded-lg shadow-lg bg-gray-100 dark:bg-gray-700 min-h-[300px]">
                                    <div class="flex flex-col">
                                        <h3 class="text-xl font-semibold mb-2">{{ $anime->title }}</h3>
                                        <img src="{{ $anime->picture }}" alt="{{ $anime->title }}" width="100" height="140" class="rounded mb-4">
                                        <div>
                                            <p class="text-sm text-gray-600">{{ Str::limit($anime->description, 300) }}</p>
                                            <div class="mt-2">
                                                <span class="text-sm dark:text-gray-300">MAL Score: {{ $anime->mal_mean }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            <div id="paginationDiv" class="mt-4">
                {{ $categoryAnime->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
