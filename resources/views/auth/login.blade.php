<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mikrotik App</title>
    
    {{-- Favicon (Ambil dari Global Share) --}}
    <link rel="icon" href="{{ $global_favicon ?? asset('favicon.ico') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { 
            background-color: #e9ecef; 
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            background-image: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        .card-login { 
            width: 100%; 
            max-width: 400px; 
            border: none; 
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
            overflow: hidden;
        }
        .card-header { 
            background: white; 
            color: #333; 
            text-align: center; 
            padding: 30px 20px 10px; 
            border-bottom: none;
        }
        .logo-login {
            max-height: 80px;
            max-width: 80%;
            margin-bottom: 15px;
            object-fit: contain;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #0d6efd;
        }
        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
        }
        .form-control {
            border-left: none;
        }
    </style>
</head>
<body>

    <div class="card card-login animate__animated animate__fadeInDown">
        
        {{-- HEADER DENGAN LOGO --}}
        <div class="card-header">
            {{-- LOGO PERUSAHAAN --}}
            <img src="{{ $global_favicon ?? asset('favicon.ico') }}" alt="Logo Perusahaan" class="logo-login">
            
            <h5 class="mb-1 fw-bold">Admin Login</h5>
            <small class="text-muted">Silakan masuk untuk mengelola sistem</small>
        </div>

        <div class="card-body p-4 pt-2">
            
            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm text-center py-2 mb-3">
                    <small><i class="fas fa-exclamation-circle me-1"></i> Email atau password salah.</small>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label small fw-bold text-secondary">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope text-primary"></i></span>
                        <input type="email" name="email" class="form-control" id="email" placeholder="admin@mikrotik.com" required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label small fw-bold text-secondary">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-key text-primary"></i></span>
                        <input type="password" name="password" class="form-control" id="password" placeholder="******" required>
                    </div>
                </div>
                
                {{-- TOMBOL LOGIN --}}
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg shadow-sm fw-bold">
                        MASUK SISTEM
                    </button>
                </div>

                {{-- TOMBOL KEMBALI --}}
                <div class="text-center mt-4 border-top pt-3">
                    <a href="{{ route('frontend.index') }}" class="text-decoration-none text-muted small">
                        <i class="fas fa-arrow-left me-1"></i> Kembali ke Halaman Depan
                    </a>
                </div>
            </form>
        </div>
        <div class="card-footer text-center py-3 bg-light border-0">
            <small class="text-muted" style="font-size: 0.75rem;">&copy; {{ date('Y') }} Managed Service Provider</small>
        </div>
    </div>

</body>
</html>