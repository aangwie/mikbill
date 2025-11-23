<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfigurasi Mikrotik</title>
    <link rel="icon" href="{{ $global_favicon ?? asset('favicon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">

    @include('layouts.navbar_partial')

    <div class="container pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="fas fa-server text-primary"></i> Konfigurasi Router</h3>
        </div>

        @if(session('success')) 
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div> 
        @endif

        <div class="card shadow border-0">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary">Koneksi API Mikrotik</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('router.update') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">URL / IP Address (Host)</label>
                        <input type="text" name="host" class="form-control" value="{{ $setting->host ?? '' }}" placeholder="192.168.88.1" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Username API</label>
                            <input type="text" name="username" class="form-control" value="{{ $setting->username ?? 'admin' }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Port API</label>
                            <input type="number" name="port" class="form-control" value="{{ $setting->port ?? '8728' }}" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Password API</label>
                        <input type="password" name="password" class="form-control" value="{{ $setting->password ?? '' }}" placeholder="Masukkan password router...">
                    </div>

                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle me-1"></i> 
                        Pastikan Service API di Mikrotik sudah aktif (IP -> Services -> api).
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary px-4">Simpan Konfigurasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>