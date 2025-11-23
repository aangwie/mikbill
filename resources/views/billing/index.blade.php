<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing & Kasir - Mikrotik App</title>
    <link rel="icon" href="{{ $global_favicon ?? asset('favicon.ico') }}">
    {{-- CSS: Bootstrap & DataTables (Sama seperti Dashboard) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        .status-dot {
            height: 10px;
            width: 10px;
            background-color: #bbb;
            border-radius: 50%;
            display: inline-block;
        }

        .online {
            background-color: #28a745;
            box-shadow: 0 0 5px #28a745;
        }

        .offline {
            background-color: #dc3545;
        }

        /* Merah untuk terisolir */
        .navbar-brand {
            font-weight: bold;
            letter-spacing: 1px;
        }

        .dataTables_wrapper {
            padding: 20px;
        }
    </style>
</head>

<body class="bg-light">

    {{-- 1. Include Navbar --}}
    @include('layouts.navbar_partial')

    <div class="container pb-5">

        {{-- 2. Header Halaman --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3><i class="fas fa-cash-register text-primary"></i> Billing / Kasir</h3>
                <span class="text-muted">Kelola tagihan pelanggan</span>
            </div>
            <div>
                {{-- TOMBOL TAMBAH MANUAL (BARU) --}}
                <button class="btn btn-success shadow-sm me-2" data-bs-toggle="modal" data-bs-target="#modalManual">
                    <i class="fas fa-plus"></i> Manual
                </button>
                {{-- TOMBOL GENERATE (BARU) --}}
                @if(auth()->user()->role == 'admin')
                <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalGenerate">
                    <i class="fas fa-plus-circle"></i> Generate Tagihan
                </button>
                @endif
            </div>
        </div>

        {{-- 3. Alert Notifications --}}
        @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm fade show">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
        @endif

        {{-- 4. Tabel Tagihan (Style Dashboard) --}}
        <div class="card shadow border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-secondary">Daftar Tagihan Belum Lunas</h5>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="tableBilling" class="table table-hover mb-0 align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Status User</th>
                                <th>Pelanggan</th>
                                <th>Jatuh Tempo</th>
                                <th>Nominal Tagihan</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $inv)
                            <tr class="{{ $inv->status == 'paid' ? 'table-success' : '' }}">
                                {{-- Kolom Status (Mirip Dashboard) --}}
                                <td>
                                    @if($inv->customer->is_active)
                                    <span class="status-dot online" title="User Aktif"></span>
                                    <span class="d-none">Aktif</span>
                                    @else
                                    <span class="status-dot offline" title="User Terisolir"></span>
                                    <small class="text-danger fw-bold">Terisolir</small>
                                    @endif
                                </td>

                                {{-- Kolom Pelanggan --}}
                                <td>
                                    <div class="fw-bold">{{ $inv->customer->name }}</div>
                                    <small class="text-muted"><i class="fas fa-user-tag me-1"></i>{{ $inv->customer->pppoe_username }}</small>
                                </td>

                                {{-- Kolom Jatuh Tempo --}}
                                <td>
                                    @if($inv->due_date < now())
                                        <span class="badge bg-danger">Telat {{ \Carbon\Carbon::parse($inv->due_date)->diffForHumans() }}</span>
                                        <div class="small text-muted mt-1">{{ \Carbon\Carbon::parse($inv->due_date)->format('d M Y') }}</div>
                                        @else
                                        {{ \Carbon\Carbon::parse($inv->due_date)->format('d M Y') }}
                                        @endif
                                </td>

                                {{-- Kolom Nominal --}}
                                <td class="fw-bold text-primary fs-6">
                                    Rp {{ number_format($inv->customer->monthly_price, 0, ',', '.') }}
                                </td>

                                {{-- Kolom Aksi --}}
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        {{-- TOMBOL PRINT (BARU) --}}
                                        <a href="{{ route('billing.print', $inv->id) }}" target="_blank" class="btn btn-sm btn-info text-white shadow-sm" title="Cetak Invoice">
                                            <i class="fas fa-print"></i>
                                        </a>

                                        {{-- TOMBOL BAYAR (YANG SUDAH ADA) --}}
                                        @if($inv->status == 'unpaid')
                                        {{-- LOGIKA 1: JIKA BELUM BAYAR -> TOMBOL BAYAR (HIJAU) --}}
                                        <form action="{{ route('billing.pay', $inv->id) }}" method="POST" onsubmit="return confirm('Terima pembayaran Rp {{ number_format($inv->customer->monthly_price) }}? User akan diaktifkan.');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success shadow-sm" title="Bayar & Aktifkan">
                                                <i class="fas fa-money-bill-wave"></i> Bayar
                                            </button>
                                        </form>
                                        @else
                                        {{-- LOGIKA 2: JIKA SUDAH LUNAS -> TOMBOL BATAL (MERAH) --}}
                                        <form action="{{ route('billing.cancel', $inv->id) }}" method="POST" onsubmit="return confirm('BATALKAN status lunas? \n\nPeringatan: User akan kembali terisolir (disable) di Mikrotik!');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger shadow-sm" title="Batalkan Pembayaran (Koreksi)">
                                                <i class="fas fa-undo"></i> Batal
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalGenerate" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('billing.generate') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fas fa-calendar-plus me-2"></i>Buat Tagihan Massal</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info text-small">
                            <i class="fas fa-info-circle"></i> Tagihan akan dibuat untuk <b>semua pelanggan AKTIF</b>.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Periode Bulan</label>
                                <select name="month" class="form-select">
                                    @for ($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ date('n') == $i ? 'selected' : '' }}>
                                        {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                        </option>
                                        @endfor
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tahun</label>
                                <select name="year" class="form-select">
                                    @for ($y = date('Y'); $y <= date('Y')+1; $y++)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-danger">Tanggal Jatuh Tempo (Otomatis Isolir)</label>
                            <input type="date" name="due_date" class="form-control" required
                                value="{{ date('Y-m-20') }}">
                            <div class="form-text">Jika lewat tanggal ini belum bayar, user akan otomatis ter-disable.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Generate Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalManual" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('billing.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Tagihan Manual</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Pilih Pelanggan</label>
                            <select name="customer_id" class="form-select" required>
                                <option value="">-- Pilih Pelanggan --</option>
                                @foreach($customers as $c)
                                <option value="{{ $c->id }}">
                                    {{ $c->name }} ({{ $c->pppoe_username }}) - Rp {{ number_format($c->monthly_price) }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-danger">Jatuh Tempo</label>
                            <input type="date" name="due_date" class="form-control" required
                                value="{{ date('Y-m-d') }}">
                            <div class="form-text">User akan otomatis terisolir jika lewat tanggal ini.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Simpan Tagihan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- 5. Scripts (Sama seperti Dashboard) --}}
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#tableBilling').DataTable({
                "language": {
                    "emptyTable": "Hore! Tidak ada tagihan yang menunggak saat ini.",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ tagihan",
                    "infoEmpty": "Menampilkan 0 sampai 0 dari 0 tagihan",
                    "infoFiltered": "(disaring dari _MAX_ total data)",
                    "search": "Cari Tagihan:",
                    "zeroRecords": "Tidak ditemukan data yang cocok",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Next",
                        "previous": "Prev"
                    }
                }
            });
        });
    </script>
</body>

</html>