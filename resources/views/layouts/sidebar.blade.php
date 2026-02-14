{{-- Sidebar Overlay (Mobile) --}}
<div x-show="sidebarOpen" @click="sidebarOpen = false"
    class="fixed inset-0 z-[60] bg-slate-900/50 backdrop-blur-sm lg:hidden"
    x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;"></div>

{{-- Sidebar --}}
<aside :class="[
        sidebarOpen ? 'translate-x-0' : '-translate-x-full',
        sidebarCollapsed ? 'lg:w-20' : 'lg:w-64'
    ]"
    class="fixed inset-y-0 left-0 z-[70] w-64 transform bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 transition-all duration-300 ease-in-out lg:translate-x-0 lg:z-30 flex flex-col"
    @click.away="if(window.innerWidth < 1024) sidebarOpen = false">

    {{-- Sidebar Header --}}
    <div
        class="flex h-16 items-center justify-between px-4 border-b border-slate-200 dark:border-slate-700 flex-shrink-0">
        {{-- Mini logo when collapsed --}}
        <a href="{{ route('pppoe.dashboard') }}" class="flex items-center gap-2 group min-w-0"
            :class="sidebarCollapsed ? 'lg:justify-center lg:w-full' : ''">
            <div
                class="bg-[#352f99] text-white p-2 rounded-lg shadow-md group-hover:bg-indigo-700 transition flex-shrink-0">
                <i class="fas fa-wifi text-sm"></i>
            </div>
            <span class="text-lg font-bold text-slate-800 dark:text-white tracking-tight truncate"
                :class="sidebarCollapsed ? 'lg:hidden' : ''" x-cloak>
                Menu
            </span>
        </a>
        {{-- Close button (mobile only) --}}
        <button @click="sidebarOpen = false"
            class="lg:hidden p-1 rounded-md text-slate-400 hover:text-slate-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-700 transition">
            <i class="fas fa-times"></i>
        </button>
    </div>

    {{-- Sidebar Navigation --}}
    <nav class="flex-1 overflow-y-auto py-4 scrollbar-thin" :class="sidebarCollapsed ? 'lg:px-2' : 'px-3'">
        <div class="space-y-1">

            {{-- Dashboard --}}
            <a href="{{ route('pppoe.dashboard') }}"
                class="group relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
            {{ request()->routeIs('pppoe.dashboard') ? 'bg-[#352f99]/10 text-[#352f99] dark:bg-indigo-900/40 dark:text-indigo-300 border-l-[3px] border-[#352f99]' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white' }}"
                :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                <i
                    class="fas fa-tachometer-alt w-5 text-center flex-shrink-0 {{ request()->routeIs('pppoe.dashboard') ? 'text-[#352f99] dark:text-indigo-400' : 'text-slate-400' }}"></i>
                <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Dashboard</span>
                {{-- Tooltip when collapsed --}}
                <span x-show="sidebarCollapsed"
                    class="hidden lg:block absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-slate-900 rounded-md shadow-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-50"
                    style="display:none;">
                    Dashboard
                </span>
            </a>

            {{-- Kontrol (Superadmin) --}}
            @if(auth()->user()->isSuperAdmin())
                <a href="{{ route('control.index') }}"
                    class="group relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                        {{ request()->routeIs('control.index') ? 'bg-[#352f99]/10 text-[#352f99] dark:bg-indigo-900/40 dark:text-indigo-300 border-l-[3px] border-[#352f99]' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white' }}"
                    :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                    <i
                        class="fas fa-hammer w-5 text-center flex-shrink-0 {{ request()->routeIs('control.index') ? 'text-[#352f99] dark:text-indigo-400' : 'text-slate-400' }}"></i>
                    <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Kontrol</span>
                    <span x-show="sidebarCollapsed"
                        class="hidden lg:block absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-slate-900 rounded-md shadow-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-50"
                        style="display:none;">Kontrol</span>
                </a>
            @endif

            {{-- Maps --}}
            <a href="{{ route('maps.index') }}"
                class="group relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
            {{ request()->routeIs('maps.index') ? 'bg-[#352f99]/10 text-[#352f99] dark:bg-indigo-900/40 dark:text-indigo-300 border-l-[3px] border-[#352f99]' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white' }}"
                :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                <i
                    class="fas fa-map-marked-alt w-5 text-center flex-shrink-0 {{ request()->routeIs('maps.index') ? 'text-[#352f99] dark:text-indigo-400' : 'text-slate-400' }}"></i>
                <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Maps</span>
                <span x-show="sidebarCollapsed"
                    class="hidden lg:block absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-slate-900 rounded-md shadow-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-50"
                    style="display:none;">Maps</span>
            </a>

            {{-- Plans (Admin only) --}}
            @if(auth()->user()->role == 'admin')
                <a href="{{ route('plans.public') }}"
                    class="group relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                        {{ request()->routeIs('plans.public') ? 'bg-[#352f99]/10 text-[#352f99] dark:bg-indigo-900/40 dark:text-indigo-300 border-l-[3px] border-[#352f99]' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white' }}"
                    :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                    <i
                        class="fas fa-box w-5 text-center flex-shrink-0 {{ request()->routeIs('plans.public') ? 'text-[#352f99] dark:text-indigo-400' : 'text-slate-400' }}"></i>
                    <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Plans</span>
                    <span x-show="sidebarCollapsed"
                        class="hidden lg:block absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-slate-900 rounded-md shadow-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-50"
                        style="display:none;">Plans</span>
                </a>
            @endif

            {{-- SECTION: Pelanggan & Kasir --}}
            @if(auth()->user()->role == 'operator' || auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                <div class="pt-4 pb-1" :class="sidebarCollapsed ? 'lg:pt-3 lg:pb-0' : ''">
                    <p class="px-3 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest"
                        :class="sidebarCollapsed ? 'lg:hidden' : ''">Pelanggan & Kasir</p>
                    <div x-show="sidebarCollapsed"
                        class="hidden lg:block border-t border-slate-200 dark:border-slate-700 mx-2" style="display:none;">
                    </div>
                </div>

                @if(auth()->user()->role == 'operator' || auth()->user()->isSuperAdmin())
                    <a href="{{ route('customers.index') }}"
                        class="group relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                                    {{ request()->routeIs('customers.index') ? 'bg-[#352f99]/10 text-[#352f99] dark:bg-indigo-900/40 dark:text-indigo-300 border-l-[3px] border-[#352f99]' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white' }}"
                        :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                        <i
                            class="fas fa-users w-5 text-center flex-shrink-0 {{ request()->routeIs('customers.index') ? 'text-[#352f99] dark:text-indigo-400' : 'text-slate-400' }}"></i>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Data Pelanggan</span>
                        <span x-show="sidebarCollapsed"
                            class="hidden lg:block absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-slate-900 rounded-md shadow-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-50"
                            style="display:none;">Data Pelanggan</span>
                    </a>

                    <a href="{{ route('billing.index') }}"
                        class="group relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                                    {{ request()->routeIs('billing.index') ? 'bg-[#352f99]/10 text-[#352f99] dark:bg-indigo-900/40 dark:text-indigo-300 border-l-[3px] border-[#352f99]' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white' }}"
                        :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                        <i
                            class="fas fa-file-invoice-dollar w-5 text-center flex-shrink-0 {{ request()->routeIs('billing.index') ? 'text-[#352f99] dark:text-indigo-400' : 'text-slate-400' }}"></i>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Tagihan</span>
                        <span x-show="sidebarCollapsed"
                            class="hidden lg:block absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-slate-900 rounded-md shadow-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-50"
                            style="display:none;">Tagihan</span>
                    </a>
                @endif

                @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                    <a href="{{ route('accounting.index') }}"
                        class="group relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                                    {{ request()->routeIs('accounting.index') ? 'bg-[#352f99]/10 text-[#352f99] dark:bg-indigo-900/40 dark:text-indigo-300 border-l-[3px] border-[#352f99]' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white' }}"
                        :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                        <i
                            class="fas fa-money-bill-wave w-5 text-center flex-shrink-0 {{ request()->routeIs('accounting.index') ? 'text-[#352f99] dark:text-indigo-400' : 'text-slate-400' }}"></i>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Biaya & Pengeluaran</span>
                        <span x-show="sidebarCollapsed"
                            class="hidden lg:block absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-slate-900 rounded-md shadow-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-50"
                            style="display:none;">Biaya & Pengeluaran</span>
                    </a>

                    <a href="{{ route('report.index') }}"
                        class="group relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                                    {{ request()->routeIs('report.index') ? 'bg-[#352f99]/10 text-[#352f99] dark:bg-indigo-900/40 dark:text-indigo-300 border-l-[3px] border-[#352f99]' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white' }}"
                        :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                        <i
                            class="fas fa-print w-5 text-center flex-shrink-0 {{ request()->routeIs('report.index') ? 'text-[#352f99] dark:text-indigo-400' : 'text-slate-400' }}"></i>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Laporan Keuangan</span>
                        <span x-show="sidebarCollapsed"
                            class="hidden lg:block absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-slate-900 rounded-md shadow-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-50"
                            style="display:none;">Laporan Keuangan</span>
                    </a>
                @endif
            @endif

            {{-- SECTION: Hotspot --}}
            @if(auth()->user()->isAdmin() || auth()->user()->role == 'operator' || auth()->user()->isSuperAdmin())
                <div class="pt-4 pb-1" :class="sidebarCollapsed ? 'lg:pt-3 lg:pb-0' : ''">
                    <p class="px-3 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest"
                        :class="sidebarCollapsed ? 'lg:hidden' : ''">Hotspot & Monitor</p>
                    <div x-show="sidebarCollapsed"
                        class="hidden lg:block border-t border-slate-200 dark:border-slate-700 mx-2" style="display:none;">
                    </div>
                </div>

                {{-- Hotspot submenu --}}
                <div x-data="{ open: {{ request()->routeIs('hotspot.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" type="button"
                        class="group relative w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                            {{ request()->routeIs('hotspot.*') ? 'bg-[#352f99]/10 text-[#352f99] dark:bg-indigo-900/40 dark:text-indigo-300' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50' }}"
                        :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                        <span class="flex items-center gap-3">
                            <i
                                class="fas fa-wifi w-5 text-center flex-shrink-0 {{ request()->routeIs('hotspot.*') ? 'text-[#352f99] dark:text-indigo-400' : 'text-slate-400' }}"></i>
                            <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Hotspot</span>
                        </span>
                        <i class="fas fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200"
                            :class="[{ 'rotate-180': open }, sidebarCollapsed ? 'lg:hidden' : '']"></i>
                        <span x-show="sidebarCollapsed"
                            class="hidden lg:block absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-slate-900 rounded-md shadow-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-50"
                            style="display:none;">Hotspot</span>
                    </button>
                    <div x-show="open && (!sidebarCollapsed || window.innerWidth < 1024)" x-collapse
                        class="ml-5 mt-1 space-y-0.5 border-l-2 border-slate-200 dark:border-slate-700 pl-3">
                        <a href="{{ route('hotspot.monitor') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-md text-sm transition-all duration-200
                                {{ request()->routeIs('hotspot.monitor') ? 'text-[#352f99] dark:text-indigo-300 font-semibold bg-[#352f99]/5' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-700/30' }}">
                            <i class="fas fa-desktop w-4 text-center text-xs"></i>
                            Monitor
                        </a>
                        <a href="{{ route('hotspot.generate') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-md text-sm transition-all duration-200
                                {{ request()->routeIs('hotspot.generate') ? 'text-[#352f99] dark:text-indigo-300 font-semibold bg-[#352f99]/5' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-700/30' }}">
                            <i class="fas fa-user-plus w-4 text-center text-xs"></i>
                            Generate Akun
                        </a>
                    </div>
                </div>

                {{-- Monitor submenu --}}
                <div x-data="{ open: {{ request()->routeIs('monitor.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" type="button"
                        class="group relative w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                            {{ request()->routeIs('monitor.*') ? 'bg-[#352f99]/10 text-[#352f99] dark:bg-indigo-900/40 dark:text-indigo-300' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50' }}"
                        :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                        <span class="flex items-center gap-3">
                            <i
                                class="fas fa-desktop w-5 text-center flex-shrink-0 {{ request()->routeIs('monitor.*') ? 'text-[#352f99] dark:text-indigo-400' : 'text-slate-400' }}"></i>
                            <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Monitor</span>
                        </span>
                        <i class="fas fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200"
                            :class="[{ 'rotate-180': open }, sidebarCollapsed ? 'lg:hidden' : '']"></i>
                        <span x-show="sidebarCollapsed"
                            class="hidden lg:block absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-slate-900 rounded-md shadow-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-50"
                            style="display:none;">Monitor</span>
                    </button>
                    <div x-show="open && (!sidebarCollapsed || window.innerWidth < 1024)" x-collapse
                        class="ml-5 mt-1 space-y-0.5 border-l-2 border-slate-200 dark:border-slate-700 pl-3">
                        <a href="{{ route('monitor.dhcp-leases') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-md text-sm transition-all duration-200
                                {{ request()->routeIs('monitor.dhcp-leases') ? 'text-[#352f99] dark:text-indigo-300 font-semibold bg-[#352f99]/5' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-700/30' }}">
                            <i class="fas fa-network-wired w-4 text-center text-xs"></i>
                            DHCP Leases
                        </a>
                        <a href="{{ route('monitor.static-users') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-md text-sm transition-all duration-200
                                {{ request()->routeIs('monitor.static-users') ? 'text-[#352f99] dark:text-indigo-300 font-semibold bg-[#352f99]/5' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-700/30' }}">
                            <i class="fas fa-user-tag w-4 text-center text-xs"></i>
                            Static Users
                        </a>
                        <a href="{{ route('monitor.simple-queues') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-md text-sm transition-all duration-200
                                {{ request()->routeIs('monitor.simple-queues') ? 'text-[#352f99] dark:text-indigo-300 font-semibold bg-[#352f99]/5' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-700/30' }}">
                            <i class="fas fa-stream w-4 text-center text-xs"></i>
                            Simple Queues
                        </a>
                    </div>
                </div>
            @endif

            {{-- SECTION: Sistem (Admin & Superadmin) --}}
            @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                <div class="pt-4 pb-1" :class="sidebarCollapsed ? 'lg:pt-3 lg:pb-0' : ''">
                    <p class="px-3 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest"
                        :class="sidebarCollapsed ? 'lg:hidden' : ''">Sistem</p>
                    <div x-show="sidebarCollapsed"
                        class="hidden lg:block border-t border-slate-200 dark:border-slate-700 mx-2" style="display:none;">
                    </div>
                </div>

                <a href="{{ route('traffic.index') }}"
                    class="group relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                        {{ request()->routeIs('traffic.index') ? 'bg-[#352f99]/10 text-[#352f99] dark:bg-indigo-900/40 dark:text-indigo-300 border-l-[3px] border-[#352f99]' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white' }}"
                    :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                    <i
                        class="fas fa-chart-area w-5 text-center flex-shrink-0 {{ request()->routeIs('traffic.index') ? 'text-[#352f99] dark:text-indigo-400' : 'text-slate-400' }}"></i>
                    <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Traffic</span>
                    <span x-show="sidebarCollapsed"
                        class="hidden lg:block absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-slate-900 rounded-md shadow-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-50"
                        style="display:none;">Traffic</span>
                </a>

                <a href="{{ route('router.index') }}"
                    class="group relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                        {{ request()->routeIs('router.index') ? 'bg-[#352f99]/10 text-[#352f99] dark:bg-indigo-900/40 dark:text-indigo-300 border-l-[3px] border-[#352f99]' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white' }}"
                    :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                    <i
                        class="fas fa-network-wired w-5 text-center flex-shrink-0 {{ request()->routeIs('router.index') ? 'text-[#352f99] dark:text-indigo-400' : 'text-slate-400' }}"></i>
                    <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Router Mikrotik</span>
                    <span x-show="sidebarCollapsed"
                        class="hidden lg:block absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-slate-900 rounded-md shadow-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-50"
                        style="display:none;">Router Mikrotik</span>
                </a>

                <a href="{{ route('company.index') }}"
                    class="group relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                        {{ request()->routeIs('company.index') ? 'bg-[#352f99]/10 text-[#352f99] dark:bg-indigo-900/40 dark:text-indigo-300 border-l-[3px] border-[#352f99]' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white' }}"
                    :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                    <i
                        class="fas fa-building w-5 text-center flex-shrink-0 {{ request()->routeIs('company.index') ? 'text-[#352f99] dark:text-indigo-400' : 'text-slate-400' }}"></i>
                    <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Profil Perusahaan</span>
                    <span x-show="sidebarCollapsed"
                        class="hidden lg:block absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-slate-900 rounded-md shadow-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-50"
                        style="display:none;">Profil Perusahaan</span>
                </a>

                <a href="{{ route('users.index') }}"
                    class="group relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                        {{ request()->routeIs('users.index') ? 'bg-[#352f99]/10 text-[#352f99] dark:bg-indigo-900/40 dark:text-indigo-300 border-l-[3px] border-[#352f99]' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white' }}"
                    :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                    <i
                        class="fas fa-user-shield w-5 text-center flex-shrink-0 {{ request()->routeIs('users.index') ? 'text-[#352f99] dark:text-indigo-400' : 'text-slate-400' }}"></i>
                    <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Admin Users</span>
                    <span x-show="sidebarCollapsed"
                        class="hidden lg:block absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-slate-900 rounded-md shadow-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-50"
                        style="display:none;">Admin Users</span>
                </a>

                <a href="{{ route('whatsapp.index') }}"
                    class="group relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                        {{ request()->routeIs('whatsapp.index') ? 'bg-[#352f99]/10 text-[#352f99] dark:bg-indigo-900/40 dark:text-indigo-300 border-l-[3px] border-[#352f99]' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white' }}"
                    :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                    <i
                        class="fab fa-whatsapp w-5 text-center flex-shrink-0 {{ request()->routeIs('whatsapp.index') ? 'text-[#352f99] dark:text-indigo-400' : 'text-slate-400' }}"></i>
                    <span :class="sidebarCollapsed ? 'lg:hidden' : ''">WhatsApp API</span>
                    <span x-show="sidebarCollapsed"
                        class="hidden lg:block absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-slate-900 rounded-md shadow-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-50"
                        style="display:none;">WhatsApp API</span>
                </a>

                @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('mail.index') }}"
                        class="group relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                                    {{ request()->routeIs('mail.index') ? 'bg-[#352f99]/10 text-[#352f99] dark:bg-indigo-900/40 dark:text-indigo-300 border-l-[3px] border-[#352f99]' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white' }}"
                        :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                        <i
                            class="fas fa-mail-bulk w-5 text-center flex-shrink-0 {{ request()->routeIs('mail.index') ? 'text-[#352f99] dark:text-indigo-400' : 'text-slate-400' }}"></i>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">SMTP Setting</span>
                        <span x-show="sidebarCollapsed"
                            class="hidden lg:block absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-slate-900 rounded-md shadow-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-50"
                            style="display:none;">SMTP Setting</span>
                    </a>

                    <a href="{{ route('plans.index') }}"
                        class="group relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                                    {{ request()->routeIs('plans.index') ? 'bg-[#352f99]/10 text-[#352f99] dark:bg-indigo-900/40 dark:text-indigo-300 border-l-[3px] border-[#352f99]' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white' }}"
                        :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                        <i
                            class="fas fa-box-open w-5 text-center flex-shrink-0 {{ request()->routeIs('plans.index') ? 'text-[#352f99] dark:text-indigo-400' : 'text-slate-400' }}"></i>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Manajemen Paket</span>
                        <span x-show="sidebarCollapsed"
                            class="hidden lg:block absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-slate-900 rounded-md shadow-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-50"
                            style="display:none;">Manajemen Paket</span>
                    </a>

                    <a href="{{ route('payment.index') }}"
                        class="group relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                                    {{ request()->routeIs('payment.index') ? 'bg-[#352f99]/10 text-[#352f99] dark:bg-indigo-900/40 dark:text-indigo-300 border-l-[3px] border-[#352f99]' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white' }}"
                        :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                        <i
                            class="fas fa-credit-card w-5 text-center flex-shrink-0 {{ request()->routeIs('payment.index') ? 'text-[#352f99] dark:text-indigo-400' : 'text-slate-400' }}"></i>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Pengaturan Pembayaran</span>
                        <span x-show="sidebarCollapsed"
                            class="hidden lg:block absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-slate-900 rounded-md shadow-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-50"
                            style="display:none;">Pengaturan Pembayaran</span>
                    </a>

                    <a href="{{ route('site.index') }}"
                        class="group relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                                    {{ request()->routeIs('site.index') ? 'bg-[#352f99]/10 text-[#352f99] dark:bg-indigo-900/40 dark:text-indigo-300 border-l-[3px] border-[#352f99]' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white' }}"
                        :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                        <i
                            class="fas fa-cog w-5 text-center flex-shrink-0 {{ request()->routeIs('site.index') ? 'text-[#352f99] dark:text-indigo-400' : 'text-slate-400' }}"></i>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Pengaturan Situs</span>
                        <span x-show="sidebarCollapsed"
                            class="hidden lg:block absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-slate-900 rounded-md shadow-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-50"
                            style="display:none;">Pengaturan Situs</span>
                    </a>

                    <a href="{{ route('system.index') }}"
                        class="group relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                                    {{ request()->routeIs('system.index') ? 'bg-[#352f99]/10 text-[#352f99] dark:bg-indigo-900/40 dark:text-indigo-300 border-l-[3px] border-[#352f99]' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white' }}"
                        :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                        <i
                            class="fas fa-sync w-5 text-center flex-shrink-0 {{ request()->routeIs('system.index') ? 'text-[#352f99] dark:text-indigo-400' : 'text-slate-400' }}"></i>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Update Aplikasi</span>
                        <span x-show="sidebarCollapsed"
                            class="hidden lg:block absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-slate-900 rounded-md shadow-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-50"
                            style="display:none;">Update Aplikasi</span>
                    </a>
                @endif
            @endif

        </div>
    </nav>

    {{-- Sidebar Footer: Collapse Toggle --}}
    <div class="flex-shrink-0 border-t border-slate-200 dark:border-slate-700 p-3">
        {{-- User info (hidden when collapsed) --}}
        <div class="flex items-center gap-3 mb-3" :class="sidebarCollapsed ? 'lg:hidden' : ''">
            <div
                class="h-8 w-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-400 text-xs font-bold flex-shrink-0">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-slate-700 dark:text-slate-200 truncate">{{ auth()->user()->name }}
                </p>
                <p class="text-[10px] text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                    {{ auth()->user()->role }}
                </p>
            </div>
        </div>
        {{-- Collapse/Expand Button (desktop only) --}}
        <button @click="toggleSidebar()" type="button"
            class="hidden lg:flex w-full items-center justify-center gap-2 px-3 py-2 rounded-lg text-xs font-medium text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-700 dark:hover:text-white transition-all duration-200"
            :class="sidebarCollapsed ? 'lg:px-0' : ''">
            <i class="fas transition-transform duration-300"
                :class="sidebarCollapsed ? 'fa-angles-right' : 'fa-angles-left'"></i>
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Tutup Sidebar</span>
        </button>
    </div>
</aside>