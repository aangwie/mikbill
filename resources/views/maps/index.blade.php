<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Peta Sebaran Pelanggan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    {{-- CSS LEAFLET --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <link rel="icon" href="{{ $global_favicon ?? asset('favicon.ico') }}">
    <style>
        #map {
            height: 80vh;
            width: 100%;
            border-radius: 10px;
        }

        /* Custom Icon Style untuk Rumah */
        .house-icon {
            font-size: 24px;
            text-align: center;
            text-shadow: 2px 2px 2px rgba(0, 0, 0, 0.5);
        }

        .house-online {
            color: #158828ff;
        }

        /* Hijau Terang */
        .house-offline {
            color: #ff0000;
        }

        /* Merah Terang */
    </style>
</head>

<body class="bg-light">

    @include('layouts.navbar_partial')

    <div class="container-fluid px-4 pb-4">

        {{-- HEADER & STATISTIK --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="mb-0"><i class="fas fa-map-marked-alt text-primary"></i> Peta Sebaran Pelanggan</h3>
                    {{-- Peta Sebaran Pelanggan --}}
                    <div class="d-flex align-items-center gap-3 mt-2 p-2 bg-white rounded shadow-sm border" style="width: fit-content;">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="switchMapRefresh" checked>
                            <label class="form-check-label small fw-bold" for="switchMapRefresh">Auto Refresh</label>
                        </div>
                        <select id="selectMapInterval" class="form-select form-select-sm border-secondary" style="width: auto; cursor: pointer;">
                            <option value="5">5 Detik</option>
                            <option value="15">15 Detik</option>
                            <option value="30">30 Detik</option>
                            <option value="60">60 Detik</option>
                            <option value="180">3 Menit</option>
                        </select>
                        <span class="badge bg-light text-dark border">
                            <i class="fas fa-clock text-warning me-1"></i> <b id="mapTimerDisplay">--</b>s
                        </span>
                    </div>
            </div>

            <div>
                <span class="badge bg-success me-2 p-2 shadow-sm"><i class="fas fa-home"></i> Online</span>
                <span class="badge bg-danger p-2 shadow-sm"><i class="fas fa-home"></i> Offline / Terputus</span>
            </div>
        </div>

        <div class="card shadow border-0">
            <div class="card-body p-1">
                <div id="map"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    {{-- JS LEAFLET --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        // 1. Inisialisasi Map
        // Default view Indonesia, tapi nanti akan di-override oleh fitBounds
        var map = L.map('map').setView([-2.5489, 118.0149], 5);

        // 2. Tambahkan Tile Layer (Peta Jalanan)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // 3. Ambil Data dari Controller
        var locations = @json($mapData);
        var markers = [];

        // 4. Looping Data untuk membuat Marker
        locations.forEach(function(loc) {

            // Tentukan Warna Ikon berdasarkan Status
            var colorClass = (loc.status === 'online') ? 'house-online' : 'house-offline';
            var statusBadge = (loc.status === 'online') ?
                '<span class="badge bg-success">ONLINE</span>' :
                '<span class="badge bg-danger">OFFLINE</span>';

            // Buat Custom Icon
            var houseIcon = L.divIcon({
                html: '<i class="fas fa-home ' + colorClass + '"></i>',
                className: 'house-icon',
                iconSize: [30, 30],
                iconAnchor: [15, 30],
                popupAnchor: [0, -30]
            });

            // Tambahkan Marker ke Peta
            var marker = L.marker([loc.lat, loc.lng], {
                icon: houseIcon
            }).addTo(map);

            // Isi Popup Info
            var popupContent = `
                <div class="text-center">
                    <h6 class="fw-bold mb-1">${loc.name}</h6>
                    <div class="mb-2">${statusBadge}</div>
                    <small class="d-block text-muted text-start mb-1"><i class="fas fa-user-tag me-1"></i> ${loc.username}</small>
                    <small class="d-block text-muted text-start mb-1"><i class="fas fa-map-marker-alt me-1"></i> ${loc.address}</small>
                    <div class="mt-2 d-grid">
                        <a href="https://wa.me/${loc.phone}" style="color:white;" target="_blank" class="btn btn-sm btn-success">
                            <i class="fab fa-whatsapp"></i> Chat WA
                        </a>
                    </div>
                </div>
            `;

            marker.bindPopup(popupContent);
            markers.push(marker);
        });

        // 5. Auto Zoom agar semua marker terlihat
        if (markers.length > 0) {
            var group = new L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.1));
        }

        // --- SCRIPT AUTO REFRESH (30 DETIK) ---
        // ... (Kode Leaflet Map sebelumnya biarkan saja) ...

        // --- LOGIKA AUTO REFRESH MAPS ---

        // 1. Load Settingan User
        let savedMapInterval = localStorage.getItem('map_refresh_interval') || 30;
        let savedMapStatus = localStorage.getItem('map_refresh_active');
        let isMapRefreshOn = (savedMapStatus === 'false') ? false : true;

        // 2. Set UI Awal
        document.getElementById('selectMapInterval').value = savedMapInterval;
        document.getElementById('switchMapRefresh').checked = isMapRefreshOn;

        let mapTimeLeft = parseInt(savedMapInterval);
        let mapTimerElem = document.getElementById('mapTimerDisplay');
        let mapIntervalId;

        function startMapTimer() {
            if (mapIntervalId) clearInterval(mapIntervalId);

            if (!isMapRefreshOn) {
                mapTimerElem.innerHTML = "OFF";
                return;
            }
            mapTimerElem.innerHTML = mapTimeLeft;

            mapIntervalId = setInterval(function() {
                // Jika user sedang drag map (mouse ditekan), pause sebentar agar tidak keganggu reload
                // (Opsional, tapi reload window pasti mengganggu, jadi kita biarkan reload saja)

                if (mapTimeLeft <= 0) {
                    window.location.reload();
                } else {
                    mapTimeLeft--;
                    mapTimerElem.innerHTML = mapTimeLeft;
                }
            }, 1000);
        }

        // Event Switch
        document.getElementById('switchMapRefresh').addEventListener('change', function() {
            isMapRefreshOn = this.checked;
            localStorage.setItem('map_refresh_active', isMapRefreshOn);

            if (isMapRefreshOn) {
                mapTimeLeft = parseInt(document.getElementById('selectMapInterval').value);
                startMapTimer();
            } else {
                clearInterval(mapIntervalId);
                mapTimerElem.innerHTML = "OFF";
            }
        });

        // Event Select Interval
        document.getElementById('selectMapInterval').addEventListener('change', function() {
            let newVal = this.value;
            localStorage.setItem('map_refresh_interval', newVal);
            mapTimeLeft = parseInt(newVal);
            if (isMapRefreshOn) mapTimerElem.innerHTML = mapTimeLeft;
        });

        // Start
        startMapTimer();
    </script>
</body>

</html>