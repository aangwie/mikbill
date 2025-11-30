<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfigurasi Mikrotik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="{{ $global_favicon ?? asset('favicon.ico') }}">
</head>
<body class="bg-light">

    @include('layouts.navbar_partial')

    <div class="container pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="fas fa-server text-primary"></i> Manajemen Router</h3>
        </div>

        @if(session('success')) <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div> @endif
        @if(session('error')) <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div> @endif

        <div class="row">
            {{-- KOLOM KIRI: FORM INPUT --}}
            <div class="col-md-4 mb-4">
                <div class="card shadow border-0">
                    <div class="card-header bg-primary text-white">
                        <h6 class="m-0" id="formTitle"><i class="fas fa-plus-circle me-1"></i> Tambah / Edit Router</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('router.store') }}" method="POST">
                            @csrf
                            {{-- Input Hidden ID untuk Mode Edit --}}
                            <input type="hidden" name="id" id="inputId">

                            <div class="mb-3">
                                <label class="form-label fw-bold">Label / Nama Router</label>
                                <input type="text" name="label" id="inputLabel" class="form-control" placeholder="Cth: Router Utama" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">IP Address (Host)</label>
                                <input type="text" name="host" id="inputHost" class="form-control" placeholder="192.168.88.1" required>
                            </div>

                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-bold">User API</label>
                                    <input type="text" name="username" id="inputUser" class="form-control" placeholder="admin" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-bold">Port API</label>
                                    <input type="number" name="port" id="inputPort" class="form-control" value="8728" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Password</label>
                                <input type="password" name="password" id="inputPass" class="form-control" placeholder="******">
                                <small class="text-muted d-block" style="font-size: 0.75rem;">*Kosongkan jika tidak ingin mengubah password saat edit.</small>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary" id="btnSave">Simpan Konfigurasi</button>
                                <button type="button" class="btn btn-secondary d-none" id="btnCancel" onclick="resetForm()">Batal Edit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: TABEL LIST --}}
            <div class="col-md-8">
                <div class="card shadow border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 fw-bold text-secondary">Daftar Konfigurasi Tersimpan</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Status</th>
                                        <th>Label</th>
                                        <th>Host / IP</th>
                                        <th>User</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($routers as $r)
                                    <tr class="{{ $r->is_active ? 'table-primary' : '' }}">
                                        <td>
                                            @if($r->is_active)
                                                <span class="badge bg-success"><i class="fas fa-check me-1"></i> AKTIF</span>
                                            @else
                                                <span class="badge bg-secondary">Cadangan</span>
                                            @endif
                                        </td>
                                        <td class="fw-bold">{{ $r->label ?? '-' }}</td>
                                        <td>{{ $r->host }}:{{ $r->port }}</td>
                                        <td>{{ $r->username }}</td>
                                        <td class="text-end">
                                            <div class="d-flex gap-1 justify-content-end">
                                                
                                                {{-- AKSI 1: GUNAKAN (Jika belum aktif) --}}
                                                @if(!$r->is_active)
                                                    <form action="{{ route('router.activate', $r->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success" title="Gunakan Router Ini">
                                                            <i class="fas fa-power-off"></i> Gunakan
                                                        </button>
                                                    </form>
                                                @endif

                                                {{-- AKSI 2: EDIT (Isi form diatas pakai JS) --}}
                                                <button class="btn btn-sm btn-warning" 
                                                    onclick="editRouter({{ json_encode($r) }})" 
                                                    title="Edit Data">
                                                    <i class="fas fa-edit"></i>
                                                </button>

                                                {{-- AKSI 3: HAPUS (Hanya jika tidak aktif) --}}
                                                @if(!$r->is_active)
                                                    <form action="{{ route('router.destroy', $r->id) }}" method="POST" onsubmit="return confirm('Hapus konfigurasi ini?')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-sm btn-danger" title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    {{-- Tombol Hapus Disabled untuk yang aktif --}}
                                                    <button class="btn btn-sm btn-secondary" disabled><i class="fas fa-trash"></i></button>
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
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fungsi untuk memasukkan data tabel ke Form Edit
        function editRouter(data) {
            document.getElementById('formTitle').innerHTML = '<i class="fas fa-edit me-1"></i> Edit Router: ' + data.label;
            document.getElementById('inputId').value = data.id;
            document.getElementById('inputLabel').value = data.label;
            document.getElementById('inputHost').value = data.host;
            document.getElementById('inputUser').value = data.username;
            document.getElementById('inputPort').value = data.port;
            document.getElementById('inputPass').value = ''; // Password kosongkan biar aman

            document.getElementById('btnSave').innerText = 'Update Perubahan';
            document.getElementById('btnSave').classList.remove('btn-primary');
            document.getElementById('btnSave').classList.add('btn-warning');
            
            document.getElementById('btnCancel').classList.remove('d-none');
            
            // Scroll ke atas (Form)
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Fungsi Reset Form (Batal Edit)
        function resetForm() {
            document.getElementById('formTitle').innerHTML = '<i class="fas fa-plus-circle me-1"></i> Tambah / Edit Router';
            document.getElementById('inputId').value = '';
            document.getElementById('inputLabel').value = '';
            document.getElementById('inputHost').value = '';
            document.getElementById('inputUser').value = '';
            document.getElementById('inputPort').value = '8728';
            document.getElementById('inputPass').value = '';

            document.getElementById('btnSave').innerText = 'Simpan Konfigurasi';
            document.getElementById('btnSave').classList.remove('btn-warning');
            document.getElementById('btnSave').classList.add('btn-primary');

            document.getElementById('btnCancel').classList.add('d-none');
        }
    </script>
</body>
</html>