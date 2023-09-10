<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Export Anime List
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 flex flex-wrap">
                    <div class="w-full md:w-3/5 mt-0">
                        <h4 class="font-bold mb-2">Export Your Anime List:</h4>
                        <form action="{{ route('export.animelistdata') }}" method="post">
                            @csrf
                            <label class="block text-sm font-bold mb-2" for="export_type">
                                Export Type
                            </label>
                            <select class="shadow appearance-none border rounded py-2 px-8 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" name="export_type" id="export_type">
                                <option value="myanimelist">MyAnimeList</option>
                                <option value="arigatou">ArigatouAnimeTracker</option>
                            </select>
                            <button type="submit" class="p-2 bg-blue-500 hover:bg-blue-700 text-white rounded-md mt-2">Export</button>
                        </form>
                        @if (session()->has('message'))
                            <span>{{ session()->get('message') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
