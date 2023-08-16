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
                                @if(auth()->user()->isModerator())
                                    <th>Actions</th>
                                @endif
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
            let columns = [
                { data: 'id', name: 'id' },
                { data: 'avatar', name: 'avatar', render: function(data, type, row) {
                    return '<img src="' + data + '" alt="Avatar" style="width:50px; max-height: 70px" onerror="this.onerror=null; this.src=\'/img/notfound.gif\';" />';
                }},
                { data: 'username', name: 'username', render: function(data, type, row) {
                    return `<a href="/users/${data}">${data}</a>`;
                }},
                { data: 'is_admin', name: 'is_admin', render: function(data, type, row) {
                    return data === 1 ? "Yes" : "No";
                }},
                { data: 'created_at', name: 'created_at' },
            ];

            @if(auth()->user()->isModerator())
                columns.push({
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    console.log(row);
                    let actions = '';
                    if(row.is_admin != 1) {
                        if(row.is_banned == 1) {
                            actions += `<button data-user-id="${data.id}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded unbanUser">Unban</button>`;
                        } else {
                            actions += `<button data-user-id="${data.id}" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded banUser">Ban</button>`;
                        }
                        actions += `<button data-user-id="${data.id}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded ml-2 removeAvatar">Remove Avatar</button>`;
                    }
                    return actions;
                }
            });
            @endif

            let dataTable = $('#userTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('users.data') }}',
                order: [4, 'desc'],
                columns: columns,
                search: { search: initialSearch },
                responsive: true,
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
            $(document).on('click', '.banUser', function() {
                let userId = $(this).data('user-id');
                axios.post(`/users/${userId}/ban`, {
                    _token: '{{ csrf_token() }}'
                })
                .then(function(response) {
                    alert(response.data.message);
                    dataTable.ajax.reload(); // Refresh the table
                })
                .catch(function(error) {
                    alert('Error banning user: ' + error);
                });
            });

            $(document).on('click', '.removeAvatar', function() {
                let userId = $(this).data('user-id');
                axios.post(`/users/${userId}/remove-avatar`, {
                    _token: '{{ csrf_token() }}'
                })
                .then(function(response) {
                    alert(response.data.message);
                    dataTable.ajax.reload(); // Refresh the table
                })
                .catch(function(error) {
                    alert('Error removing avatar: ' + error);
                });
            });

            $(document).on('click', '.unbanUser', function() {
                let userId = $(this).data('user-id');
                axios.post(`/users/${userId}/unban`, {
                    _token: '{{ csrf_token() }}'
                })
                .then(function(response) {
                    alert(response.data.message);
                    dataTable.ajax.reload(); // Refresh the table
                })
                .catch(function(error) {
                    alert('Error unbanning user: ' + error);
                });
            });
        });
    </script>
    <style>
        #yadcf-filter--userTable-3 {
            max-width: 138px;
            padding-right: 1rem;
        }
    </style>
</x-app-layout>
