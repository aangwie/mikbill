<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mikrotik App</title>
    <link rel="icon" href="{{ $global_favicon ?? asset('favicon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #e9ecef; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card-login { width: 100%; max-width: 400px; border: none; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .card-header { background: #0d6efd; color: white; text-align: center; border-radius: 10px 10px 0 0 !important; padding: 20px; }
    </style>
</head>
<body>

    <div class="card card-login animate__animated animate__fadeInDown">
        <div class="card-header">
            <h4 class="mb-0"><i class="fas fa-lock me-2"></i>Admin Login</h4>
            <small class="opacity-75">Sistem Informasi Billing & Mikrotik</small>
        </div>
        <div class="card-body p-4">
            
            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm text-center">
                    <small>Email atau password salah.</small>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label fw-bold">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control" id="email" placeholder="admin@mikrotik.com" required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label fw-bold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-key"></i></span>
                        <input type="password" name="password" class="form-control" id="password" placeholder="******" required>
                    </div>
                </div>
                
                {{-- TOMBOL LOGIN --}}
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                        Masuk Sistem <i class="fas fa-sign-in-alt ms-1"></i>
                    </button>
                </div>

                {{-- TOMBOL KEMBALI (BARU) --}}
                <div class="text-center mt-3 pt-2 border-top">
                    <a href="{{ route('frontend.index') }}" class="text-decoration-none text-secondary small">
                        <i class="fas fa-arrow-left me-1"></i> Kembali ke Halaman Depan
                    </a>
                </div>
            </form>
        </div>
        <div class="card-footer text-center py-3 bg-white rounded-bottom border-0">
            <small class="text-muted text-xs">&copy; {{ date('Y') }} Mikrotik Billing System | Dev By Aangwi</small>
        </div>
    </div>

</body>
</html>