@extends('layouts.app2')

@section('title', 'Hotspot Monitor')
@section('header', 'Hotspot Monitor')
@section('subheader', 'Daftar user hotspot yang sedang aktif.')

@section('content')
    <div
        class="bg-white dark:bg-slate-800 shadow-sm rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div
            class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
            <h3 class="text-lg font-bold text-slate-800 dark:text-white">Active Users</h3>
            <span
                class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 text-xs font-bold rounded-full">
                {{ count($activeUsers) }} Online
            </span>
        </div>
        <div class="p-4 overflow-x-auto">
            <table id="tableHotspot" class="w-full text-left border-collapse">
                <thead>
                    <tr
                        class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-bold border-b border-slate-200 dark:border-slate-700">User</th>
                        <th class="px-6 py-4 font-bold border-b border-slate-200 dark:border-slate-700">Address</th>
                        <th class="px-6 py-4 font-bold border-b border-slate-200 dark:border-slate-700">Uptime</th>
                        <th class="px-6 py-4 font-bold border-b border-slate-200 dark:border-slate-700">Keepalive</th>
                        <th class="px-6 py-4 font-bold border-b border-slate-200 dark:border-slate-700 text-right">Limit
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($activeUsers as $user)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-slate-900 dark:text-white">
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></div>
                                    {{ $user['user'] ?? '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">{{ $user['address'] ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400 font-mono">
                                {{ $user['uptime'] ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400 font-mono">
                                {{ $user['keepalive-timeout'] ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400 text-right">
                                {{ $user['limit-bytes-total'] ?? 'No Limit' }}
                            </td>
                        </tr>
                    @empty
                        <!-- Empty state handled by DataTable or by loop if no data -->
                    @endforelse
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
            $('#tableHotspot').DataTable({
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