<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Update Sistem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="{{ $global_favicon ?? asset('favicon.ico') }}">
    <style>
        .terminal-window {
            background-color: #1e1e1e;
            color: #00ff00;
            font-family: 'Courier New', Courier, monospace;
            padding: 15px;
            border-radius: 5px;
            height: 300px;
            overflow-y: auto;
            white-space: pre-wrap; /* Agar baris baru terbaca */
            border: 1px solid #333;
        }
    </style>
</head>
<body class="bg-light">

    @include('layouts.navbar_partial')

    <div class="container pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="fas fa-sync-alt text-primary"></i> System Update</h3>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                
                {{-- CARD VERSI --}}
                <div class="card shadow border-0 mb-4">
                    <div class="card-body text-center py-4">
                        <i class="fab fa-github fa-4x text-dark mb-3"></i>
                        <h5>Versi Terinstall Saat Ini:</h5>
                        <code class="fs-5 bg-light px-3 py-1 rounded d-block mb-3 text-primary">
                            {{ $currentVersion ?? 'Tidak diketahui' }}
                        </code>
                        
                        <p class="text-muted small">
                            Sumber: <a href="https://github.com/username_saya/myproject" target="_blank">Repository GitHub</a>
                        </p>

                        <form action="{{ route('system.update') }}" method="POST" onsubmit="return confirm('Yakin ingin melakukan update? Pastikan tidak ada file yang diedit manual di hosting.');">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm">
                                <i class="fas fa-cloud-download-alt me-2"></i> Cek & Update Sekarang
                            </button>
                        </form>
                    </div>
                </div>

                {{-- CARD LOG TERMINAL --}}
                @if(session('log'))
                    <div class="card shadow border-0">
                        <div class="card-header bg-dark text-white">
                            <i class="fas fa-terminal me-2"></i> Update Log
                        </div>
                        <div class="card-body bg-dark p-0">
                            <div class="terminal-window">
                                {{ session('log') }}
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            @if(session('status') == 'success')
                                <div class="text-success fw-bold"><i class="fas fa-check-circle me-1"></i> {{ session('message') }}</div>
                            @elseif(session('status') == 'info')
                                <div class="text-info fw-bold"><i class="fas fa-info-circle me-1"></i> {{ session('message') }}</div>
                            @else
                                <div class="text-danger fw-bold"><i class="fas fa-exclamation-triangle me-1"></i> {{ session('message') }}</div>
                            @endif
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>