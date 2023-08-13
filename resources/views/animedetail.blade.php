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
                    <div class="w-5/5 md:w-48 pr-8 mb-6 md:mb-0 md:mr-4 flex-none">
                        <img class="rounded-lg shadow-md w-full" src="{{ $anime->picture }}" alt="{{ $anime->title }}" />
                        <h3 class="mt-4 font-bold">{{ $anime->title }}</h3>
                        <p><strong>Type:</strong> {{ $anime->anime_type->type }}</p>
                        <p><strong>Status:</strong> {{ $anime->anime_status->status }}</p>
                        <p><strong>Episodes:</strong> {{ $anime->episodes }}</p>
                        <p><strong>Season:</strong> {{ $anime->season_display }}</p>
                        <p><strong>Year:</strong> {{ $anime->year }}</p>
                    </div>

                    <!-- Right Column -->
                    <div class="w-5/5 md:w-3/5 md:pl-8 mt-6 md:mt-0">
                        <h4 class="font-bold mb-2">Synonyms:</h4>
                        <p>{{ $anime->synonyms }}</p>

                        <h4 class="font-bold mt-4 mb-2">Relations:</h4>
                        <p>{{ $anime->relations }}</p>

                        <h4 class="font-bold mt-4 mb-2">Tags:</h4>
                        <ul>
                            @foreach(explode(', ', $anime->tags) as $tag)
                                <li>{{ $tag }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
