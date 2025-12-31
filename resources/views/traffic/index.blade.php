@extends('layouts.app2')

@section('title', 'Traffic Monitor')
@section('header', 'Traffic Monitor')
@section('subheader', 'Real-time interface traffic monitoring')

@section('content')

    <div class="space-y-6">
        <!-- Controls -->
        <div
            class="bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <div class="bg-primary-50 text-primary-600 p-2 rounded-lg">
                    <i class="fas fa-network-wired"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white">Interface</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Select interface to monitor</p>
                </div>
            </div>

            <div class="flex items-center gap-3 flex-1 justify-end">
                <div class="w-full sm:w-64">
                    <select id="interfaceSelect"
                        class="block w-full rounded-md border-0 py-1.5 text-slate-900 dark:text-white dark:bg-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
                        @foreach($interfaces as $iface)
                            <option value="{{ $iface['name'] }}" {{ $iface['name'] == 'ether1' ? 'selected' : '' }}>
                                {{ $iface['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button onclick="resetChart()"
                    class="inline-flex items-center rounded-lg bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 px-3 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 transition-all">
                    <i class="fas fa-sync-alt mr-2 text-slate-400"></i> Reset
                </button>
            </div>
        </div>

        <!-- Stats & Chart -->
        <div
            class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50 px-6 py-4">
                <div class="grid grid-cols-2 gap-8 text-center divide-x divide-slate-200">
                    <div>
                        <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">
                            Download (RX)</p>
                        <h2 class="text-3xl font-extrabold text-green-600 dark:text-green-500 tracking-tight" id="rxLabel">0
                            Mbps</h2>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Upload
                            (TX)</p>
                        <h2 class="text-3xl font-extrabold text-blue-600 dark:text-blue-500 tracking-tight" id="txLabel">0
                            Mbps</h2>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="relative h-[400px] w-full">
                    <canvas id="trafficChart"></canvas>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container .select2-selection--single {
            height: 38px;
            border-color: #d1d5db;
            border-radius: 0.375rem;
            padding-top: 5px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            top: 6px;
        }

        /* Dark Mode Select2 */
        .dark .select2-container .select2-selection--single {
            background-color: #1e293b;
            /* slate-800 */
            border-color: #475569;
            /* slate-600 */
            color: #fff;
        }

        .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #fff;
        }

        .dark .select2-dropdown {
            background-color: #1e293b;
            /* slate-800 */
            border-color: #475569;
            /* slate-600 */
            color: #fff;
        }

        .dark .select2-search__field {
            background-color: #0f172a;
            /* slate-900 */
            color: #fff;
            border-color: #475569;
        }

        .dark .select2-results__option--highlighted[aria-selected] {
            background-color: #2563eb;
            /* primary-600 */
        }

        .dark .select2-results__option[aria-selected=true] {
            background-color: #334155;
            /* slate-700 */
        }
    </style>
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        var chart;
        var intervalId;

        $(document).ready(function () {
            $('#interfaceSelect').select2({ placeholder: 'Cari Interface...', width: '100%' });

            initChart();
            startMonitoring();

            $('#interfaceSelect').on('change', function () {
                resetChart();
            });
        });

        function initChart() {
            var ctx = document.getElementById('trafficChart').getContext('2d');
            chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'RX (Download)',
                            borderColor: '#059669', // emerald-600
                            backgroundColor: 'rgba(5, 150, 105, 0.1)',
                            borderWidth: 2,
                            pointRadius: 0,
                            data: [],
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'TX (Upload)',
                            borderColor: '#2563eb', // blue-600
                            backgroundColor: 'rgba(37, 99, 235, 0.1)',
                            borderWidth: 2,
                            pointRadius: 0,
                            data: [],
                            fill: true,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { intersect: false, mode: 'index' },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { borderDash: [2, 4], color: '#f1f5f9' },
                            ticks: { callback: function (value) { return value + ' Mbps'; } }
                        },
                        x: { display: false }
                    },
                    plugins: {
                        legend: { position: 'top', align: 'end', labels: { usePointStyle: true, boxWidth: 6 } },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            padding: 12,
                            titleFont: { size: 13 },
                            bodyFont: { size: 12 },
                            cornerRadius: 8,
                            displayColors: true
                        }
                    }
                }
            });
        }

        function startMonitoring() {
            if (intervalId) clearInterval(intervalId);

            intervalId = setInterval(function () {
                var iface = $('#interfaceSelect').val();
                $.ajax({
                    url: "{{ route('traffic.data') }}",
                    type: "POST",
                    data: { _token: $('meta[name="csrf-token"]').attr('content'), interface: iface },
                    success: function (response) {
                        if (response.status === 'success') {
                            updateChart(response.rx, response.tx);
                        }
                    },
                    error: function () { console.log("Failed to fetch traffic data"); }
                });
            }, 2000);
        }

        function updateChart(rxBits, txBits) {
            var rxMbps = (rxBits / 1000000).toFixed(2);
            var txMbps = (txBits / 1000000).toFixed(2);

            $('#rxLabel').text(rxMbps + ' Mbps');
            $('#txLabel').text(txMbps + ' Mbps');

            var now = new Date().toLocaleTimeString();
            chart.data.labels.push(now);
            chart.data.datasets[0].data.push(rxMbps);
            chart.data.datasets[1].data.push(txMbps);

            if (chart.data.labels.length > 20) {
                chart.data.labels.shift();
                chart.data.datasets[0].data.shift();
                chart.data.datasets[1].data.shift();
            }
            chart.update('none'); // 'none' for performance
        }

        function resetChart() {
            chart.data.labels = [];
            chart.data.datasets[0].data = [];
            chart.data.datasets[1].data = [];
            chart.update();
            startMonitoring();
        }
    </script>
@endpush