<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Keuangan</title>
    <link rel="icon" href="{{ $global_favicon ?? asset('favicon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- CSS DataTables --}}
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="{{ $global_favicon ?? asset('favicon.ico') }}">
    <style>
        .dataTables_wrapper { padding: 15px; } /* Jarak agar search box rapi */
    </style>
</head>
<body class="bg-light">

    @include('layouts.navbar_partial')

    <div class="container pb-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="fas fa-wallet text-primary"></i> Akuntansi & Keuangan</h3>
            
            {{-- FILTER BULAN --}}
            <form action="{{ route('accounting.index') }}" method="GET" class="d-flex gap-2">
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
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Lihat</button>
            </form>
        </div>

        {{-- RINGKASAN KEUANGAN (CARD) --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-success text-white shadow h-100">
                    <div class="card-body">
                        <div class="text-uppercase small fw-bold mb-1">Total Pendapatan (Omset)</div>
                        <h3 class="fw-bold">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
                        <div class="small opacity-75"><i class="fas fa-arrow-up"></i> Dari Tagihan Lunas</div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bg-danger text-white shadow h-100">
                    <div class="card-body">
                        <div class="text-uppercase small fw-bold mb-1">Total Pengeluaran</div>
                        <h3 class="fw-bold">Rp {{ number_format($totalExpense, 0, ',', '.') }}</h3>
                        <div class="small opacity-75"><i class="fas fa-arrow-down"></i> Operasional & Lainnya</div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card {{ $netProfit >= 0 ? 'bg-primary' : 'bg-warning' }} text-white shadow h-100">
                    <div class="card-body">
                        <div class="text-uppercase small fw-bold mb-1">Laba Bersih (Profit)</div>
                        <h3 class="fw-bold">Rp {{ number_format($netProfit, 0, ',', '.') }}</h3>
                        <div class="small opacity-75">
                            @if($netProfit >= 0) <i class="fas fa-smile"></i> Untung @else <i class="fas fa-frown"></i> Rugi @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card shadow border-0">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-secondary">Rincian Pengeluaran Bulan Ini</h6>
                        
                        {{-- TOMBOL BUKA MODAL CETAK --}}
                        <button class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#modalPrint">
                            <i class="fas fa-print me-1"></i> Cetak Laporan
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            {{-- TAMBAHKAN ID DISINI UNTUK DATATABLES --}}
                            <table id="tableExpenses" class="table table-hover mb-0 align-middle w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Keterangan</th>
                                        <th class="text-end">Jumlah</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expenses as $exp)
                                    <tr>
                                        <td>{{ $exp->transaction_date->format('d/m/Y') }}</td>
                                        <td>{{ $exp->description }}</td>
                                        <td class="text-end fw-bold text-danger">Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
                                        <td class="text-end">
                                            <form action="{{ route('accounting.destroy', $exp->id) }}" method="POST" onsubmit="return confirm('Hapus pengeluaran ini?');">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-link text-danger p-0"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow border-0">
                    <div class="card-header bg-danger text-white">
                        <h6 class="m-0"><i class="fas fa-plus-circle me-1"></i> Catat Pengeluaran</h6>
                    </div>
                    <div class="card-body">
                        @if(session('success')) <div class="alert alert-success small mb-3">{{ session('success') }}</div> @endif

                        <form action="{{ route('accounting.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Tanggal</label>
                                <input type="date" name="transaction_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Keterangan / Keperluan</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="Cth: Beli Konektor RJ45, Bayar Listrik Server..." required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nominal (Rp)</label>
                                <input type="number" name="amount" class="form-control" placeholder="0" required>
                            </div>
                            <button type="submit" class="btn btn-danger w-100">Simpan Pengeluaran</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPrint" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('accounting.print') }}" method="GET" target="_blank">
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title"><i class="fas fa-print me-2"></i>Pilih Jenis Laporan</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="month" value="{{ $month }}">
                        <input type="hidden" name="year" value="{{ $year }}">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Format Laporan:</label>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="report_type" value="1" id="type1" checked>
                                <label class="form-check-label" for="type1">1. <b>Cetak Rincian Semua</b></label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="report_type" value="2" id="type2">
                                <label class="form-check-label" for="type2">2. <b>Cetak Rekap Omset & Rincian Pengeluaran</b></label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="report_type" value="3" id="type3">
                                <label class="form-check-label" for="type3">3. <b>Cetak Rincian Omset & Rekap Pengeluaran</b></label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="report_type" value="4" id="type4">
                                <label class="form-check-label" for="type4">4. <b>Cetak Rekap Semua</b></label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-dark"><i class="fas fa-print"></i> Cetak PDF</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Script DataTables --}}
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#tableExpenses').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
                },
                "order": [[ 0, "desc" ]] // Urutkan tanggal terbaru
            });
        });
    </script>
</body>
</html>