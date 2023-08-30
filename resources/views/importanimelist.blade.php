<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Import Anime Export File
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 flex flex-wrap">
                    <div class="w-full mt-0">
                        <h4 class="font-bold mb-2">Import Anime Export:</h4>
                        <form action="{{ route('import.animelistdata') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <label class="block text-sm font-bold mb-2" for="import_type">
                                Import Type
                            </label>
                            <select class="shadow appearance-none border rounded py-2 pl-4 pr-8 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" name="import_type" id="import_type">
                                <option value="myanimelist">MyAnimeList</option>
                                <option value="arigatou">ArigatouAnimeTracker</option>
                            </select>
                            <input type="file" name="anime_data_file" accept=".xml,.json">
                            <button type="submit" class="p-2 bg-blue-500 hover:bg-blue-700 text-white rounded-md mt-2">Upload and Import</button>
                        </form>
                        @if(session()->has('message'))
                            <span>{{ session()->get('message') }}</span>
                        @endif
                        @if ($errors->any())
                            <div class="w-2/5 bg-red-500 text-white text-sm rounded py-2 px-4 mb-4">
                                <ul class="list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
