<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $anime->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 flex flex-wrap">
                    <!-- Left Column -->
                    <div class="w-full md:w-56 mb-6 md:mb-0 md:mr-6 flex-none mt-0">
                        <h3 class="font-bold mb-1">{{ $anime->title }}</h3>
                        <img onerror="this.onerror=null; this.src='/img/notfound.gif';" class="rounded-lg shadow-md mb-3" src="{{ $anime->picture }}" alt="{{ $anime->title }}" />
                        <p><strong>Type:</strong> {{ $anime->anime_type->type }}</p>
                        <p><strong>Status:</strong> {{ $anime->anime_status->status }}</p>
                        <p><strong>Episodes:</strong> {{ $anime->episodes }}</p>
                        <p><strong>Season:</strong> {{ $anime->season }}</p>
                        <p><strong>Year:</strong> {{ $anime->year }}</p>
                        @if (auth()->user() && !auth()->user()->anime->contains($anime->id))
                            <form action="{{ route('anime.addToList', $anime->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Add to My Anime List
                                </button>
                            </form>
                        @endif
                        <h4 class="font-bold mt-4 mb-2">Tags:</h4>
                        <ul>
                            @foreach(explode(', ', $anime->tags) as $tag)
                                <li>{{ $tag }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Right Column -->
                    <div class="w-full md:w-3/5 mt-0">
                        @if (!empty($anime->synonyms))
                            <h4 class="font-bold mb-2">Also known as:</h4>
                            <p>{{ $anime->synonyms }}</p>
                        @endif

                        <h4 class="font-bold @if (!empty($anime->synonyms)) mt-4 @endif mb-2">More Details:</h4>
                        <ul>
                            @foreach(explode(', ', $anime->sources) as $source)
                                <li><a href="{{ $source }}" target="_blank" rel="noopener">{{ $source }}</a></li>
                            @endforeach
                        </ul>

                        <h4 class="font-bold mt-4 mb-2">Related Anime:</h4>
                        <ul>
                            @foreach(explode(', ', $anime->relations) as $relation)
                                <li><a href="{{ $relation }}" target="_blank" rel="noopener">{{ $relation }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
