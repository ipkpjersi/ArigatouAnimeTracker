<x-app-layout>
    <x-slot name="title">
        {{ config('app.name', 'Laravel') }} - Home
    </x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Home') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <p>Welcome to Arigatou Anime Tracker! Click on the Anime button above to get started.</p>
                    <!-- TODO: maybe add anime statistics, like total number of anime, total users, total hours watched, etc? -->
                </div>
            </div>
        </div>
    </div>
    <script type="module">
        $(document).ready(function () {
        $('#myTable').DataTable({
            responsive: true,
            columnDefs: [
                { responsivePriority: 1, targets: 0 }, //Highest priority to first column
                { responsivePriority: 2, targets: 1 }, //Next priority to second column
            ]
        });
    });
    </script>
</x-app-layout>
