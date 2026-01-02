@extends('layouts.app2')

@section('title', 'Static Users Monitor')
@section('header', 'Static Users Monitor')
@section('subheader', 'Daftar semua user hotspot yang terdaftar (Static).')

@section('content')
    <div
        class="bg-white dark:bg-slate-800 shadow-sm rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div
            class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
            <h3 class="text-lg font-bold text-slate-800 dark:text-white">Hotspot Users</h3>
            <span
                class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 text-xs font-bold rounded-full">
                {{ count($users) }} Users
            </span>
        </div>
        <div class="p-4 overflow-x-auto">
            <table id="tableStatic" class="w-full text-left border-collapse">
                <thead>
                    <tr
                        class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-bold border-b border-slate-200 dark:border-slate-700">Username</th>
                        <th class="px-6 py-4 font-bold border-b border-slate-200 dark:border-slate-700">Profile</th>
                        <th class="px-6 py-4 font-bold border-b border-slate-200 dark:border-slate-700">Uptime</th>
                        <th class="px-6 py-4 font-bold border-b border-slate-200 dark:border-slate-700">Bytes In/Out</th>
                        <th class="px-6 py-4 font-bold border-b border-slate-200 dark:border-slate-700">Comment</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @foreach($users as $user)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-slate-900 dark:text-white">
                                <i class="fas fa-user-circle mr-2 text-slate-400"></i>
                                {{ $user['name'] ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                                <span
                                    class="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 text-xs font-medium text-slate-600 dark:text-slate-300">
                                    {{ $user['profile'] ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400 font-mono">
                                {{ $user['uptime'] ?? '0' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400 font-mono text-xs">
                                <span class="text-green-600"><i
                                        class="fas fa-arrow-down mr-1"></i>{{ \App\Helpers\Formatting::formatBytes($user['bytes-in'] ?? 0) }}</span><br>
                                <span class="text-blue-600"><i
                                        class="fas fa-arrow-up mr-1"></i>{{ \App\Helpers\Formatting::formatBytes($user['bytes-out'] ?? 0) }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400 truncate max-w-xs">
                                {{ $user['comment'] ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.tailwindcss.css">
    <style>
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
            margin-top: 1rem;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.tailwindcss.js"></script>
    <script>
        $(document).ready(function () {
            $('#tableStatic').DataTable({
                responsive: true,
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    paginate: {
                        previous: "<",
                        next: ">"
                    }
                }
            });
        });
    </script>
@endpush