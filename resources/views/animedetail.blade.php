<x-app-layout>
    <x-slot name="title">
        {{ config('app.name', 'Laravel') }} - Anime Details for {{ $anime->title }}
    </x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $anime->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                @if (session()->has('popup'))
                    <div id="status-modal" class="fixed top-0 left-0 w-full h-full bg-opacity-50 bg-black flex justify-center items-center z-50">
                      <div class="p-4 bg-white dark:bg-black rounded">
                        <p id="status-message">{{ session()->get('popup') }}</p>
                      </div>
                    </div>
                @endif
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
                        @if (!empty($anime->synonyms))
                            @php
                                $synonyms = explode(', ', $anime->synonyms);
                            @endphp
                            <h4 class="font-bold mt-4 cursor-pointer" onclick="toggleSynonyms()">Also known as:</h4>
                            <div id="synonyms-div">
                                <span>
                                    {{ implode(', ', array_slice($synonyms, 0, 4)) }}
                                    <span id="hidden-synonyms" class="hidden">
                                        {{ count($synonyms) > 4 ? ', ' . implode(', ', array_slice($synonyms, 4)) : '' }}
                                    </span>
                                </span>
                                @if (count($synonyms) > 4)
                                    <button id="toggle-button" onclick="toggleSynonyms()">&#x25BC; More</button>
                                @endif
                            </div>
                        @endif
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
                                        <select name="progress[]" class="mt-1 dark:bg-gray-800 dark:text-gray-300 form-select block w-full">
                                            @for ($i = 0; $i <= $anime->episodes; $i++)
                                                <option value="{{ $i }}" {{ $i == ($currentUserProgress ?? 0) ? 'selected' : '' }}>{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>


                                    <!-- Score -->
                                    <label for="score" class="block text-sm font-medium text-gray-600 dark:text-gray-300 mt-4">Score:</label>
                                    <select name="score[]" class="mt-1 dark:bg-gray-800 dark:text-gray-300 form-select block w-full">
                                        <option value="" selected>Pick an option</option>
                                        @for ($i = 1; $i <= 10; $i++)
                                            <option value="{{ $i }}" {{ $currentUserScore == $i ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>

                                    <!-- Sort Order -->
                                    <div class="mt-4">
                                        <label for="sort_order" class="block text-sm font-medium text-gray-600 dark:text-gray-300">Sort Order:</label>
                                        <input type="number" name="sort_order[]" value="{{ $currentUserSortOrder ?? '' }}" min="0" class="mt-1 dark:bg-gray-800 dark:text-gray-300 form-input block w-full">
                                    </div>

                                    <!-- Notes -->
                                    <div class="mt-4">
                                        <label for="notes" class="block text-sm font-medium text-gray-600 dark:text-gray-300">Notes:</label>
                                        <textarea name="notes[]" class="mt-1 dark:bg-gray-800 dark:text-gray-300 form-input block w-full" rows="3">{{ $currentUserNotes ?? '' }}</textarea>
                                    </div>

                                    <!-- Display in List -->
                                    <div class="mt-4">
                                        <label for="display_in_list" class="block text-sm font-medium text-gray-600 dark:text-gray-300">Display in List:</label>
                                        <select name="display_in_list[]" class="mt-1 dark:bg-gray-800 dark:text-gray-300 form-select block w-full">
                                            <option value="1" {{ ($currentUserDisplayInList === 1) ? 'selected' : '' }}>Yes</option>
                                            <option value="0" {{ ($currentUserDisplayInList === 0) ? 'selected' : '' }}>No</option>
                                        </select>
                                    </div>

                                    <!-- Show Anime Notes Publicly -->
                                    <div class="mt-4">
                                        <label for="show_anime_notes_publicly" class="block text-sm font-medium text-gray-600 dark:text-gray-300">Show Notes Publicly:</label>
                                        <select name="show_anime_notes_publicly[]" class="mt-1 dark:bg-gray-800 dark:text-gray-300 form-select block w-full">
                                            <option value="1" {{ ($currentUserShowAnimeNotesPublicly === 1) ? 'selected' : '' }}>Yes</option>
                                            <option value="0" {{ ($currentUserShowAnimeNotesPublicly === 0) ? 'selected' : '' }}>No</option>
                                        </select>
                                    </div>

                                    <button type="submit" class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                        Update My Anime List
                                    </button>
                                </form>

                            @endif
                        @endif
                        <h4 class="font-bold mt-4">Tags:</h4>
                        <ul>
                            @foreach (explode(', ', $anime->tags) as $tag)
                                <a href="{{ route("anime.list", ["search" => $tag]) }}"><li>{{ $tag }}</li></a>
                            @endforeach
                        </ul>
                    </div>


                    <!-- Right Column -->
                    <div class="w-full md:w-3/5 mt-0">

                        <h4 class="font-bold mb-2">Description:</h4>
                        <p class="mb-4">{!! str_replace("\n", "<br>", $anime->description ?? "This title does not have a description yet.") !!}</p>

                        <h4 class="font-bold @if (!empty($anime->description)) mt-4 @endif mb-2">More Details:</h4>
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
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Check if the element exists
            const statusModal = document.getElementById('status-modal');
            if (statusModal) {
                setTimeout(() => {
                    statusModal.classList.add('hidden');
                }, 3000);  // 3 seconds
            }
        });
        function toggleSynonyms() {
            const hiddenSynonyms = document.getElementById('hidden-synonyms');
            const toggleButton = document.getElementById('toggle-button');
            if (hiddenSynonyms.classList.contains('hidden')) {
                hiddenSynonyms.classList.remove('hidden');
                toggleButton.innerHTML = '&#x25B2; Less';
            } else {
                hiddenSynonyms.classList.add('hidden');
                toggleButton.innerHTML = '&#x25BC; More';
            }
        }
    </script>
</x-app-layout>
