<x-app-layout>
    <x-slot name="title">
        {{ config('app.name', 'Laravel') }} - {{ $user->username }}'s Profile
    </x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $user->username }}'s Profile
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 flex flex-wrap">
                    <!-- Left Column -->
                    <div class="w-full md:w-56 mb-6 md:mb-0 md:mr-6 flex-none mt-0">
                        <h3 class="font-bold mb-1">{{ $user->username }}</h3>
                        <img onerror="this.onerror=null; this.src='/img/notfound.gif';" class="rounded-lg shadow-md mb-3" style="width:150px; height: 150px;" src="{{ $user->avatar }}" alt="{{ $user->username }}" />
                        <p><strong>Joined:</strong> {{ \Carbon\Carbon::parse($user->created_at)->format('M d, Y') }}</p>
                        @if ($user->is_admin === 1)
                            <p><strong>Role:</strong> {{ 'Admin' }}</p>
                        @endif
                        <a href="/animelist/{{ $user->username }}" class="inline-block">
                            <button id="animeListButton" class="p-2 bg-blue-500 hover:bg-blue-700 text-white rounded-md mt-2">Anime List</button>
                        </a>
                        <!-- Friends Section -->
                        @if ($canViewFriends)
                            <div class="w-full md:w-3/5 mt-4">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="font-bold">Friends</h4>
                                    <a href="/users/{{ $user->username }}?view=friends" class="text-blue-500 hover:text-blue-700">All ({{ count($user->friends) }})</a>
                                </div>
                                <div class="flex flex-wrap mb-4">
                                    @foreach ($friends as $friend)
                                        <div class="max-w-[50px] w-1/2 p-1">
                                            <a href="/users/{{ $friend->username }}" class="block text-center">
                                                <img src="{{ $friend->avatar }}" alt="{{ $friend->username }}" class="rounded-full w-full avatar-image" onerror="this.onerror=null; this.src='/img/notfound.gif';">
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if ($enableFriendsSystem)
                            @if (auth()->user() !== null && !$isOwnProfile)
                                @if (auth()->user()->isFriend($user->id))
                                    <form action="/remove-friend/{{ $user->id }}" method="POST">
                                        @csrf
                                        <button type="submit" class="p-2 bg-red-500 hover:bg-red-700 text-white rounded-md mt-2 mb-4">Remove Friend</button>
                                    </form>
                                    <form action="{{ route('toggle-friend-publicly', $user->id) }}" method="POST" id="toggle-friend-publicly-form-{{ $user->id }}">
                                        @csrf
                                        <div class="flex items-center mt-4">
                                            <label class="flex items-center space-x-2">
                                                <input type="checkbox"
                                                       name="show_friend_publicly"
                                                       value="1"
                                                       onchange="document.getElementById('toggle-friend-publicly-form-{{ $user->id }}').submit();"
                                                       {{ $friendUser->pivot->show_friend_publicly === 1 ? 'checked' : '' }}
                                                       class="form-checkbox rounded border-gray-300 dark:bg-gray-700">
                                                <span class="text-gray-700 dark:text-gray-200">Show Friend Publicly</span>
                                            </label>
                                        </div>
                                    </form>
                                @else
                                    <form action="/add-friend/{{ $user->id }}" method="POST">
                                        @csrf
                                        <button type="submit" class="p-2 bg-blue-500 hover:bg-blue-700 text-white rounded-md mt-2 mb-4">Add Friend</button>
                                    </form>
                                @endif
                            @endif
                        @endif
                        @if ($enableReviewsSystem)
                            <div class="flex justify-between mb-4">
                                <a href="{{ route('users.detail', [$user->username, 'view' => 'reviews']) }}" class="text-blue-500 hover:text-blue-700">All Reviews ({{ $totalReviewsCount }})</a>
                            </div>
                        @endif
                    </div>

                    <!-- Right Column -->
                    <div class="w-full md:w-3/5 mt-0 flex flex-row flex-wrap">
                        @if($canViewReviews && request('view') == 'reviews')
                            <div class="mb-4 flex border-b items-end">
                                <!-- Home Tab -->
                                <a href="{{ route('users.detail', $user->username) }}" class="tab-button {{ !request()->get('view') ? 'bg-blue-500 text-white' : 'dark:text-white' }} py-2 px-4">Home</a>
                                <!-- Friends Tab -->
                                <a href="{{ route('users.detail', [$user->username, 'view' => 'friends']) }}" class="tab-button {{ request()->get('view') == 'friends' ? 'bg-blue-500 text-white' : 'dark:text-white' }} py-2 px-4">Friends</a>
                                <!-- Reviews Tab -->
                                <a href="{{ route('users.detail', [$user->username, 'view' => 'reviews']) }}" class="tab-button {{ request()->get('view') == 'reviews' ? 'bg-blue-500 text-white' : 'dark:text-white' }} py-2 px-4">Reviews</a>
                            </div>

                            {{-- Display Reviews --}}
                            <div class="grid grid-cols-1 gap-4 w-full">
                                @foreach ($reviews as $review)
                                    <div class="mb-4 border-b pb-4">
                                        <h4 class="font-bold mb-2">
                                            <a href="{{ route('anime.detail', $review->anime->id) }}">
                                                {{ $review->anime->title }}
                                                <img src="{{ $review->anime->thumbnail }}" alt="{{ $review->anime->title }}" style="width:50px; height:70px; margin-right:10px; vertical-align:middle;" onerror="this.onerror=null; this.src='/img/notfound.gif';">
                                            </a>
                                        </h4>
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
                                        <p class="mt-1"><strong>By:</strong> <img src="{{ $review->user->avatar ?? '/img/default-avatar.png' }}" alt="Avatar" style="width:50px; max-height:70px" onerror="this.onerror=null; this.src='/img/notfound.gif';"/> {{ $review->user->username }} on {{ $review->created_at->format('M d, Y H:i:s A') }}</p>
                                    </div>
                                @endforeach
                                {{-- Include Spoilers Checkbox --}}
                                <form action="{{ route('users.detail', $user->username) }}" method="GET">
                                    <input type="hidden" name="view" value="reviews">
                                    <label for="spoilers" class="inline-flex items-center">
                                        <input type="checkbox" id="spoilers" name="spoilers" value="1" {{ request('spoilers') ? 'checked' : '' }} onchange="this.form.submit()">
                                        <span class="ml-2">Include Spoilers</span>
                                    </label>
                                </form>
                            </div>

                            {{-- Pagination Links --}}
                            <div class="mt-4 flex-grow">
                                {{ $reviews->appends(['view' => 'reviews', 'spoilers' => request('spoilers')])->links() }}
                            </div>
                        @elseif($canViewFriends && request('view') == 'friends')
                            <div class="mb-4 flex border-b items-end">
                                <!-- Home Tab -->
                                <a href="{{ route('users.detail', $user->username) }}" class="tab-button {{ !request()->get('view') ? 'bg-blue-500 text-white' : 'dark:text-white' }} py-2 px-4">Home</a>
                                <!-- Friends Tab -->
                                <a href="{{ route('users.detail', [$user->username, 'view' => 'friends']) }}" class="tab-button {{ request()->get('view') == 'friends' ? 'bg-blue-500 text-white' : 'dark:text-white' }} py-2 px-4">Friends</a>
                                <!-- Reviews Tab -->
                                <a href="{{ route('users.detail', [$user->username, 'view' => 'reviews']) }}" class="tab-button {{ request()->get('view') == 'reviews' ? 'bg-blue-500 text-white' : 'dark:text-white' }} py-2 px-4">Reviews</a>
                            </div>

                            <!-- Grid View for Friends -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-4 w-full">
                                @foreach ($friends as $friend)
                                    <div class="m-2 p-4 rounded-lg shadow-lg bg-gray-100 dark:bg-gray-700 flex flex-col justify-between min-h-[125px] relative">
                                        <div class="relative z-20">
                                            <a href="/users/{{ $friend->username }}">
                                                <h3 class="text-xl font-semibold mb-2">{{ $friend->username }}</h3>
                                                <img src="{{ $friend->avatar }}" alt="{{ $friend->username }}" class="rounded mb-4 avatar-image" onerror="this.onerror=null; this.src='/img/notfound.gif';">
                                            </a>
                                            <p class="text-sm">Added on: {{ $friend->pivot->created_at->format('M d, Y h:i:s A') }}</p>
                                        </div>
                                    </div>
                                @endforeach
                                @if ($isOwnProfile)
                                    {{-- Show All Friends Checkbox --}}
                                    <div class="mt-4 col-span-full">
                                        <form action="{{ route('users.detail', $user->username) }}" method="GET">
                                            <input type="hidden" name="view" value="friends">
                                            <label for="showallfriends" class="inline-flex items-center">
                                                <input type="checkbox" id="showallfriends" name="showallfriends" value="1" {{ request('showallfriends') ? 'checked' : '' }} onchange="this.form.submit()">
                                                <span class="ml-2">Show All Friends</span>
                                            </label>
                                        </form>
                                    </div>
                                @endif
                            </div>
                            {{-- We need flex-grow because the parent is a flex container/element. --}}
                            <div class="mt-4 flex-grow">
                                {{ $friends->appends(['view' => 'friends'])->links() }}
                            </div>
                        @else
                            <!-- Left sub-column for days watched and watch status -->
                            <div class="w-full md:w-56">
                                <h5 class="font-bold mb-4">Days Watched: <span class="font-normal">{{ number_format($stats['totalDaysWatched'], 2) }} days</span></h5>

                                <h5 class="font-bold mb-2">Status Counts:</h5>
                                <ul>
                                    @foreach ($stats['animeStatusCounts'] as $status => $count)
                                        <li>
                                            <span class="inline-block rounded-full h-4 w-4 mr-2"
                                                  style="
                                                    background-color: {{ match($status) {
                                                        'WATCHING' => '#3A8E40',
                                                        'COMPLETED' => '#1D77C3',
                                                        'ON-HOLD' => '#DAA005',
                                                        'DROPPED' => '#A93226',
                                                        'PLAN-TO-WATCH' => '#7D1F8F',
                                                        default => '#000'
                                                    } }};
                                                  ">
                                            </span>
                                            {{ $status }}: {{ $count }}
                                        </li>
                                    @endforeach
                                </ul>
                                @if ($enableScoreCharts && $showChart)
                                    <canvas id="userScoreChart" width="400" height="400" class="mt-4"></canvas>
                                @endif
                            </div>

                            <!-- Right sub-column for total completed, total episodes watched, and average score -->
                            <div class="w-full md:w-3/5">
                                <h4 class="font-bold mb-4">Average Score: <span class="font-normal">{{ number_format($stats['averageScore'], 2) }}</span></h4>
                                <h4 class="font-bold mb-2">Statistics:</h4>
                                <p>Total Anime Completed: {{ $stats['totalCompleted'] }}</p>
                                <p>Total Episodes Watched: {{ $stats['totalEpisodes'] }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
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
        import '/js/chart.js';
        document.addEventListener('DOMContentLoaded', function () {
            let ctx = document.getElementById('userScoreChart').getContext('2d');
            let userScoreDistribution = @json($userScoreDistribution);
            const scoreToColorMap = {
                1: '#FF0000', // red
                2: '#FF4500', // orange-red
                3: '#FFA500', // orange
                4: '#FFD700', // yellow
                5: '#005600', // lime green
                6: '#006a00', // dark green
                7: '#218c21', // green
                8: '#0bb9b1', // light sea green
                9: '#0079eb', // dodger blue
                10: '#1E90FF', // dodger blue
            };
            let dynamicScoreColors = Object.keys(userScoreDistribution).map(score => scoreToColorMap[score]);

            let data = {
                labels: Object.keys(userScoreDistribution),
                datasets: [{
                    label: 'Score Distribution',
                    data: Object.values(userScoreDistribution),
                    backgroundColor: dynamicScoreColors,
                    borderWidth: 1
                }]
            };
            function getLabelTextColor() {
                const isLightMode = document.documentElement.classList.contains('light');
                return isLightMode ? '#000000' : '#FFFFFF';
            }
            let options = {
                plugins: {
                    legend: {
                        labels: {
                            color: getLabelTextColor() // set label text color
                        }
                    }
                }
                // other options...
            };

            let chart = new Chart(ctx, {
                type: 'pie',
                data: data,
                options: options
            });
        });
    </script>
</x-app-layout>
