<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring PPPoE Mikrotik</title>
    <link rel="icon" href="{{ $global_favicon ?? asset('favicon.ico') }}">
    {{-- 1. CSS Bootstrap & DataTables --}}
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

        .navbar-brand {
            font-weight: bold;
            letter-spacing: 1px;
        }

        /* Sedikit perbaikan padding untuk datatables */
        .dataTables_wrapper {
            padding: 20px;
        }
    </style>
</head>

<body class="bg-light">

    {{-- Navbar --}}
    @include('layouts.navbar_partial')

    <div class="container pb-5">

        {{-- Info Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="fas fa-network-wired text-primary"></i> Monitor User Online</h3>
            <div>
                <span class="badge bg-secondary">Host: {{ env('MIKROTIK_HOST') }}</span>
                <span class="badge bg-success">Connected</span>
            </div>
        </div>

        {{-- Alerts --}}
        @if(isset($error) && $error)
        <div class="alert alert-danger border-0 shadow-sm">{{ $error }}</div>
        @endif
        @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
        @endif
        @if(session('warning'))
        <div class="alert alert-warning border-0 shadow-sm">{{ session('warning') }}</div>
        @endif

        {{-- Tabel DataTables --}}
        @if(isset($secrets))
        <div class="card shadow border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-secondary">Daftar Pelanggan ({{ count($secrets) }})</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    {{-- Tambahkan ID 'tableUser' agar bisa dipanggil Javascript --}}
                    <table id="tableUser" class="table table-hover mb-0 align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Status</th>
                                <th>Username</th>
                                <th>Password</th>
                                <th>Profile</th>
                                <th>IP Address</th>
                                <th>Uptime</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($secrets as $secret)
                            @php
                            $name = $secret['name'];
                            $isActive = $actives->has($name);
                            $activeData = $isActive ? $actives[$name] : null;
                            $isDisabled = isset($secret['disabled']) && $secret['disabled'] == 'true';
                            @endphp
                            <tr class="{{ $isDisabled ? 'table-secondary text-muted' : '' }}">
                                <td>
                                    @if($isDisabled)
                                    <span class="badge bg-secondary">Disabled</span>
                                    @elseif($isActive)
                                    <span class="status-dot online"></span> <span class="d-none">Online</span> {{-- d-none text untuk sorting --}}
                                    @else
                                    <span class="status-dot offline"></span> <span class="d-none">Offline</span>
                                    @endif
                                </td>
                                <td class="fw-bold">
                                    {{ $secret['name'] }}
                                    @if($isDisabled) <i class="fas fa-ban text-danger ms-1"></i> @endif
                                </td>
                                <td>
                                    @php
                                    $rawPass = $secret['password'] ?? '';
                                    // Logika: Jika panjang > 3, ambil 3 huruf awal + bintang. Jika pendek, tampilkan saja.
                                    $maskedPass = strlen($rawPass) > 3 ? substr($rawPass, 0, 3) . '******' : $rawPass;
                                    @endphp

                                    <code class="text-dark" title="Password disembunyikan demi keamanan">
                                        {{ $maskedPass }}
                                    </code>
                                </td>
                                <td><span class="badge bg-info text-dark">{{ $secret['profile'] ?? 'default' }}</span></td>
                                <td>{{ $activeData['address'] ?? '-' }}</td>
                                <td>{{ $activeData['uptime'] ?? '-' }}</td>
                                <td class="text-end">
                                    <div class="d-flex gap-1 justify-content-end">
                                        {{-- TOMBOL 1: KICK Manual (Hanya muncul jika user sedang Online) --}}
                                        @if($isActive)
                                        <form action="{{ route('pppoe.kick') }}" method="POST" onsubmit="return confirm('Hanya kick koneksi (reset) user {{ $name }}?');">
                                            @csrf
                                            <input type="hidden" name="username" value="{{ $name }}">
                                            <button type="submit" class="btn btn-sm btn-warning" title="Reset Koneksi">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </form>
                                        @endif

                                        {{-- TOMBOL 2: ENABLE / DISABLE --}}
                                        <form action="{{ route('pppoe.toggle') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="username" value="{{ $name }}">

                                            @if($isDisabled)
                                            {{-- Jika user sedang DISABLE, tampilkan tombol ENABLE --}}
                                            <input type="hidden" name="action" value="enable">
                                            <button type="submit" class="btn btn-sm btn-success" title="Aktifkan User">
                                                <i class="fas fa-check"></i> Enable
                                            </button>
                                            @else
                                            {{-- Jika user sedang ENABLE, tampilkan tombol DISABLE --}}
                                            <input type="hidden" name="action" value="disable">
                                            {{-- Tambahkan confirm agar tidak kepencet --}}
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin memblokir {{ $name }}? Koneksi aktifnya akan langsung diputus!');" title="Blokir & Kick">
                                                <i class="fas fa-ban"></i> Disable
                                            </button>
                                            @endif
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- 2. Script Javascript (jQuery + DataTables) --}}
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    {{-- 3. Inisialisasi DataTables dengan Bahasa Indonesia --}}
    <script>
        $(document).ready(function() {
            $('#tableUser').DataTable({
                "language": {
                    "emptyTable": "Tidak ada data yang tersedia di tabel",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    "infoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
                    "infoFiltered": "(disaring dari _MAX_ total entri)",
                    "lengthMenu": "Tampilkan _MENU_ entri",
                    "loadingRecords": "Sedang memuat...",
                    "processing": "Sedang memproses...",
                    "search": "Cari:",
                    "zeroRecords": "Tidak ditemukan data yang cocok",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    }
                }
            });
        });
    </script>
</body>

</html>