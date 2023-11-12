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
                                        <div class="max-w-[50px] w-1/4 p-1">
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
                                        <button type="submit" class="p-2 bg-red-500 hover:bg-red-700 text-white rounded-md mt-2">Remove Friend</button>
                                    </form>
                                @else
                                    <form action="/add-friend/{{ $user->id }}" method="POST">
                                        @csrf
                                        <button type="submit" class="p-2 bg-blue-500 hover:bg-blue-700 text-white rounded-md mt-2">Add Friend</button>
                                    </form>
                                @endif
                            @endif
                        @endif
                    </div>

                    <!-- Right Column -->
                    <div class="w-full md:w-3/5 mt-0 flex flex-row flex-wrap">
                        @if(request('view') == 'friends')
                            <div class="mb-4 flex border-b">
                                <!-- Home Tab -->
                                <a href="{{ route('users.detail', $user->username) }}" class="tab-button {{ !request()->get('view') ? 'bg-blue-500 text-white' : 'dark:text-white' }} py-2 px-4">Home</a>
                                <!-- Friends Tab -->
                                <a href="{{ route('users.detail', [$user->username, 'view' => 'friends']) }}" class="tab-button {{ request()->get('view') == 'friends' ? 'bg-blue-500 text-white' : 'dark:text-white' }} py-2 px-4">Friends</a>
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
</x-app-layout>
