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
        #map { height: 85vh; width: 100%; border-radius: 10px; }
        
        /* Custom Icon Style untuk Rumah */
        .house-icon {
            font-size: 24px;
            text-align: center;
            text-shadow: 2px 2px 2px rgba(0,0,0,0.5); /* Shadow agar terlihat di peta terang */
        }
        .house-online { color: #00ff2a; } /* Hijau Terang */
        .house-offline { color: #ff0000; } /* Merah Terang */
    </style>
</head>
<body class="bg-light">

    @include('layouts.navbar_partial')

    <div class="container-fluid px-4 pb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3><i class="fas fa-map-marked-alt text-primary"></i> Peta Sebaran Pelanggan</h3>
            <div>
                <span class="badge bg-success me-2"><i class="fas fa-home"></i> Online</span>
                <span class="badge bg-danger"><i class="fas fa-home"></i> Offline / Terputus</span>
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
        // 1. Inisialisasi Map (Default view: Indonesia)
        // Nanti akan otomatis zoom ke titik pelanggan
        var map = L.map('map').setView([-2.5489, 118.0149], 5); 

        // 2. Tambahkan Tile Layer (Peta Jalanan OpenStreetMap)
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

            // Buat Custom Icon menggunakan FontAwesome
            var houseIcon = L.divIcon({
                html: '<i class="fas fa-home ' + colorClass + '"></i>',
                className: 'house-icon',
                iconSize: [30, 30],
                iconAnchor: [15, 30], // Agar ujung bawah icon pas di titik koordinat
                popupAnchor: [0, -30]
            });

            // Tambahkan Marker ke Peta
            var marker = L.marker([loc.lat, loc.lng], {icon: houseIcon}).addTo(map);

            // Isi Popup (Info saat diklik)
            var popupContent = `
                <div class="text-center">
                    <h6 class="fw-bold mb-1">${loc.name}</h6>
                    <div class="mb-2">${statusBadge}</div>
                    <small class="d-block text-muted text-start mb-1"><i class="fas fa-user-tag me-1"></i> ${loc.username}</small>
                    <small class="d-block text-muted text-start mb-1"><i class="fas fa-map-marker-alt me-1"></i> ${loc.address}</small>
                    <div class="mt-2 d-grid">
                        <a href="https://wa.me/${loc.phone}" target="_blank" class="btn btn-sm btn-success">
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
            map.fitBounds(group.getBounds().pad(0.1)); // pad 0.1 agar tidak terlalu mepet pinggir
        }
    </script>
</body>
</html>