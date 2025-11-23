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

                {{-- Menu Dashboard --}}
                @if(auth()->user()->role == 'admin')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('pppoe.dashboard') ? 'active' : '' }}" href="{{ route('pppoe.dashboard') }}">
                        <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                    </a>
                </li>

                {{-- Menu Pelanggan --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}">
                        <i class="fas fa-users me-1"></i> Pelanggan
                    </a>
                </li>
                @endif
                {{-- Menu Billing --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('billing.*') ? 'active' : '' }}" href="{{ route('billing.index') }}">
                        <i class="fas fa-cash-register me-1"></i> Billing
                    </a>
                </li>

                {{-- Menu Laporan (Baru) --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('report.*') ? 'active' : '' }}" href="{{ route('report.index') }}">
                        <i class="fas fa-chart-bar me-1"></i> Laporan
                    </a>
                </li>

                {{-- Menu Laporan (Baru) --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('accounting.*') ? 'active' : '' }}" href="{{ route('accounting.index') }}">
                        <i class="fas fa-wallet me-1"></i> Keuangan
                    </a>
                </li>

                @if(auth()->user()->role == 'admin')
                {{-- Menu whatsapp --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('whatsapp.*') ? 'active' : '' }}" href="{{ route('whatsapp.index') }}">
                        <i class="fab fa-whatsapp me-1"></i> WhatsApp
                    </a>
                </li>

                {{-- Menu perusahaan --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('company.*') ? 'active' : '' }}" href="{{ route('company.index') }}">
                        <i class="fas fa-building me-1"></i> Perusahaan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                        <i class="fas fa-users-cog me-1"></i> Users
                    </a>
                </li>
                @endif
                {{-- Tombol Logout --}}
                <li class="nav-item ms-3">
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm mt-1">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>