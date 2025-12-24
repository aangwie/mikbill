<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Gateway</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ $global_favicon ?? asset('favicon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }

        .main-container {
            background: #f8fafc;
            min-height: calc(100vh - 56px);
            padding: 20px;
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            height: 100%;
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid #eee;
            font-weight: 600;
            padding: 12px 16px;
            font-size: 0.9rem;
        }

        .card-body {
            padding: 16px;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            font-size: 0.875rem;
        }

        .btn {
            border-radius: 10px;
            font-weight: 500;
            font-size: 0.875rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }

        .btn-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border: none;
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
            border: none;
        }

        .btn-info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border: none;
            color: white;
        }

        .select2-container .select2-selection--multiple {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            min-height: 38px;
        }

        .log-area {
            height: 200px;
            overflow-y: auto;
            background: #1a1a2e;
            border-radius: 10px;
            padding: 12px;
            font-size: 0.8rem;
            font-family: 'Courier New', monospace;
            color: #eee;
        }

        .log-item {
            padding: 3px 0;
            border-bottom: 1px solid #333;
        }

        .log-success {
            color: #38ef7d;
        }

        .log-error {
            color: #f5576c;
        }

        .nav-pills .nav-link {
            border-radius: 10px;
            font-size: 0.8rem;
            padding: 8px 14px;
            color: #666;
        }

        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .status-box {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }

        .status-connected {
            background: linear-gradient(135deg, #d4fc79 0%, #96e6a1 100%);
        }

        .status-offline {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
        }

        .form-control-sm,
        .btn-sm {
            font-size: 0.8rem;
        }

        label {
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 4px;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.02);
            }
        }

        .pulse {
            animation: pulse 2s infinite;
        }
    </style>
</head>

