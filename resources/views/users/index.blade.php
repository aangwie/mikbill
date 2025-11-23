<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User</title>
    <link rel="icon" href="{{ $global_favicon ?? asset('favicon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">

    @include('layouts.navbar_partial')

    <div class="container pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="fas fa-users-cog text-primary"></i> Manajemen User</h3>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAdd">
                <i class="fas fa-plus"></i> Tambah User
            </button>
        </div>

        @if(session('success')) 
            <div class="alert alert-success border-0 shadow-sm"><i class="fas fa-check-circle me-1"></i>{{ session('success') }}</div> 
        @endif
        @if(session('error')) 
            <div class="alert alert-danger border-0 shadow-sm"><i class="fas fa-exclamation-circle me-1"></i>{{ session('error') }}</div> 
        @endif
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Nama User</th>
                                <th>Email Login</th>
                                <th>Role / Hak Akses</th>
                                <th>Tanggal Dibuat</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td class="ps-4 fw-bold">{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->role == 'admin')
                                        <span class="badge bg-danger">ADMINISTRATOR</span>
                                    @else
                                        <span class="badge bg-success">OPERATOR</span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $user->created_at->format('d M Y') }}</td>
                                <td class="text-end pe-4">
                                    {{-- Tombol Edit --}}
                                    <button class="btn btn-sm btn-warning me-1 btn-edit" 
                                            data-id="{{ $user->id }}"
                                            data-name="{{ $user->name }}"
                                            data-email="{{ $user->email }}"
                                            data-role="{{ $user->role }}"
                                            title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    {{-- Tombol Hapus (Cegah hapus diri sendiri di View) --}}
                                    @if(auth()->user()->id != $user->id)
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus user {{ $user->name }}?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <button class="btn btn-sm btn-secondary" disabled title="Akun Sendiri"><i class="fas fa-trash"></i></button>
                                    @endif
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
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Tambah User Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control" required placeholder="Cth: Operator Budi">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email (Untuk Login)</label>
                            <input type="email" name="email" class="form-control" required placeholder="budi@mikrotik.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required placeholder="******">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role / Hak Akses</label>
                            <select name="role" class="form-select" required>
                                <option value="operator">Operator (Hanya Billing)</option>
                                <option value="admin">Administrator (Full Akses)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEdit" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                {{-- Action form akan diisi via JS --}}
                <form id="formEdit" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="name" id="editName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="editEmail" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password Baru (Opsional)</label>
                            <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diganti">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" id="editRole" class="form-select" required>
                                <option value="operator">Operator</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Handle Tombol Edit
            $('.btn-edit').click(function() {
                // Ambil data dari tombol
                let id = $(this).data('id');
                let name = $(this).data('name');
                let email = $(this).data('email');
                let role = $(this).data('role');

                // Isi Form
                $('#editName').val(name);
                $('#editEmail').val(email);
                $('#editRole').val(role);

                // Set Action URL Form
                $('#formEdit').attr('action', '/users/' + id);

                // Tampilkan Modal
                new bootstrap.Modal(document.getElementById('modalEdit')).show();
            });
        });
    </script>
</body>
</html>