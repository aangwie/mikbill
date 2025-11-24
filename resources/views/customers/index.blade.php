<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pelanggan</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- CSS Libraries --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    {{-- Favicon --}}
    <link rel="icon" href="{{ $global_favicon ?? asset('favicon.ico') }}">
</head>

<body class="bg-light">

    @include('layouts.navbar_partial')

    <div class="container pb-5">

        {{-- Header Page --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="fas fa-users text-primary"></i> Manajemen Pelanggan</h3>
            <div>
                {{-- TOMBOL EXPORT --}}
                <a href="{{ route('customers.export') }}" class="btn btn-success me-1">
                    <i class="fas fa-file-excel"></i> Ekspor
                </a>
                {{-- TOMBOL IMPOR --}}
                <button class="btn btn-danger text-white me-1" data-bs-toggle="modal" data-bs-target="#modalImport">
                    <i class="fas fa-file-upload"></i> Impor
                </button>
                {{-- TOMBOL DOWNLOAD TEMPLATE --}}
                <a href="{{ route('customers.template') }}" class="btn btn-warning me-1">
                    <i class="fas fa-download me-1"></i> Download Template Excel
                </a>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalSync">
                    <i class="fas fa-sync"></i> Sinkron Mikrotik
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAdd">
                    <i class="fas fa-plus"></i> Tambah Baru
                </button>
            </div>
        </div>

        {{-- Alerts --}}
        @if(session('success')) <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div> @endif
        @if(session('error')) <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div> @endif
        @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Tabel Data --}}
        <div class="card shadow border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tableCust" class="table table-hover align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Nomor Internet</th>
                                <th>Nama Pelanggan</th>
                                <th>Operator (PJ)</th>
                                <th>No. HP</th>
                                <th>Harga Paket</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $c)
                            <tr>
                                <td class="fw-bold text-primary">{{ $c->internet_number ?? '-' }}</td>
                                <td>
                                    {{ $c->name }}
                                    <div class="small text-muted" style="font-size: 0.75rem;">User: {{ $c->pppoe_username }}</div>
                                </td>
                                <td>
                                    @if($c->operator)
                                    <span class="badge bg-secondary">{{ $c->operator->name }}</span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($c->phone)
                                    <a href="https://wa.me/{{ $c->phone }}" target="_blank" class="text-decoration-none text-success">
                                        <i class="fab fa-whatsapp"></i> {{ $c->phone }}
                                    </a>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>Rp {{ number_format($c->monthly_price, 0, ',','.') }}</td>
                                <td class="text-end">
                                    {{-- Tombol Edit: Data Attribute Ditambahkan (Address, Lat, Lng) --}}
                                    <button class="btn btn-sm btn-warning btn-edit"
                                        data-id="{{ $c->id }}"
                                        data-internet="{{ $c->internet_number }}"
                                        data-name="{{ $c->name }}"
                                        data-phone="{{ $c->phone }}"
                                        data-price="{{ $c->monthly_price }}"
                                        data-operator="{{ $c->operator_id }}"
                                        data-address="{{ $c->address }}"
                                        data-lat="{{ $c->latitude }}"
                                        data-lng="{{ $c->longitude }}"

                                        {{-- TAMBAHKAN INI --}}
                                        data-profile="{{ $c->profile }}">

                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <form action="{{ route('customers.destroy', $c->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus permanen pelanggan ini? Data di Mikrotik juga akan dihapus.');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
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

    <div class="modal fade" id="modalAdd" tabindex="-1">
        <div class="modal-dialog modal-lg"> {{-- Pakai Modal Large agar muat --}}
            <div class="modal-content">
                <form action="{{ route('customers.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Tambah Pelanggan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">

                        <div class="row">
                            <div class="col-md-6 border-end">
                                <h6 class="text-primary border-bottom pb-2">Data Administratif</h6>
                                <div class="mb-3">
                                    <label class="fw-bold">Nomor Internet</label>
                                    <div class="input-group">
                                        <input type="text" name="internet_number" id="addInetNum" class="form-control" placeholder="Klik generate ->" required maxlength="8">
                                        <button type="button" class="btn btn-secondary" onclick="generateRandomInet()"><i class="fas fa-random"></i></button>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>Nama Pelanggan</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label>No. HP (WA)</label>
                                    <input type="text" name="phone" class="form-control" placeholder="628xxx">
                                </div>
                                <div class="mb-3">
                                    <label>Harga Paket (Rp)</label>
                                    <input type="number" name="monthly_price" class="form-control" value="150000" required>
                                </div>
                                <div class="mb-3">
                                    <label>Operator Penanggung Jawab</label>
                                    <select name="operator_id" class="form-select">
                                        <option value="">-- Admin Langsung --</option>
                                        @if(isset($operators))
                                        @foreach($operators as $op)
                                        <option value="{{ $op->id }}">{{ $op->name }}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h6 class="text-danger border-bottom pb-2">Data Teknis (Mikrotik)</h6>
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label>Username PPPoE</label>
                                        <input type="text" name="pppoe_username" class="form-control" required>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label>Password PPPoE</label>
                                        <input type="text" name="pppoe_password" class="form-control" required>
                                    </div>
                                </div>
                                <div class="mb-3"><label>Profile Mikrotik</label>
                                    <select name="profile" class="form-select">
                                        @if(isset($profiles))
                                        @foreach($profiles as $p)
                                        <option value="{{ $p['name'] }}">{{ $p['name'] }}</option>
                                        @endforeach
                                        @else
                                        <option value="default">Default</option>
                                        @endif
                                    </select>
                                </div>

                                {{-- BAGIAN ALAMAT & MAPS --}}
                                <h6 class="text-success border-bottom pb-2 mt-3">Lokasi & Peta</h6>
                                <div class="mb-3">
                                    <label class="small fw-bold">Alamat Lengkap</label>
                                    <textarea name="address" class="form-control" rows="2" placeholder="Jalan, RT/RW, Desa..."></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label class="small fw-bold">Latitude</label>
                                        <input type="text" name="latitude" class="form-control" placeholder="-7.xxxxx">
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label class="small fw-bold">Longitude</label>
                                        <input type="text" name="longitude" class="form-control" placeholder="110.xxxxx">
                                    </div>
                                </div>
                                <small class="text-muted d-block" style="font-size: 0.75rem;">
                                    <i class="fas fa-info-circle"></i> Ambil koordinat dari Google Maps (Klik kanan di lokasi -> Klik angka koordinat).
                                </small>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan Data</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEdit" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form id="formEdit" method="POST">
                @csrf @method('PUT')
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">Edit Data Pelanggan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">

                        <div class="row">
                            {{-- KIRI: ADMINISTRATIF --}}
                            <div class="col-md-6 border-end">
                                <h6 class="text-primary border-bottom pb-2">Data Utama</h6>
                                <div class="mb-3">
                                    <label class="fw-bold">Nomor Internet</label>
                                    <div class="input-group">
                                        <input type="text" id="editInet" name="internet_number" class="form-control" required maxlength="8">
                                        <button type="button" class="btn btn-secondary" onclick="generateRandomInetEdit()"><i class="fas fa-random"></i></button>
                                    </div>
                                </div>
                                <div class="mb-3"><label>Nama Pelanggan</label><input type="text" id="editName" name="name" class="form-control" required></div>
                                <div class="mb-3"><label>No. HP</label><input type="text" id="editPhone" name="phone" class="form-control"></div>
                                <div class="mb-3"><label>Harga Paket</label><input type="number" id="editPrice" name="monthly_price" class="form-control" required></div>
                                <div class="mb-3">
                                    <label>Operator Penanggung Jawab</label>
                                    <select name="operator_id" id="editOperator" class="form-select">
                                        <option value="">-- Admin Langsung --</option>
                                        @if(isset($operators))
                                        @foreach($operators as $op)
                                        <option value="{{ $op->id }}">{{ $op->name }}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>

                            {{-- KANAN: LOKASI --}}
                            <div class="col-md-6">
                                <h6 class="text-success border-bottom pb-2">Lokasi & Peta</h6>
                                <div class="mb-3">
                                    <label class="small fw-bold">Alamat Lengkap</label>
                                    <textarea name="address" id="editAddress" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label class="small fw-bold">Latitude</label>
                                        <input type="text" name="latitude" id="editLat" class="form-control">
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label class="small fw-bold">Longitude</label>
                                        <input type="text" name="longitude" id="editLng" class="form-control">
                                    </div>
                                </div>

                                <div class="alert alert-secondary text-small mt-3">
                                    <i class="fas fa-lock me-1"></i> Username & Password PPPoE diedit via Winbox.
                                </div>
                            </div>
                            <div class="mt-3 pt-3 border-top">
                                <h6 class="text-danger">Paket Internet (Mikrotik)</h6>
                                <div class="mb-3">
                                    <label class="fw-bold">Ganti Profile</label>
                                    <select name="profile" id="editProfile" class="form-select" required>
                                        @if(isset($profiles))
                                        @foreach($profiles as $p)
                                        <option value="{{ $p['name'] }}">{{ $p['name'] }}</option>
                                        @endforeach
                                        @else
                                        <option value="default">Default</option>
                                        @endif
                                    </select>
                                    <div class="form-text text-muted small">
                                        <i class="fas fa-exclamation-triangle"></i> Perubahan ini akan langsung diterapkan di Router Mikrotik.
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer"><button type="submit" class="btn btn-primary">Update Data</button></div>
            </form>
        </div>
    </div>
    </div>

    <div class="modal fade" id="modalSync" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sinkronisasi Database</h5>
                </div>
                <div class="modal-body text-center">
                    <div id="syncInitial">
                        <i class="fas fa-database fa-4x text-secondary mb-3"></i>
                        <p>Sistem akan mengambil data Secret dari Mikrotik.</p>
                        <button onclick="startSync()" class="btn btn-primary btn-lg w-100">Mulai Sinkronisasi</button>
                    </div>
                    <div id="syncProgress" style="display:none;">
                        <h5 class="mb-2">Memproses Data...</h5>
                        <div class="progress mb-3" style="height: 25px;">
                            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%">0%</div>
                        </div>
                        <p id="syncStatusText" class="text-muted">Menghubungkan ke Mikrotik...</p>
                        <ul id="syncLog" class="list-group text-start" style="height: 200px; overflow-y: auto; font-size: 0.8rem;"></ul>
                    </div>
                    <div id="syncDone" style="display:none;">
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <h4>Selesai!</h4>
                        <button onclick="location.reload()" class="btn btn-success">Tutup & Refresh</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalImport" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('customers.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title"><i class="fas fa-file-excel me-2"></i>Impor Data Pelanggan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning small">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            <b>PENTING:</b><br>
                            1. Gunakan file <b>.xlsx</b> atau <b>.csv</b>.<br>
                            2. Pastikan header kolom sesuai format (nomor_internet, nama_pelanggan, username_pppoe, dll).<br>
                            3. Data yang diimpor <b>hanya masuk ke Database</b>, tidak otomatis masuk ke Mikrotik. Silakan lakukan sinkronisasi manual jika perlu.
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih File Excel</label>
                            <input type="file" name="file" class="form-control" required accept=".xlsx, .xls, .csv">
                        </div>

                        <div class="mt-3">
                            <small class="text-muted">Bingung formatnya? <a href="{{ route('customers.export') }}" class="text-decoration-none">Download template data saat ini</a> sebagai contoh.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info text-white">Upload & Proses</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- JS Libraries --}}
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#tableCust').DataTable();

            // EVENT DELEGATION UNTUK TOMBOL EDIT
            // EVENT DELEGATION UNTUK TOMBOL EDIT
            $('#tableCust tbody').on('click', '.btn-edit', function() {
                let id = $(this).data('id');

                // Isi Form Edit
                $('#editInet').val($(this).data('internet'));
                $('#editName').val($(this).data('name'));
                $('#editPhone').val($(this).data('phone'));
                $('#editPrice').val($(this).data('price'));
                $('#editOperator').val($(this).data('operator'));

                // Isi Data Lokasi
                $('#editAddress').val($(this).data('address'));
                $('#editLat').val($(this).data('lat'));
                $('#editLng').val($(this).data('lng'));

                // ISI PROFILE (BARU)
                let profile = $(this).data('profile');
                $('#editProfile').val(profile); // Select option sesuai data

                $('#formEdit').attr('action', '/customers/' + id);
                new bootstrap.Modal(document.getElementById('modalEdit')).show();
            });
        });

        // GENERATE ANGKA ACAK (MODAL ADD)
        function generateRandomInet() {
            let randomNumber = Math.floor(10000000 + Math.random() * 90000000);
            document.getElementById('addInetNum').value = randomNumber;
        }

        // GENERATE ANGKA ACAK (MODAL EDIT)
        function generateRandomInetEdit() {
            let randomNumber = Math.floor(10000000 + Math.random() * 90000000);
            document.getElementById('editInet').value = randomNumber;
        }

        // LOGIKA SINKRONISASI
        function startSync() {
            $('#syncInitial').hide();
            $('#syncProgress').show();
            $.ajax({
                url: "{{ route('sync.list') }}",
                type: "GET",
                success: function(res) {
                    if (res.status === 'success') processQueue(res.data, res.total, 0);
                    else {
                        alert(res.message);
                        location.reload();
                    }
                },
                error: function() {
                    alert("Koneksi Error");
                }
            });
        }

        function processQueue(secrets, total, index) {
            if (index >= total) {
                $('#progressBar').css('width', '100%').text('100%');
                $('#syncProgress').hide();
                $('#syncDone').show();
                return;
            }
            let item = secrets[index];
            let percent = Math.round(((index + 1) / total) * 100);
            $('#progressBar').css('width', percent + '%').text(percent + '%');
            $('#syncStatusText').text("Memproses: " + item.name);

            $.ajax({
                url: "{{ route('sync.process') }}",
                type: "POST",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    secret: item
                },
                success: function(res) {
                    let badge = res.status === 'created' ? '<span class="badge bg-success">New</span>' : '<span class="badge bg-info">Upd</span>';
                    $('#syncLog').prepend('<li class="list-group-item py-1">' + badge + ' ' + res.name + '</li>');
                    processQueue(secrets, total, index + 1);
                },
                error: function() {
                    processQueue(secrets, total, index + 1);
                }
            });
        }
    </script>
</body>

</html>