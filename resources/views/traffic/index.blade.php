<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Monitoring Traffic Interface</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    {{-- 1. TAMBAHKAN CSS SELECT2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <link rel="icon" href="{{ $global_favicon ?? asset('favicon.ico') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        /* Sedikit perbaikan agar Select2 terlihat serasi dengan Bootstrap 5 */
        .select2-container .select2-selection--single {
            height: 38px;
            border: 1px solid #dee2e6;
            padding-top: 5px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
    </style>
</head>
<body class="bg-light">

    @include('layouts.navbar_partial')

    <div class="container pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="fas fa-chart-area text-primary"></i> Traffic Monitor</h3>
        </div>

        <div class="card shadow border-0">
            <div class="card-header bg-white py-3">
                <div class="row align-items-center">
                    <div class="col-md-5">
                        <h6 class="m-0 font-weight-bold text-secondary">Realtime Interface Traffic</h6>
                    </div>
                    <div class="col-md-7">
                        <div class="d-flex gap-2 justify-content-end">
                            <div style="min-width: 250px; flex-grow: 1;">
                                {{-- Select ini akan berubah jadi Searchable --}}
                                <select id="interfaceSelect" class="form-select" style="width: 100%;">
                                    @foreach($interfaces as $iface)
                                        <option value="{{ $iface['name'] }}" {{ $iface['name'] == 'ether1' ? 'selected' : '' }}>
                                            {{ $iface['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button class="btn btn-primary" onclick="resetChart()">
                                <i class="fas fa-sync"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row text-center mb-4">
                    <div class="col-6 border-end">
                        <h5 class="text-muted small text-uppercase">RX (Download)</h5>
                        <h2 class="fw-bold text-success" id="rxLabel">0 Mbps</h2>
                    </div>
                    <div class="col-6">
                        <h5 class="text-muted small text-uppercase">TX (Upload)</h5>
                        <h2 class="fw-bold text-primary" id="txLabel">0 Mbps</h2>
                    </div>
                </div>

                {{-- CANVAS CHART.JS --}}
                <div style="height: 400px; width: 100%;">
                    <canvas id="trafficChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- 2. TAMBAHKAN JS SELECT2 & CHART.JS --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        var chart;
        var intervalId;

        $(document).ready(function() {
            // 3. INISIALISASI SELECT2 (Agar bisa search)
            $('#interfaceSelect').select2({
                placeholder: 'Cari Interface...',
                width: 'resolve' // Mengikuti lebar style parent
            });

            initChart();
            startMonitoring();

            // Event saat user memilih interface (Select2 menggunakan event 'select2:select' atau 'change')
            $('#interfaceSelect').on('change', function() {
                resetChart();
            });
        });

        // 1. Inisialisasi Chart Kosong
        function initChart() {
            var ctx = document.getElementById('trafficChart').getContext('2d');
            
            chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [], 
                    datasets: [
                        {
                            label: 'RX (Download)',
                            borderColor: '#198754', 
                            backgroundColor: 'rgba(25, 135, 84, 0.1)',
                            borderWidth: 2,
                            data: [],
                            fill: true,
                            tension: 0.4 
                        },
                        {
                            label: 'TX (Upload)',
                            borderColor: '#0d6efd',
                            backgroundColor: 'rgba(13, 110, 253, 0.1)',
                            borderWidth: 2,
                            data: [],
                            fill: true,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Speed (Mbps)' }
                        },
                        x: { display: false }
                    },
                    plugins: {
                        legend: { position: 'top' }
                    }
                }
            });
        }

        // 2. Fungsi Ambil Data ke Laravel
        function startMonitoring() {
            if (intervalId) clearInterval(intervalId);

            intervalId = setInterval(function() {
                var iface = $('#interfaceSelect').val();

                $.ajax({
                    url: "{{ route('traffic.data') }}",
                    type: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        interface: iface
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            updateChart(response.rx, response.tx);
                        }
                    },
                    error: function() {
                        console.log("Gagal mengambil data traffic");
                    }
                });
            }, 2000); 
        }

        // 3. Update Chart
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

            chart.update();
        }

        // 4. Reset Chart
        function resetChart() {
            chart.data.labels = [];
            chart.data.datasets[0].data = [];
            chart.data.datasets[1].data = [];
            chart.update();
            startMonitoring();
        }
    </script>
</body>
</html>