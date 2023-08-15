<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            User Anime List
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <table class="min-w-full">
                        <thead>
                            <tr>
                                <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-600">Name</th>
                                <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-600">Type</th>
                                <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-600">Status</th>
                                <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-600">Score</th>
                                <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-600">Sort Order</th>
                                <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-600">Episodes</th>
                                <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-600">Season</th>
                                <th class="py-2 px-4 border-b border-gray-200 text-left text-sm uppercase font-semibold text-gray-600">Year</th>
                                <!-- Add more table headers based on your fields here -->
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($userAnime as $anime)
                                <tr>
                                    <td class="py-2 px-4 border-b border-gray-200">{{ $anime->title }}</td>
                                    <td class="py-2 px-4 border-b border-gray-200">{{ optional($anime->anime_type)->type }}</td>
                                    <td class="py-2 px-4 border-b border-gray-200">{{ optional($anime->anime_status)->status }}</td>
                                    <td class="py-2 px-4 border-b border-gray-200">{{ $anime->pivot->score }}</td>
                                    <td class="py-2 px-4 border-b border-gray-200">{{ $anime->pivot->sort_order }}</td>
                                    <td class="py-2 px-4 border-b border-gray-200">{{ $anime->episodes }}</td>
                                    <td class="py-2 px-4 border-b border-gray-200">{{ $anime->season }}</td>
                                    <td class="py-2 px-4 border-b border-gray-200">{{ $anime->year }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if (config('config.user_anime_list_paginated'))
                        <div class="mt-4">
                            {{ $userAnime->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
