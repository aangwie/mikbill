@extends('layouts.app2')

@section('title', 'Peta Persebaran Pelanggan')
@section('header', 'Peta Pelanggan')
@section('subheader', 'Lokasi persebaran pelanggan dan status koneksi.')

@section('content')

    <!-- Map Controls -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="inline-flex items-center rounded-lg bg-white p-1.5 shadow-sm border border-slate-200">
            <label class="flex items-center cursor-pointer px-2">
                <span class="mr-2 text-sm font-medium text-slate-600">Auto Refresh</span>
                <div class="relative">
                    <input type="checkbox" id="switchMapRefresh" class="sr-only" checked>
                    <div class="block bg-slate-200 w-10 h-6 rounded-full transition-colors duration-300" id="switchBg">
                    </div>
                    <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform duration-300 transform translate-x-4"
                        id="switchDot"></div>
                </div>
            </label>
            <div class="h-6 w-px bg-slate-200 mx-2"></div>
            <select id="selectMapInterval"
                class="block w-28 rounded-md border-0 py-1 pl-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-xs sm:leading-6">
                <option value="5">5 Detik</option>
                <option value="15">15 Detik</option>
                <option value="30">30 Detik</option>
                <option value="60">1 Menit</option>
                <option value="180">3 Menit</option>
            </select>
            <div class="ml-3 px-2 py-1 bg-slate-100 rounded text-xs font-mono text-slate-500 min-w-[40px] text-center"
                id="mapTimerDisplay">--</div>
        </div>

        <div class="flex gap-2">
            <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm">
                <span class="h-2.5 w-2.5 rounded-full bg-green-500"></span>
                <span class="text-xs font-semibold text-slate-600">Online</span>
            </div>
            <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm">
                <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>
                <span class="text-xs font-semibold text-slate-600">Offline</span>
            </div>
        </div>
    </div>

    <!-- Map Container -->
    <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden relative h-[75vh]">
        <div id="map" class="absolute inset-0 h-full w-full z-0"></div>
    </div>

@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        /* Custom Switch */
        input:checked~#switchBg {
            @apply bg-primary-600;
        }

        input:checked~#switchDot {
            @apply translate-x-6;
        }

        /* Map Icons */
        .house-icon {
            font-size: 24px;
            text-align: center;
            filter: drop-shadow(0 2px 2px rgba(0, 0, 0, 0.3));
            transition: transform 0.2s;
        }

        .house-icon:hover {
            transform: scale(1.2);
        }

        .house-online {
            color: #10b981;
        }

        .house-offline {
            color: #ef4444;
        }

        /* Map Popup Tailwind-like Styling */
        .leaflet-popup-content-wrapper {
            @apply rounded-xl shadow-xl overflow-hidden p-0 !important;
        }

        .leaflet-popup-content {
            @apply m-0 !important;
        }

        .leaflet-container a.leaflet-popup-close-button {
            @apply text-slate-400 hover:text-slate-600 p-1 !important;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        var map = L.map('map').setView([-2.5489, 118.0149], 5);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var locations = @json($mapData);
        var markers = [];

        locations.forEach(function (loc) {
            var colorClass = (loc.status === 'online') ? 'house-online' : 'house-offline';
            var statusBadge = (loc.status === 'online') ?
                '<span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">ONLINE</span>' :
                '<span class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">OFFLINE</span>';

            var houseIcon = L.divIcon({
                html: '<i class="fas fa-home ' + colorClass + '"></i>',
                className: 'house-icon',
                iconSize: [30, 30],
                iconAnchor: [15, 30],
                popupAnchor: [0, -30]
            });

            var marker = L.marker([loc.lat, loc.lng], { icon: houseIcon }).addTo(map);

            var popupContent = `
                <div class="px-4 py-3 bg-white min-w-[200px]">
                    <div class="text-center mb-3">
                        <h6 class="text-sm font-bold text-slate-800 mb-1 leading-tight">${loc.name}</h6>
                        ${statusBadge}
                    </div>
                    <div class="space-y-1.5 border-t border-slate-100 pt-2">
                        <div class="flex items-start text-xs text-slate-500">
                            <i class="fas fa-user-circle mt-0.5 mr-2 text-slate-400"></i>
                            <span class="font-mono text-slate-700">${loc.username}</span>
                        </div>
                        <div class="flex items-start text-xs text-slate-500">
                            <i class="fas fa-map-marker-alt mt-0.5 mr-2 text-slate-400"></i>
                            <span>${loc.address ? loc.address.substring(0, 30) + '...' : '-'}</span>
                        </div>
                    </div>
                    <div class="mt-3 pt-2">
                        <a href="https://wa.me/${loc.phone}" target="_blank" class="flex w-full justify-center items-center rounded-md bg-green-50 px-2 py-1.5 text-xs font-bold text-green-700 hover:bg-green-100 transition-colors">
                            <i class="fab fa-whatsapp mr-1.5"></i> Chat WhatsApp
                        </a>
                    </div>
                </div>
            `;

            marker.bindPopup(popupContent);
            markers.push(marker);
        });

        if (markers.length > 0) {
            var group = new L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.1));
        }

        // Auto Refresh Logic
        let savedMapInterval = localStorage.getItem('map_refresh_interval') || 30;
        let savedMapStatus = localStorage.getItem('map_refresh_active');
        let isMapRefreshOn = (savedMapStatus === 'false') ? false : true;

        document.getElementById('selectMapInterval').value = savedMapInterval;
        document.getElementById('switchMapRefresh').checked = isMapRefreshOn;

        let mapTimeLeft = parseInt(savedMapInterval);
        let mapTimerElem = document.getElementById('mapTimerDisplay');
        let mapIntervalId;

        function startMapTimer() {
            if (mapIntervalId) clearInterval(mapIntervalId);
            if (!isMapRefreshOn) { mapTimerElem.innerHTML = "OFF"; return; }
            mapTimerElem.innerHTML = mapTimeLeft;

            mapIntervalId = setInterval(function () {
                if (mapTimeLeft <= 0) window.location.reload();
                else { mapTimeLeft--; mapTimerElem.innerHTML = mapTimeLeft; }
            }, 1000);
        }

        document.getElementById('switchMapRefresh').addEventListener('change', function () {
            isMapRefreshOn = this.checked;
            localStorage.setItem('map_refresh_active', isMapRefreshOn);
            if (isMapRefreshOn) { mapTimeLeft = parseInt(document.getElementById('selectMapInterval').value); startMapTimer(); }
            else { clearInterval(mapIntervalId); mapTimerElem.innerHTML = "OFF"; }
        });

        document.getElementById('selectMapInterval').addEventListener('change', function () {
            let newVal = this.value;
            localStorage.setItem('map_refresh_interval', newVal);
            mapTimeLeft = parseInt(newVal);
            if (isMapRefreshOn) mapTimerElem.innerHTML = mapTimeLeft;
        });

        startMapTimer();
    </script>
@endpush