<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan</title>
    <link rel="icon" href="{{ $global_favicon ?? asset('favicon.ico') }}">
    {{-- CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .card-summary { border: none; border-radius: 10px; color: white; }
        .bg-gradient-primary { background: linear-gradient(45deg, #4e73df, #224abe); }
        .bg-gradient-success { background: linear-gradient(45deg, #1cc88a, #13855c); }
        .bg-gradient-danger { background: linear-gradient(45deg, #e74a3b, #be2617); }
    </style>
</head>
<body class="bg-light">

    {{-- Include Navbar --}}
    @include('layouts.navbar_partial') 

    <div class="container py-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="fas fa-chart-line text-primary"></i> Laporan Pembayaran</h3>
            
            {{-- FORM FILTER --}}
            <form action="{{ route('report.index') }}" method="GET" class="d-flex gap-2">
                <select name="month" class="form-select">
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                            {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                        </option>
                    @endfor
                </select>
                <select name="year" class="form-select">
                    @for ($y = date('Y'); $y >= 2023; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            </form>
        </div>

        {{-- KARTU REKAPITULASI --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card card-summary bg-gradient-primary p-3 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Total Tagihan (Omset)</h6>
                            <h3 class="fw-bold mb-0">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</h3>
                            <small>{{ count($invoices) }} Pelanggan</small>
                        </div>
                        <i class="fas fa-file-invoice-dollar fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-summary bg-gradient-success p-3 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Uang Masuk (Lunas)</h6>
                            <h3 class="fw-bold mb-0">Rp {{ number_format($totalLunas, 0, ',', '.') }}</h3>
                            <small>{{ $jumlahLunas }} Pelanggan</small>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-summary bg-gradient-danger p-3 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Piutang (Belum Lunas)</h6>
                            <h3 class="fw-bold mb-0">Rp {{ number_format($totalBelumLunas, 0, ',', '.') }}</h3>
                            <small>{{ $jumlahBelumLunas }} Pelanggan</small>
                        </div>
                        <i class="fas fa-times-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABEL DATA --}}
        <div class="card shadow border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-secondary">Detail Transaksi</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tableReport" class="table table-hover w-100 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Pelanggan</th>
                                <th>Akun PPPoE</th>
                                <th>Jatuh Tempo</th>
                                <th>Nominal</th>
                                <th>Status</th>
                                {{-- KOLOM BARU --}}
                                <th class="text-center">Cetak Invoice</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $inv)
                            <tr>
                                <td>{{ $inv->customer->name }}</td>
                                <td>{{ $inv->customer->pppoe_username }}</td>
                                <td>{{ \Carbon\Carbon::parse($inv->due_date)->format('d M Y') }}</td>
                                <td class="fw-bold">Rp {{ number_format($inv->customer->monthly_price, 0, ',', '.') }}</td>
                                <td>
                                    @if($inv->status == 'paid')
                                        <span class="badge bg-success"><i class="fas fa-check"></i> Lunas</span>
                                    @else
                                        <span class="badge bg-danger"><i class="fas fa-clock"></i> Belum Bayar</span>
                                    @endif
                                </td>
                                {{-- TOMBOL CETAK --}}
                                <td class="text-center">
                                    <a href="{{ route('billing.print', $inv->id) }}" target="_blank" class="btn btn-sm btn-info text-white" title="Cetak / Lihat Invoice">
                                        <i class="fas fa-print"></i> Cetak
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- Script --}}
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#tableReport').DataTable({
                "language": { 
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json" 
                },
                "order": [[ 2, "desc" ]] // Urutkan berdasarkan Jatuh Tempo (Terbaru diatas)
            });
        });
    </script>
</body>
</html>