<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $anime->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                @if (session()->has('message'))
                    <span class="text-center">{{ session()->get('message') }}</span>
                @endif
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
                        @if (auth()->user())
                            @if (!auth()->user()->anime->contains($anime->id))
                                <form action="{{ route('anime.addToList', $anime->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                        Add to My Anime List
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('anime.deleteFromList', $anime->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="mt-4 bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                        Delete from My Anime List
                                    </button>
                                </form>
                                <form action="{{ route('user.anime.update',  ['username' => Auth::user()->username, 'redirectBack' => true]) }}" method="POST" class="bg-white dark:bg-gray-700 p-4 rounded shadow mt-4">
                                    @csrf
                                    @method('POST')

                                    <!-- Hidden input for anime_ids -->
                                    <input type="hidden" name="anime_ids[]" value="{{ $anime->id }}">

                                    <!-- Status -->
                                    <label for="watch_status_id" class="block text-sm font-medium text-gray-600 dark:text-gray-300">Status:</label>
                                    <select name="watch_status_id[]" class="mt-1 dark:bg-gray-800 dark:text-gray-300 form-select block w-full">
                                        @foreach ($watchStatuses as $status)
                                            <option value="{{ $status->id }}" {{ $currentUserStatus == $status->id ? 'selected' : '' }}>{{ $status->status }}</option>
                                        @endforeach
                                    </select>

                                    <!-- Progress -->
                                    <div class="mt-4">
                                        <label for="progress" class="block text-sm font-medium text-gray-600 dark:text-gray-300">In Progress:</label>
                                        <input type="checkbox" name="progress[]" value="1" {{ $currentUserProgress ? 'checked' : '' }} class="mt-1">
                                    </div>

                                    <!-- Score -->
                                    <label for="score" class="block text-sm font-medium text-gray-600 dark:text-gray-300 mt-4">Score:</label>
                                    <select name="score[]" class="mt-1 dark:bg-gray-800 dark:text-gray-300 form-select block w-full">
                                        <option value="" disabled selected>Pick an option</option>
                                        @for ($i = 1; $i <= 10; $i++)
                                            <option value="{{ $i }}" {{ $currentUserScore == $i ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>

                                    <!-- Sort Order -->
                                    <div class="mt-4">
                                        <label for="sort_order" class="block text-sm font-medium text-gray-600 dark:text-gray-300">Sort Order:</label>
                                        <input type="number" name="sort_order[]" value="{{ $currentUserSortOrder ?? '' }}" class="mt-1 dark:bg-gray-800 dark:text-gray-300 form-input block w-full">
                                    </div>

                                    <button type="submit" class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                        Update My Anime List
                                    </button>
                                </form>

                            @endif
                        @endif
                        <h4 class="font-bold mt-4 mb-2">Tags:</h4>
                        <ul>
                            @foreach (explode(', ', $anime->tags) as $tag)
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
                            @foreach (explode(', ', $anime->sources) as $source)
                                <li><a href="{{ $source }}" target="_blank" rel="noopener">{{ $source }}</a></li>
                            @endforeach
                        </ul>

                        <h4 class="font-bold mt-4 mb-2">Related Anime:</h4>
                        <ul>
                            @foreach (explode(', ', $anime->relations) as $relation)
                                <li><a href="{{ $relation }}" target="_blank" rel="noopener">{{ $relation }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
