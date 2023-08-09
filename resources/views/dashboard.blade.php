<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Home') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in!") }}
                </div>
                <div class="p-4">
                    <p>Datatables example: </p>
                    <table style="width:100%" id="myTable" class="display text-gray-900 dark:text-gray-100">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Age</th>
                                <th>Position</th>
                                <th>Salary</th>
                                <th>Tenure</th>
                                <th>Stock Options</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Tiger Nixon</td>
                                <td>61</td>
                                <td>System Architect</td>
                                <td>80,000</td>
                                <td>12 years</td>
                                <td>Yes</td>
                            </tr>
                            <tr>
                                <td>Garrett Winters</td>
                                <td>63</td>
                                <td>Accountant</td>
                                <td>80,000</td>
                                <td>15 years</td>
                                <td>Yes</td>
                            </tr>
                        </tbody>
                    </table>
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
