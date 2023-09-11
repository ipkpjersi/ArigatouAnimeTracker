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
                                <div class="m-2 p-4 rounded-lg shadow-lg bg-gray-100 dark:bg-gray-700 flex flex-col justify-between min-h-[300px] relative">
                                    <div class="relative z-20">
                                        <a href="{{ route("anime.detail", $anime->id) }}">
                                        <h3 class="text-xl font-semibold mb-2">{{ $anime->title }}</h3>
                                        <img src="{{ $anime->picture }}" alt="{{ $anime->title }}" width="100" height="140" class="rounded mb-4">
                                        <p class="text-sm text-gray-600">{{ Str::limit($anime->description, 300) }}</p>
                                        </a>
                                    </div>
                                    <div class="flex items-center relative z-20">
                                        <span class="text-sm dark:text-gray-300 flex-1">MAL Score: {{ $anime->mal_mean }}</span>
                                        <span class="text-sm dark:text-gray-300 flex-1">MAL Members: {{ $anime->mal_list_members }}</span>
                                        @if (Auth::user())
                                            @php
                                                $userAnime = $anime->users->firstWhere('id', Auth::id());
                                            @endphp
                                            <button class="text-sm bg-blue-500 text-white rounded p-1 flex-1 focus:outline-none z-50">
                                                {{ App\Models\WatchStatus::where('id', '=', optional($userAnime)->pivot?->watch_status_id)->first()->status ?? 'Add to List' }}
                                            </button>
                                        @endif
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
