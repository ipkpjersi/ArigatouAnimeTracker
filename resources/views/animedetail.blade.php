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
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-white-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">There were one or more errors:</span>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
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
                        @if (!empty($anime->duration))
                            <p>
                                <strong>Duration:</strong>
                                @if ($anime->episodes > 1)
                                    {{ \Carbon\CarbonInterval::seconds($anime->duration)->cascade()->forHumans() }} per episode
                                @else
                                    {{ \Carbon\CarbonInterval::seconds($anime->duration)->cascade()->forHumans() }}
                                @endif
                            </p>
                        @endif
                        @if (!empty($anime->rating))
                            @php
                                $rating = strtoupper(str_replace('_', '-', $anime->rating));
                            @endphp
                            @switch($rating)
                                @case('R')
                                    @php $rating .= ' - Violence & Profanity'; @endphp
                                    @break
                                @case('R+')
                                    @php $rating .= ' - ' . str_rot13('Ahqvgl'); @endphp
                                    @break
                                @case('RX')
                                    @php $rating .= ' - ' . str_rot13('Uragnv'); @endphp
                                    @break
                            @endswitch
                            <p><strong>Rating:</strong> {{ $rating }}</p>
                        @endif
                        @if (!empty($anime->synonyms))
                            @php
                                $synonyms = explode(', ', $anime->synonyms);
                            @endphp
                            <h4 class="font-bold mt-4 cursor-pointer" onclick="toggleSynonyms()">Also known as:</h4>
                            <div id="synonyms-div" class="mb-2">
                                <span>
                                    {{ implode(', ', array_slice($synonyms, 0, 4)) }}
                                    <span id="hidden-synonyms" class="hidden">
                                        {{ count($synonyms) > 4 ? ', ' . implode(', ', array_slice($synonyms, 4)) : '' }}
                                    </span>
                                </span>
                                @if (count($synonyms) > 4)
                                    <button id="toggle-button" onclick="toggleSynonyms()">&#x25BC; Show More</button>
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
                                <form action="{{ route('user.anime.update',  ['username' => Auth::user()->username, 'redirectBack' => true]) }}" method="POST" class="bg-white dark:bg-gray-700 p-4 rounded shadow mt-2">
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
                                        <label for="progress" class="block text-sm font-medium text-gray-600 dark:text-gray-300">Progress:</label>
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

                        @if ($favouriteSystemEnabled)
                            @if (!auth()->user()->favourites->contains($anime->id))
                                <!-- Add to Favourites Button -->
                                <form action="{{ route('anime.addToFavourites', $anime->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                        Add to Favourites
                                    </button>
                                </form>
                            @else
                                <!-- Remove from Favourites Button -->
                                <form action="{{ route('anime.removeFromFavourites', $anime->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="mt-4 bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                        Remove from Favourites
                                    </button>
                                </form>
                                <!-- Manage Favourites Form -->
                                <form action="{{ route('anime.updateFavourite', $anime->id) }}" method="POST" class="bg-white dark:bg-gray-700 p-4 rounded shadow mt-2">
                                    @csrf
                                    @method('PUT')

                                    <!-- Hidden input for anime_id -->
                                    <input type="hidden" name="anime_id" value="{{ $anime->id }}">

                                    <!-- Show Publicly -->
                                    <div class="mt-1">
                                        <label for="show_publicly" class="block text-sm font-medium text-gray-600 dark:text-gray-300">Show Publicly:</label>
                                        <select name="show_publicly" class="mt-1 dark:bg-gray-800 dark:text-gray-300 form-select block w-full">
                                            <option value="1" {{ $favourite->pivot->show_publicly === 1 ? 'selected' : '' }}>Yes</option>
                                            <option value="0" {{ $favourite->pivot->show_publicly === 0 ? 'selected' : '' }}>No</option>
                                        </select>
                                    </div>

                                    <!-- Sort Order -->
                                    <div class="mt-4">
                                        <label for="sort_order" class="block text-sm font-medium text-gray-600 dark:text-gray-300">Sort Order:</label>
                                        <input type="number" name="sort_order" value="{{ $favourite->sort_order ?? 0 }}" min="0" class="mt-1 dark:bg-gray-800 dark:text-gray-300 form-input block w-full">
                                    </div>

                                    <button type="submit" class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                        Update Favourites
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
                        <div class="bg-white dark:bg-gray-700 rounded shadow mb-4 p-4 w-3/5">
                            <!-- First Row: MAL Score and Users -->
                            <div class="grid grid-cols-2 gap-1 max-w-md mx-auto">
                                <!-- First Row -->
                                <div><strong>MAL Score:</strong> {{ ($anime->mal_mean ?? 0) > 0 ? number_format($anime->mal_mean, 2) : 'N/A' }}</div>
                                <div><strong>MAL Users:</strong> {{ ($anime->mal_scoring_users ?? 0) > 0 ? number_format($anime->mal_scoring_users) : 'N/A' }}</div>

                                <!-- Second Row -->
                                <div><a href="{{ route('anime.top', ['sort' => 'highest_rated']) }}"><strong>MAL Ranked:</strong> {{ ($anime->mal_rank ?? 0) > 0 ? '#' . number_format($anime->mal_rank) : 'N/A' }}</a></div>
                                <div><a href="{{ route('anime.top', ['sort' => 'most_popular']) }}"><strong>MAL Popularity:</strong> {{ ($anime->mal_popularity ?? 0) > 0 ? '#' . number_format($anime->mal_popularity) : 'N/A' }}</a></div>

                                <div><strong>MAL Members:</strong> {{ ($anime->mal_list_members ?? 0) > 0 ? number_format($anime->mal_list_members) : 'N/A' }}</div>
                                <div><strong>AAT Score:</strong> {{ ($aatScore ?? 0) > 0 ? number_format($aatScore, 2) : "N/A" }}</div>
                                <div><strong>AAT Members:</strong> {{ ($aatMembers ?? 0) > 0 ? $aatMembers : "N/A" }}</div>
                                <div><strong>AAT Users:</strong> {{ ($aatUsers ?? 0) > 0 ? $aatUsers : "N/A" }}</div>

                                @if (Auth::user() !== null)
                                    <div><strong>My Score:</strong> {{ $currentUserScore > 0 ? number_format($currentUserScore, 2) : 'N/A' }}</div>
                                    <div><strong>My Status:</strong> {{ $currentUserStatus > 0 ? $watchStatuses[$currentUserStatus]->status ?? "N/A" : "N/A" }}</div>
                                @endif
                            </div>
                        </div>


                        <h4 class="font-bold mb-2">Description:</h4>
                        <p class="mb-4">{!! str_replace("\n", "<br>", empty(trim($anime->description)) ? "This title does not have a description yet." : $anime->description) !!}</p>

                        <h4 class="font-bold @if (!empty(trim($anime->description))) mt-4 @endif mb-2">More Details:</h4>
                        <ul class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach (explode(', ', $anime->sources) as $source)
                                <li><a href="{{ $source }}" target="_blank" rel="noopener">{{ $source }}</a></li>
                            @endforeach
                        </ul>

                        @if (!empty($anime->relations))
                            <h4 class="font-bold mt-4 mb-2">Related Anime:</h4>
                            <ul class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach (explode(', ', $anime->relations) as $relation)
                                    <li><a href="{{ $relation }}" target="_blank" rel="noopener">{{ $relation }}</a></li>
                                @endforeach
                            </ul>
                        @endif

                        @if (!empty($otherAnime))
                            <h4 class="font-bold mt-4 mb-2">Other Anime:</h4>
                            <div class="flex flex-wrap -mx-2" id="other-anime-list">
                                @foreach ($otherAnime as $anime)
                                    <div class="w-1/2 md:w-1/5 px-2 mb-4">
                                        <a href="/anime/{{ $anime->id }}/{{ Str::slug($anime->title) }}" class="block border p-2 h-full rounded-lg">
                                            <div class="h-full flex flex-col items-center">
                                                <img src="{{ $anime->thumbnail }}" onerror="this.onerror=null; this.src='/img/notfound.gif';" alt="{{ $anime->title }}" class="h-16 w-12 mb-2 mt-1 rounded">
                                                <h5 class="text-center">{{ Str::limit($anime->title, 40) }}</h5>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                            {{ $otherAnime->links() }}
                        @endif

                        <!-- Anime Reviews Section -->
                        @if (auth()->user() === null || auth()->user()->show_others_reviews === 1)
                            <div class="mt-8">
                                <h4 class="font-bold mb-2">Anime Reviews (Total: {{ $totalReviewsCount }}):</h4>
                                <form action="{{ route('anime.detail', ['id' => $anime->id, 'title' => $anime->title]) }}" method="GET" class="mb-3">
                                    <input type="checkbox" name="spoilers" value="1" onchange="this.form.submit()" {{ request('spoilers') ? 'checked' : '' }}> Include Spoilers
                                </form>
                                @forelse ($reviews as $review)
                                    <div class="mb-4 border-b pb-4">
                                        <h5 class="font-semibold">{{ $review->title }}</h5>
                                        <span id="less-{{ $review->id }}">
                                            {!! nl2br(strlen($review->body) > 100 ? e(substr($review->body, 0, 100)) . '...' : e($review->body)) !!}
                                            @if (strlen($review->body) > 100)
                                                <button onclick="toggleReviewContent({{ $review->id }})" id="button-{{ $review->id }}" class="font-bold">&#x25BC; Show More</button>
                                            @endif
                                        </span>
                                        @if (strlen($review->body) > 100)
                                            <span id="more-{{ $review->id }}" style="display: none;">
                                                {!! nl2br(e($review->body)) !!}
                                                <button onclick="toggleReviewContent({{ $review->id }})" id="button-less-{{ $review->id }}" class="font-bold">&#x25B2; Show Less</button>
                                            </span>
                                        @endif
                                        <p class="mt-2">
                                            <strong>Recommendation:</strong>
                                            <span class="inline-block rounded-full h-4 w-4"
                                                  style="background-color: {{
                                                      match($review->recommendation) {
                                                          'recommended' => '#3A8E40', // Green
                                                          'mixed' => '#DAA005',       // Yellow
                                                          'not_recommended' => '#A93226', // Red
                                                          default => '#000'          // Default color if none of the above
                                                      }
                                                  }};">
                                            </span>
                                            @switch($review->recommendation)
                                                @case('recommended')
                                                    Recommended
                                                    @break
                                                @case('mixed')
                                                    Mixed
                                                    @break
                                                @case('not_recommended')
                                                    Not Recommended
                                                    @break
                                                @default
                                                    Not Specified
                                            @endswitch
                                        </p>
                                        <p class="mt-1"><strong>By:</strong> <a href="{{route('users.detail', $review->user->username)}}"><img src="{{ $review->user->avatar ?? '/img/default-avatar.png' }}" alt="Avatar" style="width:50px; max-height:70px" onerror="this.onerror=null; this.src='/img/notfound.gif';"/> {{ $review->user->username }} on {{ $review->created_at->format('M d, Y H:i:s A') }}</a></p>
                                        <!-- Remove Review Button -->
                                        @if (auth()->user() && auth()->user()->isAdmin())
                                            <button data-review-id="{{ $review->id }}" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded removeReview mt-2 mb-3">Remove Review</button>
                                        @endif
                                    </div>
                                @empty
                                    <p>No reviews available.</p>
                                @endforelse

                                <!-- Pagination -->
                                {{ $reviews->links() }}
                            </div>

                            <!-- Add/Update Review Button -->
                            @if(auth()->user() !== null)
                                <button class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" id="toggleReviewForm" onclick="toggleReviewForm()">
                                    {{ $userHasReview ? 'Update Review' : 'Add Review' }}
                                </button>
                                @if (session()->has('reviewmessage'))
                                    <span class="text-center">{{ session()->get('reviewmessage') }}</span>
                                @endif
                            @endif

                            <!-- Review Form -->
                            <div id="reviewForm" class="hidden bg-white dark:bg-gray-800 p-4 rounded shadow mt-4">
                                <form action="{{ $userHasReview ? route('anime.updateReview', $anime->id) : route('anime.addReview') }}" method="POST" class="space-y-4">
                                    @csrf
                                    @if($userHasReview)
                                        @method('PUT')
                                    @endif

                                    <!-- Hidden Input for Anime ID -->
                                    <input type="hidden" name="anime_id" value="{{ $anime->id }}">

                                    <!-- Review Title -->
                                    <div>
                                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Review Title:</label>
                                        <input type="text" id="title" name="title" value="{{ $userReview->title ?? '' }}" class="form-input w-full rounded border-gray-300 dark:bg-gray-700 dark:text-gray-200">
                                    </div>

                                    <!-- Review Body -->
                                    <div>
                                        <label for="body" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Review:</label>
                                        <textarea id="body" name="body" class="form-textarea w-full rounded border-gray-300 dark:bg-gray-700 dark:text-gray-200" rows="4">{{ $userReview->body ?? '' }}</textarea>
                                    </div>

                                    <!-- Recommendation Dropdown -->
                                    <div>
                                        <label for="recommendation" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Your Recommendation:</label>
                                        <select id="recommendation" name="recommendation" class="mt-1 form-select w-full rounded border-gray-300 dark:bg-gray-700 dark:text-gray-200">
                                            <option value="recommended" {{ (empty($userReview->recommendation) || $userReview->recommendation === 'recommended') ? 'selected' : '' }}>Recommended</option>
                                            <option value="mixed" {{ ($userReview->recommendation ?? '') === 'mixed' ? 'selected' : '' }}>Mixed</option>
                                            <option value="not_recommended" {{ ($userReview->recommendation ?? '') === 'not_recommended' ? 'selected' : '' }}>Not Recommended</option>
                                        </select>
                                    </div>


                                    <!-- Spoiler Checkbox -->
                                    <div class="flex items-center">
                                        <label class="flex items-center space-x-2">
                                            <input type="checkbox" id="contains_spoilers" name="contains_spoilers" value="1" {{ $userReview->contains_spoilers ?? false ? 'checked' : '' }} class="form-checkbox rounded border-gray-300 dark:bg-gray-700">
                                            <span class="text-gray-700 dark:text-gray-200">Contains Spoilers</span>
                                        </label>
                                    </div>

                                    <!-- Show Review Publicly Checkbox -->
                                    <div class="flex items-center mt-4">
                                        <label class="flex items-center space-x-2">
                                            <input type="checkbox" id="show_review_publicly" name="show_review_publicly" value="1" {{ $userReview->show_review_publicly ?? true ? 'checked' : '' }} class="form-checkbox rounded border-gray-300 dark:bg-gray-700">
                                            <span class="text-gray-700 dark:text-gray-200">Show Review Publicly</span>
                                        </label>
                                    </div>


                                    <!-- Submit Button -->
                                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Submit Review</button>

                                    <!-- Delete Review Button (only if the user has a review) -->
                                    @if($userHasReview)
                                        <button form="deleteReviewForm" type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Delete Review</button>
                                    @endif
                                </form>

                                <!-- Delete Review Form -->
                                @if($userHasReview)
                                    <form id="deleteReviewForm" action="{{ route('anime.deleteReview', $anime->id) }}" method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @endif

                                <!-- Hide Review Form Button -->
                                <button class="mt-2 bg-gray-300 hover:bg-gray-400 text-black font-bold py-2 px-4 rounded" onclick="toggleReviewForm()">Hide Form</button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.location.search.includes('otheranimepage')) {
                document.getElementById('other-anime-list').scrollIntoView({ behavior: 'smooth' });
            }
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
                toggleButton.innerHTML = '&#x25B2; Show Less';
            } else {
                hiddenSynonyms.classList.add('hidden');
                toggleButton.innerHTML = '&#x25BC; Show More';
            }
        }

        function toggleReviewForm() {
            let form = document.getElementById('reviewForm');
            form.classList.toggle('hidden');
        }

        function toggleReviewContent(reviewId) {
            let moreText = document.getElementById("more-" + reviewId);
            let lessText = document.getElementById("less-" + reviewId);

            if (moreText.style.display === "none") {
                moreText.style.display = "inline";
                lessText.style.display = "none";
            } else {
                moreText.style.display = "none";
                lessText.style.display = "inline";
            }
        }
    </script>
    <script type="module">
        $(document).on('click', '.removeReview', function() {
            let reviewId = $(this).data('review-id');
            axios.post(`/reviews/${reviewId}/remove`, {
                _token: '{{ csrf_token() }}'
            })
            .then(function(response) {
                //alert(response.data.message);
                location.reload();
            })
            .catch(function(error) {
                alert('Error removing review: ' + error);
            });
        });
    </script>
</x-app-layout>
