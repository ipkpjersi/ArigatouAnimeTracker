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
                    <div class="w-full md:w-3/5 mt-0">
                        <h4 class="font-bold mb-2">Import Anime Export:</h4>
                        <form action="{{ route('import.animelistdata') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="myanimelist_xml" accept=".xml">
                            <button type="submit" class="p-2 bg-blue-500 hover:bg-blue-700 text-white rounded-md mt-2">Upload and Import</button>
                        </form>
                        @if(session()->has('message'))
                            <span>{{ session()->get('message') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
