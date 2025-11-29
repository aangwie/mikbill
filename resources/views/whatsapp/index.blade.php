<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Gateway</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ $global_favicon ?? asset('favicon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            padding-bottom: 5px;
        }
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        /* Style untuk Log Area Broadcast */
        .log-area {
            height: 250px;
            overflow-y: auto;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            font-size: 0.85rem;
            font-family: monospace;
        }
        .log-item { border-bottom: 1px solid #eee; padding: 2px 0; }
        .log-success { color: #198754; }
        .log-error { color: #dc3545; }
    </style>
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

                <div class="card shadow-sm mb-4">
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
                        <i class="fas fa-user-comment me-2"></i>Kirim Pesan Personal (Multi)
                    </div>
                    <div class="card-body">
                        <form action="{{ route('whatsapp.send.customer') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold">Pilih Pelanggan</label>
                                <select name="customer_ids[]" id="multiUserSelect" class="form-select" multiple="multiple" required>
                                    @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Isi Pesan</label>
                                <div class="form-text mb-1">Gunakan <b>{name}</b> untuk panggil nama otomatis.</div>
                                <textarea name="message" class="form-control" rows="3" required placeholder="Halo {name}, koneksi internet aman?"></textarea>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-info text-white"><i class="fas fa-paper-plane me-1"></i> Kirim ke Terpilih</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">Broadcast Pesan Massal</div>
                    <div class="card-body">
                        
                        <ul class="nav nav-tabs mb-3" id="broadcastTab" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" id="unpaid-tab" data-bs-toggle="tab" data-bs-target="#unpaid-content" type="button">Tagihan (Belum Bayar)</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-content" type="button">Semua Pelanggan</button>
                            </li>
                        </ul>

                        <div class="tab-content" id="broadcastTabContent">
                            
                            <div class="tab-pane fade show active" id="unpaid-content">
                                <div class="alert alert-info py-2"><small>Kirim ke pelanggan dengan status Invoice <b>BELUM LUNAS</b>.</small></div>
                                <div class="mb-3">
                                    <label>Template Pesan</label>
                                    <textarea id="msgUnpaid" class="form-control" rows="4">Halo {name}, tagihan internet Anda sebesar Rp {tagihan} belum terbayar. Mohon segera lunasi.</textarea>
                                </div>
                                <button onclick="prepareBroadcast('unpaid')" class="btn btn-danger w-100"><i class="fab fa-whatsapp"></i> Mulai Broadcast (Unpaid)</button>
                            </div>

                            <div class="tab-pane fade" id="all-content">
                                <div class="alert alert-primary py-2"><small>Kirim ke <b>SEMUA</b> pelanggan aktif yang memiliki nomor WA.</small></div>
                                <div class="mb-3">
                                    <label>Isi Pesan Informasi / Promo</label>
                                    <textarea id="msgAll" class="form-control" rows="4">Halo {name}, akan ada maintenance jaringan besok jam 12.00. Mohon maaf atas ketidaknyamanannya.</textarea>
                                </div>
                                <button onclick="prepareBroadcast('all')" class="btn btn-primary w-100"><i class="fas fa-bullhorn"></i> Mulai Broadcast (Semua)</button>
                            </div>
                        </div>

                        <div id="monitorArea" class="mt-4" style="display: none;">
                            <hr>
                            <h6 class="fw-bold"><i class="fas fa-desktop text-success"></i> Status Pengiriman</h6>
                            
                            <div class="progress mb-2" style="height: 20px;">
                                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 0%">0%</div>
                            </div>
                            
                            <div class="d-flex justify-content-between text-muted small mb-2">
                                <span>Total: <b id="statTotal">0</b></span>
                                <span>Sukses: <b id="statSuccess" class="text-success">0</b></span>
                                <span>Gagal: <b id="statFail" class="text-danger">0</b></span>
                            </div>

                            <div class="log-area" id="logList">
                                </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#multiUserSelect').select2({ placeholder: "Cari dan Pilih Pelanggan...", allowClear: true, width: '100%' });
        });

        // --- LOGIKA BROADCAST AJAX ---
        var queue = [];
        var total = 0;
        var current = 0;
        var successCount = 0;
        var failCount = 0;
        var messageToSend = "";

        function prepareBroadcast(type) {
            // Ambil pesan sesuai tab
            if (type === 'unpaid') {
                messageToSend = $('#msgUnpaid').val();
            } else {
                messageToSend = $('#msgAll').val();
            }

            if (!messageToSend.trim()) {
                alert("Isi pesan tidak boleh kosong!");
                return;
            }

            if (!confirm("Yakin ingin memulai broadcast " + type.toUpperCase() + "? Proses ini tidak bisa dibatalkan.")) {
                return;
            }

            // UI Reset
            $('#monitorArea').show();
            $('#logList').html('<div class="text-center text-muted">Mengambil daftar target...</div>');
            $('#progressBar').css('width', '0%').text('0%');
            $('#statTotal').text('0'); $('#statSuccess').text('0'); $('#statFail').text('0');

            // 1. Ambil Daftar Target dari Server
            $.ajax({
                url: "{{ route('whatsapp.broadcast.targets') }}",
                type: "GET",
                data: { type: type },
                success: function(response) {
                    queue = response;
                    total = queue.length;
                    
                    if (total === 0) {
                        $('#logList').html('<div class="text-center text-danger">Tidak ada target pelanggan ditemukan untuk kategori ini.</div>');
                        return;
                    }

                    // Setup UI Start
                    $('#logList').html('');
                    $('#statTotal').text(total);
                    current = 0; successCount = 0; failCount = 0;
                    
                    // Kunci Tombol
                    $('button').prop('disabled', true);
                    
                    // Mulai Proses
                    processQueue();
                },
                error: function() {
                    $('#logList').html('<div class="text-center text-danger">Gagal mengambil data target. Cek koneksi server.</div>');
                }
            });
        }

        function processQueue() {
            if (current >= total) {
                // Selesai
                $('button').prop('disabled', false);
                alert("Broadcast Selesai! Sukses: " + successCount + ", Gagal: " + failCount);
                $('#logList').prepend('<div class="log-item fw-bold text-primary">--- SELESAI ---</div>');
                $('#progressBar').removeClass('progress-bar-animated');
                return;
            }

            let target = queue[current];
            let percent = Math.round(((current + 1) / total) * 100);
            
            // Update Progress Bar
            $('#progressBar').css('width', percent + '%').text(percent + '%');

            // Kirim AJAX Per Item
            $.ajax({
                url: "{{ route('whatsapp.broadcast.process') }}",
                type: "POST",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    id: target.id,
                    message: messageToSend
                },
                success: function(res) {
                    if (res.status) {
                        successCount++;
                        $('#statSuccess').text(successCount);
                        appendLog(target.name, true, 'Terkirim');
                    } else {
                        failCount++;
                        $('#statFail').text(failCount);
                        appendLog(target.name, false, res.message || 'Gagal API');
                    }
                },
                error: function() {
                    failCount++;
                    $('#statFail').text(failCount);
                    appendLog(target.name, false, 'Error Server');
                },
                complete: function() {
                    current++;
                    processQueue(); // Rekursif lanjut ke item berikutnya
                }
            });
        }

        function appendLog(name, status, msg) {
            let colorClass = status ? 'log-success' : 'log-error';
            let icon = status ? 'check' : 'times';
            let html = `<div class="log-item">
                            <i class="fas fa-${icon} ${colorClass} me-1"></i> 
                            <b>${name}</b>: <span class="${colorClass}">${msg}</span>
                        </div>`;
            $('#logList').prepend(html);
        }
    </script>
</body>
</html>