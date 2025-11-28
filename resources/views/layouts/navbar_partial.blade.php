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
                    <a class="nav-link {{ request()->routeIs('pppoe.dashboard') ? 'active' : '' }}" href="{{ route('pppoe.dashboard') }}">
                        <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                    </a>
                </li class="nav-item">
                <li>
                    <a class="nav-link {{ request()->routeIs('maps.index') ? 'active' : '' }}" href="{{ route('maps.index') }}">
                        <i class="fas fa-map-marked-alt me-1"></i> Peta Pelanggan
                    </a>
                </li>
                @if(auth()->user()->role == 'admin')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('traffic.index') ? '' : '' }}" href="{{ route('traffic.index') }}">
                        <i class="fas fa-chart-area me-1"></i> Traffic Monitor
                    </a>
                </li>
                @endif
                {{-- 2. MENU ADMINISTRASI (GABUNGAN) --}}
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('billing.*') || request()->routeIs('report.*') || request()->routeIs('accounting.*') ? 'active' : '' }}" href="#" id="navAdmin" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-folder-open me-1"></i> Administrasi
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navAdmin">

                        {{-- Billing (Semua Role) --}}
                        <li>
                            <a class="dropdown-item" href="{{ route('billing.index') }}">
                                <i class="fas fa-cash-register me-2 text-secondary"></i> Billing / Kasir
                            </a>
                        </li>

                        {{-- Laporan (Semua Role) --}}
                        <li>
                            <a class="dropdown-item" href="{{ route('report.index') }}">
                                <i class="fas fa-chart-bar me-2 text-secondary"></i> Laporan
                            </a>
                        </li>

                        {{-- Keuangan (HANYA ADMIN) --}}
                        {{-- Operator tidak akan melihat menu ini di dalam dropdown --}}
                        @if(auth()->user()->role == 'admin')
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('accounting.index') }}">
                                <i class="fas fa-wallet me-2 text-secondary"></i> Keuangan (Laba Rugi)
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>

                {{-- 3. MENU KHUSUS ADMIN (Pelanggan, User, Setting) --}}
                @if(auth()->user()->role == 'admin')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}">
                        <i class="fas fa-users me-1"></i> Pelanggan
                    </a>
                </li>

                {{-- Dropdown Pengaturan --}}
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('users.*') || request()->routeIs('router.*') ? 'active' : '' }}" href="#" id="navSettings" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cogs me-1"></i> Pengaturan
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navSettings">
                        <li><a class="dropdown-item" href="{{ route('users.index') }}"><i class="fas fa-users-cog me-2"></i> Manajemen User</a></li>
                        <li><a class="dropdown-item" href="{{ route('router.index') }}"><i class="fas fa-server me-2"></i> Config Mikrotik</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="{{ route('company.index') }}"><i class="fas fa-building me-2"></i> Perusahaan</a></li>
                        <li><a class="dropdown-item" href="{{ route('whatsapp.index') }}"><i class="fab fa-whatsapp me-2"></i> WhatsApp API</a></li>
                        {{-- MENU UPDATE SYSTEM (BARU) --}}
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
                        <span class="badge bg-warning text-dark" style="font-size: 0.65em">{{ strtoupper(auth()->user()->role) }}</span>
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