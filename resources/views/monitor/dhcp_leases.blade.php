@extends('layouts.app2')

@section('title', 'DHCP Leases Monitor')
@section('header', 'DHCP Leases Monitor')
@section('subheader', 'Daftar IP Address yang dipinjamkan oleh DHCP Server.')

@section('content')
    <div
        class="bg-white dark:bg-slate-800 shadow-sm rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div
            class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
            <h3 class="text-lg font-bold text-slate-800 dark:text-white">Active Leases</h3>
            <span
                class="px-3 py-1 bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300 text-xs font-bold rounded-full">
                {{ count($leases) }} Leases
            </span>
        </div>
        <div class="p-4 overflow-x-auto">
            <table id="tableLeases" class="w-full text-left border-collapse">
                <thead>
                    <tr
                        class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-bold border-b border-slate-200 dark:border-slate-700">IP Address</th>
                        <th class="px-6 py-4 font-bold border-b border-slate-200 dark:border-slate-700">MAC Address</th>
                        <th class="px-6 py-4 font-bold border-b border-slate-200 dark:border-slate-700">Host Name</th>
                        <th class="px-6 py-4 font-bold border-b border-slate-200 dark:border-slate-700">Status</th>
                        <th class="px-6 py-4 font-bold border-b border-slate-200 dark:border-slate-700">Expires After</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @foreach($leases as $item)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-slate-900 dark:text-white">
                                {{ $item['address'] ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400 font-mono text-xs">
                                {{ $item['mac-address'] ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">{{ $item['host-name'] ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if(($item['status'] ?? '') == 'bound')
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                        Bound
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800">
                                        {{ $item['status'] ?? 'Unknown' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400 font-mono">
                                {{ $item['expires-after'] ?? '-' }}</td>
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
            $('#tableLeases').DataTable({
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