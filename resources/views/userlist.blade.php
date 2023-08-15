<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('User List') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <table style="width:100%" id="userTable" class="display text-gray-900 dark:text-gray-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Avatar</th>
                                <th>Username</th>
                                <th>Admin</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script type="module">
        import '/js/jquery.dataTables.yadcf.js';
        $(document).ready(function () {
            let initialSearch = new URLSearchParams(window.location.search).get('search') || "";
            let dataTable = $('#userTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('users.data') }}',
                order: [4, 'desc'],
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'avatar', name: 'avatar', render: function(data, type, row) {
                        return '<img src="' + data + '" alt="Avatar" style="width:50px; max-height: 70px"  onerror="this.onerror=null; this.src=\'/img/notfound.gif\';" />';
                    }},
                    { data: 'username', name: 'username', render: function(data, type, row) {
                        return `<a href="/users/${data}">${data}</a>`;
                    }},
                    { data: 'is_admin', name: 'is_admin', render: function(data, type, row) {
                        return data === 1 ? "Yes" : "No";
                    } },
                    { data: 'created_at', name: 'created_at' },
                ],
                search: { search: initialSearch },
            });
            yadcf.init(dataTable, [
                {
                    column_number: 2,
                    filter_type: "text"
                },
                {
                    column_number: 3,
                    filter_type: "select",
                    data: [
                        { value: "1", label: "Yes" },
                        { value: "0", label: "No" },
                    ],
                    filter_default_label: "All Users"
                },
            ]);
        });
    </script>
    <style>
        #yadcf-filter--userTable-3 {
            max-width: 138px;
            padding-right: 1rem;
        }
    </style>
</x-app-layout>
