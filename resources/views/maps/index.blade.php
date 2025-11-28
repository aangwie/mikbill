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
        #map { height: 80vh; width: 100%; border-radius: 10px; }
        
        /* Custom Icon Style untuk Rumah */
        .house-icon {
            font-size: 24px;
            text-align: center;
            text-shadow: 2px 2px 2px rgba(0,0,0,0.5);
        }
        .house-online { color: #158828ff; } /* Hijau Terang */
        .house-offline { color: #ff0000; } /* Merah Terang */
    </style>
</head>
<body class="bg-light">

    @include('layouts.navbar_partial')

    <div class="container-fluid px-4 pb-4">
        
        {{-- HEADER & STATISTIK --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="mb-0"><i class="fas fa-map-marked-alt text-primary"></i> Peta Sebaran Pelanggan</h3>
                
                {{-- INDIKATOR AUTO REFRESH --}}
                <div class="d-flex align-items-center gap-2 mt-1">
                    <span class="badge bg-white text-secondary border shadow-sm">
                        <i class="fas fa-clock text-warning me-1"></i>
                        Refresh: <b id="timer" class="text-dark">30</b>s
                    </span>
                    <small class="text-muted fst-italic">Peta akan memuat ulang data otomatis.</small>
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
            var statusBadge = (loc.status === 'online') 
                ? '<span class="badge bg-success">ONLINE</span>' 
                : '<span class="badge bg-danger">OFFLINE</span>';

            // Buat Custom Icon
            var houseIcon = L.divIcon({
                html: '<i class="fas fa-home ' + colorClass + '"></i>',
                className: 'house-icon',
                iconSize: [30, 30],
                iconAnchor: [15, 30],
                popupAnchor: [0, -30]
            });

            // Tambahkan Marker ke Peta
            var marker = L.marker([loc.lat, loc.lng], {icon: houseIcon}).addTo(map);

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
        var timeLeft = 45;
        var elem = document.getElementById('timer');
        
        setInterval(function() {
            // Kita hentikan hitungan jika user sedang menahan klik (dragging map) agar tidak ganggu
            // Tapi untuk simplifikasi, kita refresh saja paksa.
            
            if (timeLeft <= 0) {
                window.location.reload();
            } else {
                elem.innerHTML = timeLeft;
                timeLeft--;
            }
        }, 1000);

    </script>
</body>
</html>