<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Gateway</title>
    <link rel="icon" href="{{ $global_favicon ?? asset('favicon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-light">

    @include('layouts.navbar_partial')

    <div class="container pb-5">
        <h3><i class="fab fa-whatsapp text-success"></i> WhatsApp Gateway</h3>
        <hr>

        @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
        @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

        <div class="row">
            <div class="col-md-5">

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">Konfigurasi API</div>
                    <div class="card-body">
                        <form action="{{ route('whatsapp.update') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label>URL API Endpoint</label>
                                <input type="url" name="target_url" class="form-control" value="{{ $setting->target_url ?? '' }}" placeholder="https://api.provider.com/send" required>
                            </div>
                            <div class="mb-3">
                                <label>API Key</label>
                                <input type="text" name="api_key" class="form-control" value="{{ $setting->api_key ?? '' }}" required>
                            </div>
                            <div class="mb-3">
                                <label>Nomor Pengirim (Sender)</label>
                                <input type="text" name="sender_number" class="form-control" value="{{ $setting->sender_number ?? '' }}">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Simpan Konfigurasi</button>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">Test Kirim Pesan</div>
                    <div class="card-body">
                        <form action="{{ route('whatsapp.test') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label>Nomor Tujuan</label>
                                <input type="text" name="target" class="form-control" placeholder="0812xxxxx" required>
                            </div>
                            <div class="mb-3">
                                <label>Pesan</label>
                                <textarea name="message" class="form-control" rows="2" required>Halo, ini pesan test dari Billing System.</textarea>
                            </div>
                            <button type="submit" class="btn btn-warning w-100"><i class="fas fa-paper-plane"></i> Kirim Test</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <i class="fas fa-user-comment me-2"></i>Kirim Pesan Personal
                    </div>
                    <div class="card-body">
                        <form action="{{ route('whatsapp.send.customer') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold">Pilih Pelanggan</label>
                                <select name="customer_id" class="form-select" required>
                                    <option value="">-- Cari Nama Pelanggan --</option>
                                    @foreach($customers as $c)
                                    <option value="{{ $c->id }}">
                                        {{ $c->name }} ({{ $c->phone }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Isi Pesan</label>
                                <div class="form-text mb-1">
                                    Bisa gunakan <b>{name}</b> untuk panggil nama otomatis.
                                </div>
                                <textarea name="message" class="form-control" rows="3" required placeholder="Halo {name}, koneksi internet di rumah aman?"></textarea>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-info text-white">
                                    <i class="fas fa-paper-plane"></i> Kirim Personal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">Broadcast Pesan Massal</div>
                    <div class="card-body">
                        <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
                            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#unpaid">Tagihan (Belum Bayar)</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#all">Semua Pelanggan</button></li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="unpaid">
                                <form action="{{ route('whatsapp.broadcast') }}" method="POST" onsubmit="return confirm('Kirim pengingat ke semua yang belum bayar?');">
                                    @csrf
                                    <input type="hidden" name="type" value="unpaid">
                                    <div class="alert alert-info">
                                        <small>Gunakan <b>{name}</b> untuk nama pelanggan dan <b>{tagihan}</b> untuk nominal.</small>
                                    </div>
                                    <div class="mb-3">
                                        <label>Template Pesan Pengingat</label>
                                        <textarea name="message" class="form-control" rows="5" required>Halo {name},

Kami mengingatkan bahwa tagihan internet Anda sebesar Rp {tagihan} belum terbayar.
Mohon segera lakukan pembayaran untuk menghindari isolir otomatis.

Terima kasih.</textarea>
                                    </div>
                                    <button type="submit" class="btn btn-danger w-100"><i class="fab fa-whatsapp"></i> Kirim ke User Belum Lunas</button>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="all">
                                <form action="{{ route('whatsapp.broadcast') }}" method="POST" onsubmit="return confirm('Kirim pesan ke SEMUA pelanggan?');">
                                    @csrf
                                    <input type="hidden" name="type" value="all">
                                    <div class="mb-3">
                                        <label>Pesan Informasi / Promo</label>
                                        <textarea name="message" class="form-control" rows="5" required>Halo {name},

Akan ada pemeliharaan jaringan pada hari Minggu jam 12:00 - 13:00 WIB.
Mohon maaf atas ketidaknyamanannya.</textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-bullhorn"></i> Kirim Info ke Semua</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>