<body>
    @include('layouts.navbar_partial')

    <div class="main-container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold mb-0"><i class="fab fa-whatsapp text-success me-2"></i>WhatsApp Gateway</h4>
                <small class="text-muted">Manage connections and broadcast messages</small>
            </div>
            <a href="{{ route('whatsapp.setup') }}" class="btn btn-outline-primary btn-sm"><i
                    class="fas fa-cog me-1"></i>Setup & Docs</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show py-2 small" role="alert">
                <i class="fas fa-check-circle me-1"></i>{{ session('success') }}
                <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show py-2 small" role="alert">
                <i class="fas fa-times-circle me-1"></i>{{ session('error') }}
                <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-3">

            <!-- COL 1: Gateway Status Only -->
            <div class="col-lg-3 col-md-4">
                <div class="card">
                    <div class="card-header"><i class="fas fa-server text-primary me-2"></i>Gateway Status</div>
                    <div class="card-body text-center p-3">
                        <span id="ws-status" class="badge bg-secondary rounded-pill px-3 py-2 mb-2">Checking...</span>

                        <div id="ws-qr-area" class="status-box my-2" style="min-height: 140px;">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                            <p class="small text-muted mt-2 mb-0">Connecting...</p>
                        </div>

                        <div id="disconnectArea" class="d-grid gap-2 mt-2" style="display: none;">
                            <form action="{{ route('whatsapp.logout') }}" method="POST"
                                onsubmit="return confirm('Disconnect?');">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm w-100"><i
                                        class="fas fa-sign-out-alt me-1"></i>Logout</button>
                            </form>
                            <form action="{{ route('whatsapp.stop') }}" method="POST"
                                onsubmit="return confirm('Stop Service?');">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm w-100"><i
                                        class="fas fa-power-off me-1"></i>Stop</button>
                            </form>
                        </div>

                        <div id="startArea" class="mt-2" style="display: none;">
                            <form action="{{ route('whatsapp.start') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm w-100 pulse"><i
                                        class="fas fa-play me-1"></i>Start Service</button>
                            </form>
                        </div>

                        <button onclick="checkStatus()" class="btn btn-link btn-sm text-muted mt-1"><i
                                class="fas fa-sync-alt me-1"></i>Refresh</button>
                    </div>
                </div>
            </div>

            <!-- COL 2: Message Center (with all tabs) -->
            <div class="col-lg-9 col-md-8">
                <div class="card h-100">
                    <div class="card-header">
                        <i class="fas fa-broadcast-tower text-primary me-2"></i>Message Center
                    </div>
                    <div class="card-body">

                        <ul class="nav nav-pills flex-wrap mb-3" id="msgTabs">
                            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab"
                                    data-bs-target="#tab-multi"><i class="fas fa-users me-1"></i>Multi-Send</button>
                            </li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab"
                                    data-bs-target="#tab-unpaid"><i
                                        class="fas fa-file-invoice-dollar me-1"></i>Unpaid</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab"
                                    data-bs-target="#tab-all"><i class="fas fa-bullhorn me-1"></i>Broadcast All</button>
                            </li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab"
                                    data-bs-target="#tab-test"><i class="fas fa-paper-plane me-1"></i>Quick
                                    Test</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab"
                                    data-bs-target="#tab-api"><i class="fas fa-code me-1"></i>API Key</button></li>
                        </ul>

                        <div class="tab-content">
                            <!-- MULTI SEND -->
                            <div class="tab-pane fade show active" id="tab-multi">
                                <form action="{{ route('whatsapp.send.customer') }}" method="POST">
                                    @csrf
                                    <div class="row g-3">
                                        <div class="col-md-5">
                                            <label class="fw-bold">Select Recipients</label>
                                            <select name="customer_ids[]" id="multiUserSelect" class="form-select"
                                                multiple required style="width:100%">
                                                @foreach($customers as $c)
                                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-7">
                                            <label class="fw-bold">Message <small class="text-muted">(use
                                                    {name})</small></label>
                                            <textarea name="message" class="form-control" rows="4" required
                                                placeholder="Hello {name}, ..."></textarea>
                                        </div>
                                    </div>
                                    <div class="text-end mt-3">
                                        <button type="submit" class="btn btn-primary"><i
                                                class="fas fa-paper-plane me-1"></i>Send to Selected</button>
                                    </div>
                                </form>
                            </div>

                            <!-- UNPAID -->
                            <div class="tab-pane fade" id="tab-unpaid">
                                <div class="alert alert-warning py-2 small mb-3"><i class="fas fa-bell me-1"></i>Send
                                    reminders to customers with <b>UNPAID</b> invoices.</div>
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="fw-bold">Reminder Template</label>
                                        <textarea id="msgUnpaid" class="form-control"
                                            rows="4">Halo {name}, tagihan internet Anda sebesar Rp {tagihan} belum terbayar. Mohon segera lunasi.</textarea>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <button onclick="prepareBroadcast('unpaid')"
                                            class="btn btn-warning w-100 py-3"><i class="fab fa-whatsapp me-1"></i>Start
                                            Reminder</button>
                                    </div>
                                </div>
                            </div>

                            <!-- ALL BROADCAST -->
                            <div class="tab-pane fade" id="tab-all">
                                <div class="alert alert-info py-2 small mb-3"><i class="fas fa-rss me-1"></i>Broadcast
                                    to <b>ALL ACTIVE</b> customers.</div>
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="fw-bold">Announcement</label>
                                        <textarea id="msgAll" class="form-control"
                                            rows="4">Halo {name}, akan ada maintenance jaringan pada tanggal XX jam XX.</textarea>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <button onclick="prepareBroadcast('all')" class="btn btn-info w-100 py-3"><i
                                                class="fas fa-bullhorn me-1"></i>Broadcast Now</button>
                                    </div>
                                </div>
                            </div>

                            <!-- QUICK TEST -->
                            <div class="tab-pane fade" id="tab-test">
                                <div class="row justify-content-center">
                                    <div class="col-md-6">
                                        <div class="alert alert-secondary py-2 small mb-3"><i
                                                class="fas fa-vial me-1"></i>Send a quick test message to verify
                                            connection.</div>
                                        <form action="{{ route('whatsapp.test') }}" method="POST">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="fw-bold">Target Number</label>
                                                <input type="text" name="target" class="form-control"
                                                    placeholder="e.g 081234567890" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="fw-bold">Message</label>
                                                <textarea name="message" class="form-control" rows="3" required
                                                    placeholder="Hello, this is a test message..."></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-warning w-100"><i
                                                    class="fas fa-paper-plane me-1"></i>Send Test Message</button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- API KEY -->
                            <div class="tab-pane fade" id="tab-api">
                                <div class="row justify-content-center">
                                    <div class="col-md-8">
                                        <div class="alert alert-dark py-2 small mb-3"><i
                                                class="fas fa-plug me-1"></i>Use this API Key to integrate WhatsApp
                                            messaging with external applications.</div>

                                        <label class="fw-bold mb-2">Your API Key</label>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control font-monospace bg-light"
                                                value="{{ auth()->user()->api_token ?? 'Not Generated Yet' }}"
                                                id="apiKeyField" readonly>
                                            <button class="btn btn-outline-secondary" type="button"
                                                onclick="copyApiKey()"><i class="fas fa-copy"></i> Copy</button>
                                        </div>

                                        <div class="row g-2">
                                            <div class="col-sm-6">
                                                <form action="{{ route('whatsapp.apikey') }}" method="POST"
                                                    onsubmit="return confirm('Generate new key? Old key will be invalid.');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary w-100"><i
                                                            class="fas fa-key me-1"></i>Generate New Key</button>
                                                </form>
                                            </div>
                                            <div class="col-sm-6">
                                                <a href="{{ asset('docs/api.html') }}" target="_blank"
                                                    class="btn btn-info w-100"><i class="fas fa-book me-1"></i>View
                                                    Documentation</a>
                                            </div>
                                        </div>

                                        <hr class="my-4">
                                        <h6 class="fw-bold mb-2"><i class="fas fa-code me-2"></i>Example Usage (cURL)
                                        </h6>
                                        <div class="bg-dark text-light p-3 rounded small font-monospace"
                                            style="white-space: pre-wrap;">curl -X POST {{ url('/api/send-message') }} \
                                            -H "Content-Type: application/json" \
                                            -d '{
                                            "api_key": "YOUR_API_KEY",
                                            "number": "08123456789",
                                            "message": "Hello World"
                                            }'</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MONITOR -->
                        <div id="monitorArea" class="mt-4" style="display: none;">
                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold small"><i
                                        class="fas fa-desktop text-success me-1"></i>Progress</span>
                                <span class="small"><span id="statSuccess" class="text-success fw-bold">0</span> OK /
                                    <span id="statFail" class="text-danger fw-bold">0</span> Fail</span>
                            </div>
                            <div class="progress mb-2" style="height: 8px;">
                                <div id="progressBar"
                                    class="progress-bar bg-success progress-bar-striped progress-bar-animated"
                                    style="width: 0%"></div>
                            </div>
                            <div class="log-area" id="logList"></div>
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
        $(document).ready(function () {
            $('#multiUserSelect').select2({ placeholder: "Search customers...", allowClear: true, width: '100%' });
            checkStatus();
        });

        var queue = [], total = 0, current = 0, successCount = 0, failCount = 0, messageToSend = "";

        function prepareBroadcast(type) {
            messageToSend = type === 'unpaid' ? $('#msgUnpaid').val() : $('#msgAll').val();
            if (!messageToSend.trim()) { alert("Message empty!"); return; }
            if (!confirm("Start " + type.toUpperCase() + " broadcast?")) return;

            $('#monitorArea').slideDown();
            $('#logList').html('<div class="text-center text-muted py-2">Loading targets...</div>');
            $('#progressBar').css('width', '0%');
            $('#statSuccess').text('0'); $('#statFail').text('0');

            $.get("{{ route('whatsapp.broadcast.targets') }}", { type: type }, function (response) {
                queue = response; total = queue.length;
                if (total === 0) { $('#logList').html('<div class="text-warning text-center py-2">No targets found.</div>'); return; }
                $('#logList').html('');
                current = 0; successCount = 0; failCount = 0;
                $('button').prop('disabled', true);
                processQueue();
            }).fail(function () {
                $('#logList').html('<div class="text-danger text-center py-2">Failed to fetch targets.</div>');
            });
        }

        function processQueue() {
            if (current >= total) {
                $('button').prop('disabled', false);
                $('#progressBar').removeClass('progress-bar-animated');
                $('#logList').prepend('<div class="log-item text-info text-center fw-bold py-1">--- DONE ---</div>');
                alert("Broadcast Finished! Success: " + successCount + ", Failed: " + failCount);
                return;
            }
            let target = queue[current];
            $('#progressBar').css('width', Math.round(((current + 1) / total) * 100) + '%');

            $.post("{{ route('whatsapp.broadcast.process') }}", {
                _token: $('meta[name="csrf-token"]').attr('content'),
                id: target.id, message: messageToSend
            }).done(function (res) {
                if (res.status) { successCount++; $('#statSuccess').text(successCount); appendLog(target.name, true, 'Sent'); }
                else { failCount++; $('#statFail').text(failCount); appendLog(target.name, false, res.message || 'Error'); }
            }).fail(function () {
                failCount++; $('#statFail').text(failCount); appendLog(target.name, false, 'Server Error');
            }).always(function () { current++; processQueue(); });
        }

        function appendLog(name, status, msg) {
            let cls = status ? 'log-success' : 'log-error';
            let ico = status ? 'check' : 'times';
            $('#logList').prepend(`<div class="log-item"><i class="fas fa-${ico} ${cls} me-1"></i><b>${name}</b> <span class="float-end ${cls}">${msg}</span></div>`);
        }

        let pollTimer = null;
        function checkStatus() {
            if (pollTimer) clearTimeout(pollTimer);
            $.get("{{ route('whatsapp.gateway.status') }}", function (data) {
                let res = typeof data === 'string' ? JSON.parse(data) : data;
                let s = res.status;
                if (s === 'CONNECTED') {
                    $('#ws-status').text('CONNECTED').attr('class', 'badge bg-success rounded-pill px-3 py-2 mb-2');
                    $('#ws-qr-area').html('<div class="status-connected p-3 rounded"><i class="fas fa-check-circle fa-3x text-success mb-2"></i><br><b>Connected</b></div>').removeClass('status-offline').addClass('status-connected');
                    $('#disconnectArea').show(); $('#startArea').hide();
                } else if (s === 'QR_READY' || s === 'WAITING' || s === 'DISCONNECTED') {
                    $('#ws-status').text(s).attr('class', 'badge bg-warning text-dark rounded-pill px-3 py-2 mb-2');
                    $('#disconnectArea').show(); $('#startArea').hide();
                    loadQr();
                } else {
                    $('#ws-status').text('OFFLINE').attr('class', 'badge bg-danger rounded-pill px-3 py-2 mb-2');
                    $('#ws-qr-area').html('<div class="status-offline p-3 rounded"><i class="fas fa-server fa-3x text-danger mb-2"></i><br><b>Service Offline</b></div>');
                    $('#disconnectArea').hide(); $('#startArea').show();
                }
            }).fail(function () {
                $('#ws-status').text('OFFLINE').attr('class', 'badge bg-danger rounded-pill px-3 py-2 mb-2');
                $('#ws-qr-area').html('<div class="status-offline p-3 rounded"><i class="fas fa-server fa-3x text-danger mb-2"></i><br><b>Service Offline</b></div>');
                $('#disconnectArea').hide(); $('#startArea').show();
            });
        }

        function loadQr() {
            $.get("{{ route('whatsapp.gateway.qr') }}", function (data) {
                let res = typeof data === 'string' ? JSON.parse(data) : data;
                if (res.status === 'QR_READY' && res.qr_code) {
                    $('#ws-qr-area').html('<img src="' + res.qr_code + '" class="img-fluid rounded" style="max-width:160px"><p class="small text-muted mt-1 mb-0">Scan with WhatsApp</p>');
                    pollTimer = setTimeout(checkStatus, 3000);
                } else if (res.status === 'WAITING' || res.status === 'DISCONNECTED') {
                    $('#ws-qr-area').html('<div class="spinner-border spinner-border-sm text-warning"></div><p class="small text-muted mt-1 mb-0">Generating QR...</p>');
                    pollTimer = setTimeout(checkStatus, 4000);
                } else if (res.status === 'CONNECTED') { checkStatus(); }
            });
        }

        function copyApiKey() {
            var el = document.getElementById("apiKeyField");
            el.select(); el.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(el.value);
            alert("API Key copied!");
        }
    </script>
</body>

</html>