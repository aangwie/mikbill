@extends('layouts.app2')

@section('title', 'Simple Queues Monitor')
@section('header', 'Simple Queues Monitor')
@section('subheader', 'Monitor limitasi bandwidth user (Queues) secara realtime.')

@section('content')
    <div
        class="bg-white dark:bg-slate-800 shadow-sm rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div
            class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
            <h3 class="text-lg font-bold text-slate-800 dark:text-white">Active Queues</h3>
            <div class="flex items-center gap-4">
                <span class="inline-flex items-center gap-1 text-xs text-slate-500 animate-pulse">
                    <i class="fas fa-circle text-red-500 text-[6px]"></i> Live Update
                </span>
                <span
                    class="px-3 py-1 bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-indigo-300 text-xs font-bold rounded-full">
                    {{ count($queues) }} Queues
                </span>
            </div>
        </div>
        <div class="p-4 overflow-x-auto">
            <table id="tableQueues" class="w-full text-left border-collapse">
                <thead>
                    <tr
                        class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-bold border-b border-slate-200 dark:border-slate-700">Name</th>
                        <th class="px-6 py-4 font-bold border-b border-slate-200 dark:border-slate-700">Target</th>
                        <th class="px-6 py-4 font-bold border-b border-slate-200 dark:border-slate-700 text-red-500">Upload
                            (TX)</th>
                        <th class="px-6 py-4 font-bold border-b border-slate-200 dark:border-slate-700 text-green-500">
                            Download (RX)</th>
                        <th class="px-6 py-4 font-bold border-b border-slate-200 dark:border-slate-700">Total Traffic</th>
                    </tr>
                </thead>
                <tbody id="queueBody" class="divide-y divide-slate-200 dark:divide-slate-700">
                    @foreach($queues as $item)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors" data-id="{{ $item['.id'] }}">
                            <td class="px-6 py-4 text-sm font-medium text-slate-900 dark:text-white">{{ $item['name'] ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400 font-mono text-xs">
                                {{ $item['target'] ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm font-mono text-xs text-red-500 font-bold queue-up">0 bps</td>
                            <td class="px-6 py-4 text-sm font-mono text-xs text-green-500 font-bold queue-down">0 bps</td>
                            <td class="px-6 py-4 text-sm font-mono text-xs text-slate-600 dark:text-slate-300 queue-total">0 bps
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
        function formatBps(bits) {
            bits = parseInt(bits);
            if (isNaN(bits)) return "0 bps";
            if (bits < 1000) return bits + " bps";
            if (bits < 1000000) return (bits / 1000).toFixed(1) + " Kbps";
            if (bits < 1000000000) return (bits / 1000000).toFixed(1) + " Mbps";
            return (bits / 1000000000).toFixed(1) + " Gbps";
        }

        function updateQueues() {
            $.get("{{ route('monitor.simple-queues-json') }}", function (data) {
                data.forEach(function (item) {
                    let row = $(`tr[data-id="${item['.id']}"]`);
                    if (row.length) {
                        let rate = item.rate || "0/0";
                        let parts = rate.split('/');
                        let up = parseInt(parts[0]) || 0;
                        let down = parseInt(parts[1]) || 0;
                        let total = up + down;

                        row.find('.queue-up').text(formatBps(up));
                        row.find('.queue-down').text(formatBps(down));
                        row.find('.queue-total').text(formatBps(total));
                    }
                });
            });
        }

        $(document).ready(function () {
            $('#tableQueues').DataTable({
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

            setInterval(updateQueues, 2000);
            updateQueues(); // initial call
        });
    </script>
@endpush