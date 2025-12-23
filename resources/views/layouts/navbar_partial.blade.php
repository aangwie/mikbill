<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ route('pppoe.dashboard') }}">
            <i class="fas fa-wifi me-2"></i>Mikrotik App
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">

                {{-- 1. DASHBOARD (Semua Role Bisa Akses) --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('pppoe.dashboard') ? 'active' : '' }}"
                        href="{{ route('pppoe.dashboard') }}">
                        <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('maps.index') ? 'active' : '' }}"
                        href="{{ route('maps.index') }}">
                        <i class="fas fa-map-marked-alt me-1"></i> Peta Pelanggan
                    </a>
                </li>

                {{-- 2. MENU KHUSUS ADMIN --}}
                @if(auth()->user()->role == 'admin')

                    {{-- Traffic Monitor --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('traffic.index') ? 'active' : '' }}"
                            href="{{ route('traffic.index') }}">
                            <i class="fas fa-chart-area me-1"></i> Traffic
                        </a>
                    </li>



                    {{-- Dropdown Pengaturan & Manajemen --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('customers.*') || request()->routeIs('billing.*') || request()->routeIs('report.*') ? 'active' : '' }}"
                            href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-cogs me-1"></i> Manajemen
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="navbarDropdown">
                            <li>
                                <h6 class="dropdown-header">Pelanggan & Tagihan</h6>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('customers.index') }}"><i
                                        class="fas fa-users me-2"></i> Data Pelanggan</a></li>
                            <li><a class="dropdown-item" href="{{ route('billing.index') }}"><i
                                        class="fas fa-file-invoice-dollar me-2"></i> Tagihan (Billing)</a></li>
                            <li><a class="dropdown-item" href="{{ route('report.index') }}"><i
                                        class="fas fa-print me-2"></i> Laporan Keuangan</a></li>

                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <h6 class="dropdown-header">Pengaturan Sistem</h6>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('router.index') }}"><i
                                        class="fas fa-network-wired me-2"></i> Konfigurasi Router</a></li>
                            <li><a class="dropdown-item" href="{{ route('company.index') }}"><i
                                        class="fas fa-building me-2"></i> Profil Perusahaan</a></li>
                            <li><a class="dropdown-item" href="{{ route('users.index') }}"><i
                                        class="fas fa-user-shield me-2"></i> Manajemen User Admin</a></li>
                            <li><a class="dropdown-item" href="{{ route('whatsapp.index') }}"><i
                                        class="fab fa-whatsapp me-2"></i> WhatsApp API</a></li>

                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item text-primary" href="{{ route('system.index') }}">
                                    <i class="fas fa-sync me-2"></i> Update Aplikasi
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                {{-- 4. User Info & Logout --}}
                <li class="nav-item ms-3 border-start ps-3 d-flex align-items-center">
                    <div class="text-end me-2" style="line-height: 1.2;">
                        <span class="d-block text-white small fw-bold">{{ auth()->user()->name }}</span>
                        <span class="badge bg-warning text-dark"
                            style="font-size: 0.65em">{{ strtoupper(auth()->user()->role) }}</span>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm rounded-circle" title="Keluar">
                            <i class="fas fa-power-off"></i>
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>