<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Tagihan Internet</title>
    <link rel="icon" href="{{ $global_favicon ?? asset('favicon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .hero-section {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 80px 0 120px;
            border-radius: 0 0 50px 50px;
            margin-bottom: -60px;
        }
        .card-check {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .status-paid { color: #198754; background-color: #d1e7dd; padding: 5px 15px; border-radius: 20px; font-weight: bold; }
        .status-unpaid { color: #dc3545; background-color: #f8d7da; padding: 5px 15px; border-radius: 20px; font-weight: bold; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="/"><i class="fas fa-wifi me-2"></i>MyISP Portal</a>
            <div class="ms-auto">
                <a href="{{ route('login') }}" class="btn btn-outline-primary px-4">Login Admin</a>
            </div>
        </div>
    </nav>

    <div class="hero-section text-center">
        <div class="container">
            <h1 class="fw-bold display-5">Cek Tagihan Internet</h1>
            <p class="lead opacity-75">Mudah, Cepat, dan Transparan. Masukkan ID pelanggan Anda.</p>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                
                <div class="card card-check bg-white p-4 mb-5">
                    <form action="{{ route('frontend.check') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nomor Internet (ID Pelanggan)</label>
                            <input type="text" name="internet_number" class="form-control form-control-lg" placeholder="Contoh: 82193822" required value="{{ request('internet_number') }}">
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold">Bulan</label>
                                <select name="month" class="form-select form-select-lg">
                                    @for ($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ (request('month') ?? date('n')) == $i ? 'selected' : '' }}>
                                            {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold">Tahun</label>
                                <select name="year" class="form-select form-select-lg">
                                    @for ($y = date('Y'); $y >= 2023; $y--)
                                        <option value="{{ $y }}" {{ (request('year') ?? date('Y')) == $y ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 mt-3 shadow">
                            <i class="fas fa-search me-2"></i> Periksa Tagihan
                        </button>
                    </form>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger text-center shadow-sm border-0 py-3">
                        <i class="fas fa-exclamation-circle fa-2x mb-2 d-block"></i>
                        {{ session('error') }}
                    </div>
                @endif

                @if(isset($invoice) && isset($customer))
                    <div class="card card-check border-0 shadow-lg animate__animated animate__fadeInUp">
                        <div class="card-header bg-white text-center py-3 border-bottom-0">
                            <h5 class="fw-bold mb-0 text-secondary">Hasil Pencarian</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <div class="mb-2 text-muted small">Nomor Internet</div>
                                <h3 class="fw-bold text-primary">{{ $customer->internet_number }}</h3>
                                <div class="badge bg-light text-dark border mt-1">{{ $customer->name }}</div>
                            </div>

                            <div class="row border-top border-bottom py-3 mb-3">
                                <div class="col-6">
                                    <small class="text-muted d-block">Periode Tagihan</small>
                                    <strong>{{ \Carbon\Carbon::parse($invoice->due_date)->format('F Y') }}</strong>
                                </div>
                                <div class="col-6 text-end">
                                    <small class="text-muted d-block">Jatuh Tempo</small>
                                    <strong>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</strong>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <small class="text-muted d-block">Total Tagihan</small>
                                    <span class="fs-4 fw-bold">Rp {{ number_format($customer->monthly_price, 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    @if($invoice->status == 'paid')
                                        <span class="status-paid"><i class="fas fa-check-circle me-1"></i> LUNAS</span>
                                    @else
                                        <span class="status-unpaid"><i class="fas fa-times-circle me-1"></i> BELUM BAYAR</span>
                                    @endif
                                </div>
                            </div>

                            @if($invoice->status == 'paid')
                                <a href="{{ route('frontend.invoice', $invoice->id) }}" target="_blank" class="btn btn-success w-100 py-2 shadow-sm">
                                    <i class="fas fa-file-pdf me-2"></i> Download Invoice / Kuitansi
                                </a>
                            @else
                                <div class="alert alert-warning small mb-0">
                                    <i class="fas fa-info-circle me-1"></i> Silakan lakukan pembayaran agar layanan tidak terisolir. Hubungi Admin via WhatsApp jika sudah transfer.
                                </div>
                            @endif

                        </div>
                    </div>
                @endif

            </div>
        </div>
        
        <div class="text-center text-muted mt-5 mb-5 small">
            &copy; {{ date('Y') }} Sistem Informasi Billing ISP. Dev By Aangwi.
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>