<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring PPPoE Mikrotik</title>
    <link rel="icon" href="{{ $global_favicon ?? asset('favicon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="icon" href="{{ $global_favicon ?? asset('favicon.ico') }}">

    <style>
        .status-dot { height: 10px; width: 10px; background-color: #bbb; border-radius: 50%; display: inline-block; }
        .online { background-color: #28a745; box-shadow: 0 0 5px #28a745; }
        .offline { background-color: #dc3545; }
        .navbar-brand { font-weight: bold; letter-spacing: 1px; }
        .dataTables_wrapper { padding: 20px; }
    </style>
</head>
<body class="bg-light">

    @include('layouts.navbar_partial')

    <div class="container pb-5">
        
        {{-- Info Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3><i class="fas fa-network-wired text-primary"></i> Monitor User Online</h3>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <small class="text-muted">Pantau status koneksi pelanggan secara realtime</small>
                    <span class="badge bg-white text-secondary border shadow-sm">
                        <i class="fas fa-clock text-warning me-1"></i>
                        Refresh: <b id="timer" class="text-dark">30</b>s
                    </span>
                </div>
            </div>
            
            <div class="text-end">
                <h5 class="mb-1">
                    Host: <strong>{{ $routerInfo->host ?? 'Belum Disetting' }}</strong>
                    <span class="text-muted small">({{ $routerInfo->username ?? '-' }})</span>
                </h5>
                @if(isset($isConnected) && $isConnected)
                    <span class="badge bg-success shadow-sm"><i class="fas fa-link me-1"></i> TERHUBUNG</span>
                    <span class="badge bg-secondary ms-1">Port: {{ $routerInfo->port }}</span>
                @else
                    <span class="badge bg-danger shadow-sm"><i class="fas fa-unlink me-1"></i> TERPUTUS</span>
                    @if($routerInfo)
                        @if(auth()->user()->role == 'admin')
                            <a href="{{ route('router.index') }}" class="btn btn-sm btn-outline-danger ms-2 py-0"><i class="fas fa-cog"></i> Config</a>
                        @endif
                    @endif
                @endif
            </div>
        </div>

        {{-- Alerts --}}
        @if(isset($error) && $error) <div class="alert alert-danger shadow-sm">{{ $error }}</div> @endif
        @if(session('success')) <div class="alert alert-success shadow-sm">{{ session('success') }}</div> @endif
        @if(session('warning')) <div class="alert alert-warning shadow-sm">{{ session('warning') }}</div> @endif

        {{-- KARTU MONITORING --}}
        @if(isset($secrets) && isset($actives))
            @php
                $totalUser = count($secrets);
                $onlineUser = $actives->count();
                $offlineUser = $totalUser - $onlineUser;
            @endphp
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white shadow-sm h-100 border-0">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div><h6 class="text-uppercase mb-1 opacity-75">Total Pelanggan</h6><h2 class="mb-0 fw-bold">{{ $totalUser }}</h2></div>
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white shadow-sm h-100 border-0">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div><h6 class="text-uppercase mb-1 opacity-75">Sedang Online</h6><h2 class="mb-0 fw-bold">{{ $onlineUser }}</h2></div>
                            <i class="fas fa-wifi fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-secondary text-white shadow-sm h-100 border-0">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div><h6 class="text-uppercase mb-1 opacity-75">Sedang Offline</h6><h2 class="mb-0 fw-bold">{{ $offlineUser }}</h2></div>
                            <i class="fas fa-power-off fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Tabel DataTables --}}
        @if(isset($secrets))
        <div class="card shadow border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-secondary">Daftar Pelanggan ({{ count($secrets) }})</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="tableUser" class="table table-hover mb-0 align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                {{-- TOTAL KOLOM DASAR: 7 --}}
                                <th>Status</th>
                                <th>Username</th>
                                <th>Profile</th>
                                <th>IP Address</th>
                                <th>Status</th>
                                <th>Terakhir Terputus</th>
                                <th>Durasi (Uptime)</th>
                                
                                {{-- KOLOM KE-8 (HANYA ADMIN) --}}
                                @if(auth()->user()->role == 'admin')
                                    <th class="text-end">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($secrets as $secret)
                                @php
                                    $name = $secret['name'];
                                    $isActive = $actives->has($name);
                                    $activeData = $isActive ? $actives[$name] : null;
                                    $isDisabled = isset($secret['disabled']) && $secret['disabled'] == 'true';
                                    $lastLoggedOut = $secret['last-logged-out'] ?? '-';
                                @endphp
                                <tr class="{{ $isDisabled ? 'table-secondary text-muted' : '' }}">
                                    <td>
                                        @if($isDisabled) <span class="badge bg-secondary">Disabled</span>
                                        @elseif($isActive) <span class="status-dot online"></span> <span class="d-none">Online</span>
                                        @else <span class="status-dot offline"></span> <span class="d-none">Offline</span> @endif
                                    </td>
                                    <td class="fw-bold">{{ $secret['name'] }} @if($isDisabled) <i class="fas fa-ban text-danger ms-1"></i> @endif</td>
                                    <td><span class="badge bg-info text-dark">{{ $secret['profile'] ?? 'default' }}</span></td>
                                    <td>{{ $activeData['address'] ?? '-' }}</td>
                                    <td>
                                        @if($isActive) <span class="text-success small fw-bold"><i class="fas fa-plug me-1"></i>Connected</span>
                                        @else <span class="text-danger small fw-bold"><i class="fas fa-chain-broken me-1"></i>Disconnected</span> @endif
                                    </td>
                                    <td>
                                        @if($isActive) <span class="text-muted small">-</span>
                                        @else <small class="text-secondary">{{ $lastLoggedOut }}</small> @endif
                                    </td>
                                    <td class="fw-bold text-dark">{{ $activeData['uptime'] ?? '-' }}</td>
                                    
                                    {{-- KOLOM KE-8 (HANYA ADMIN) --}}
                                    @if(auth()->user()->role == 'admin')
                                        <td class="text-end">
                                            <div class="d-flex gap-1 justify-content-end">
                                                @if($isActive)
                                                    <form action="{{ route('pppoe.kick') }}" method="POST" onsubmit="return confirm('Reset koneksi user {{ $name }}?');">
                                                        @csrf
                                                        <input type="hidden" name="username" value="{{ $name }}">
                                                        <button type="submit" class="btn btn-sm btn-warning" title="Reset Koneksi"><i class="fas fa-sync-alt"></i></button>
                                                    </form>
                                                @endif
                                                <form action="{{ route('pppoe.toggle') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="username" value="{{ $name }}">
                                                    @if($isDisabled)
                                                        <input type="hidden" name="action" value="enable">
                                                        <button type="submit" class="btn btn-sm btn-success" title="Aktifkan"><i class="fas fa-check"></i></button>
                                                    @else
                                                        <input type="hidden" name="action" value="disable">
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Blokir user {{ $name }}?');" title="Blokir"><i class="fas fa-ban"></i></button>
                                                    @endif
                                                </form>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#tableUser').DataTable({
                "language": { "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json" }
            });

            var timeLeft = 30;
            var elem = document.getElementById('timer');
            setInterval(function() {
                var isModalOpen = $('.modal.show').length > 0;
                if (isModalOpen) { timeLeft = 30; elem.innerHTML = "Paused"; return; }
                if (timeLeft <= 0) window.location.reload();
                else { elem.innerHTML = timeLeft; timeLeft--; }
            }, 1000);
        });
    </script>
</body>
</html>