<nav x-data="{ mobileMenuOpen: false }"
    class="bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-50 border-b border-slate-200 dark:border-slate-700 transition-colors duration-300">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
            <div class="flex">
                <div class="flex flex-shrink-0 items-center">
                    @if(isset($company) && (!empty($company->logo_path) || !empty($company->company_name)))
                        <a href="{{ route('pppoe.dashboard') }}" class="flex items-center gap-2 group">
                            @if(!empty($company->logo_path))
                                <img src="{{ asset('uploads/' . $company->logo_path) }}" alt="Logo" class="h-10 w-auto rounded-lg">
                            @else
                                <div class="bg-[#352f99] text-white p-1.5 rounded-lg shadow-md group-hover:bg-indigo-700 transition">
                                    <i class="fas fa-building"></i>
                                </div>
                            @endif
                            <span class="text-xl font-bold text-slate-800 dark:text-white tracking-tight group-hover:text-[#352f99] transition">
                                {{ $company->company_name ?? 'MikBill' }}
                            </span>
                        </a>
                    @else
                        <!-- Default Branding -->
                        <a href="{{ route('pppoe.dashboard') }}" class="flex items-center gap-2 group">
                            <div class="bg-[#352f99] text-white p-1.5 rounded-lg shadow-md group-hover:bg-indigo-700 transition">
                                <i class="fas fa-wifi"></i>
                            </div>
                            <span class="text-xl font-bold text-slate-800 dark:text-white tracking-tight group-hover:text-[#352f99] transition">MikBill</span>
                        </a>
                    @endif
                </div>
                <div class="hidden sm:ml-8 sm:flex sm:space-x-1">
                    <!-- Dashboard -->
                    <a href="{{ route('pppoe.dashboard') }}"
                        class="{{ request()->routeIs('pppoe.dashboard') ? 'border-[#352f99] text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium transition-colors duration-200">
                        <i
                            class="fas fa-tachometer-alt mr-2 {{ request()->routeIs('pppoe.dashboard') ? 'text-[#352f99]' : 'text-gray-400' }}"></i>
                        PPPoE
                    </a>

                    <!-- Peta -->
                    <a href="{{ route('maps.index') }}"
                        class="{{ request()->routeIs('maps.index') ? 'border-[#352f99] text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium transition-colors duration-200">
                        <i
                            class="fas fa-map-marked-alt mr-2 {{ request()->routeIs('maps.index') ? 'text-[#352f99]' : 'text-gray-400' }}"></i>
                        Maps
                    </a>
                    @if(auth()->user()->role == 'admin')
                    <a href="{{ route('plans.public') }}"
                        class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-[#352f99]">
                        <i class="fas fa-box mr-3 text-gray-400 group-hover:text-[#352f99]"></i>
                        Plans
                    </a>
                    @endif
                    <!-- Billing (Operator & Superadmin) -->
                    @if(auth()->user()->role == 'operator' || auth()->user()->isSuperAdmin())
                        <a href="{{ route('billing.index') }}"
                            class="{{ request()->routeIs('billing.index') ? 'border-[#352f99] text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium transition-colors duration-200">
                            <i
                                class="fas fa-file-invoice-dollar mr-2 {{ request()->routeIs('billing.index') ? 'text-[#352f99]' : 'text-gray-400' }}"></i>
                            Tagihan
                        </a>
                    @endif

                    <!-- Customers (Operator & Superadmin) -->
                    @if(auth()->user()->role == 'operator' || auth()->user()->isSuperAdmin())
                        <a href="{{ route('customers.index') }}"
                            class="{{ request()->routeIs('customers.index') ? 'border-[#352f99] text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium transition-colors duration-200">
                            <i
                                class="fas fa-users mr-2 {{ request()->routeIs('customers.index') ? 'text-[#352f99]' : 'text-gray-400' }}"></i>
                            Pelanggan
                        </a>
                    @endif

                    <!-- Hotspot (Admin & Operator) -->
                    @if(auth()->user()->isAdmin() || auth()->user()->role == 'operator' || auth()->user()->isSuperAdmin())
                        <div class="relative ml-2 flex items-center" x-data="{ open: false }">
                            <button @click="open = !open" @click.away="open = false" type="button"
                                class="{{ request()->routeIs('hotspot.*') ? 'border-[#352f99] text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium transition-colors duration-200 focus:outline-none"
                                aria-expanded="false">
                                <i class="fas fa-wifi mr-2 {{ request()->routeIs('hotspot.*') ? 'text-[#352f99]' : 'text-gray-400' }}"></i>
                                <span>Hotspot</span>
                                <i class="fas fa-chevron-down ml-2 h-3 w-3 text-gray-400 transition-transform duration-200"
                                    :class="{ 'rotate-180': open }"></i>
                            </button>

                            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 translate-y-1" style="display: none;"
                                class="absolute left-0 top-full z-10 mt-3 w-56 max-w-xs transform px-2 sm:px-0 lg:ml-0 lg:left-1/2 lg:-translate-x-1/2">
                                <div class="overflow-hidden rounded-lg shadow-lg ring-1 ring-black ring-opacity-5">
                                    <div class="bg-white dark:bg-slate-800 py-1">
                                        <a href="{{ route('hotspot.monitor') }}"
                                            class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-[#352f99]">
                                            <i class="fas fa-desktop mr-3 text-gray-400 group-hover:text-[#352f99]"></i>
                                            Monitor Hotspot
                                        </a>
                                        <a href="{{ route('hotspot.generate') }}"
                                            class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-[#352f99]">
                                            <i class="fas fa-user-plus mr-3 text-gray-400 group-hover:text-[#352f99]"></i>
                                            Generate Akun
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Monitor (Admin & Operator) -->
                    @if(auth()->user()->isAdmin() || auth()->user()->role == 'operator' || auth()->user()->isSuperAdmin())
                        <div class="relative ml-2 flex items-center" x-data="{ open: false }">
                            <button @click="open = !open" @click.away="open = false" type="button"
                                class="{{ request()->routeIs('monitor.*') ? 'border-[#352f99] text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium transition-colors duration-200 focus:outline-none"
                                aria-expanded="false">
                                <i class="fas fa-desktop mr-2 {{ request()->routeIs('monitor.*') ? 'text-[#352f99]' : 'text-gray-400' }}"></i>
                                <span>Monitor</span>
                                <i class="fas fa-chevron-down ml-2 h-3 w-3 text-gray-400 transition-transform duration-200"
                                    :class="{ 'rotate-180': open }"></i>
                            </button>

                            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 translate-y-1" style="display: none;"
                                class="absolute left-0 top-full z-10 mt-3 w-56 max-w-xs transform px-2 sm:px-0 lg:ml-0 lg:left-1/2 lg:-translate-x-1/2">
                                <div class="overflow-hidden rounded-lg shadow-lg ring-1 ring-black ring-opacity-5">
                                    <div class="bg-white dark:bg-slate-800 py-1">
                                        <a href="{{ route('monitor.dhcp-leases') }}"
                                            class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-[#352f99]">
                                            <i class="fas fa-network-wired mr-3 text-gray-400 group-hover:text-[#352f99]"></i>
                                            DHCP Leases
                                        </a>
                                        <a href="{{ route('monitor.static-users') }}"
                                            class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-[#352f99]">
                                            <i class="fas fa-user-tag mr-3 text-gray-400 group-hover:text-[#352f99]"></i>
                                            Static Users
                                        </a>
                                        <a href="{{ route('monitor.simple-queues') }}"
                                            class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-[#352f99]">
                                            <i class="fas fa-stream mr-3 text-gray-400 group-hover:text-[#352f99]"></i>
                                            Simple Queues
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Traffic (Admin & Superadmin) -->
                    @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                        <a href="{{ route('traffic.index') }}"
                            class="{{ request()->routeIs('traffic.index') ? 'border-[#352f99] text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium transition-colors duration-200">
                            <i
                                class="fas fa-chart-area mr-2 {{ request()->routeIs('traffic.index') ? 'text-[#352f99]' : 'text-gray-400' }}"></i>
                            Traffic
                        </a>



                        <!-- Dropdown Manajemen -->
                        <div class="relative ml-3 flex items-center" x-data="{ open: false }">
                            <button @click="open = !open" @click.away="open = false" type="button"
                                class="group inline-flex items-center rounded-md bg-white dark:bg-slate-800 text-sm font-medium text-gray-500 dark:text-slate-300 hover:text-gray-700 dark:hover:text-white focus:outline-none focus:ring-2 focus:ring-[#352f99] focus:ring-offset-2 dark:focus:ring-offset-slate-800"
                                aria-expanded="false">
                                <span>Manajemen</span>
                                <i class="fas fa-chevron-down ml-2 h-4 w-4 text-gray-400 group-hover:text-gray-500 transition-transform duration-200"
                                    :class="{ 'rotate-180': open }"></i>
                            </button>

                            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 translate-y-1" style="display: none;"
                                class="absolute left-0 top-full z-10 mt-3 w-56 max-w-xs transform px-2 sm:px-0 lg:ml-0 lg:left-1/2 lg:-translate-x-1/2">
                                <div class="overflow-hidden rounded-lg shadow-lg ring-1 ring-black ring-opacity-5">
                                    <div class="bg-white dark:bg-slate-800 py-1">
                                        <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                            Pelanggan & Kasir
                                        </div>
                                        <a href="{{ route('customers.index') }}"
                                            class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-[#352f99]">
                                            <i class="fas fa-users mr-3 text-gray-400 group-hover:text-[#352f99]"></i>
                                            Data Pelanggan
                                        </a>
                                        <a href="{{ route('billing.index') }}"
                                            class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-[#352f99]">
                                            <i
                                                class="fas fa-file-invoice-dollar mr-3 text-gray-400 group-hover:text-[#352f99]"></i>
                                            Tagihan (Billing)
                                        </a>
                                        <a href="{{ route('accounting.index') }}"
                                            class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-[#352f99]">
                                            <i class="fas fa-money-bill-wave mr-3 text-gray-400 group-hover:text-[#352f99]"></i>
                                            Biaya & Pengeluaran
                                        </a>
                                        <a href="{{ route('report.index') }}"
                                            class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-[#352f99]">
                                            <i class="fas fa-print mr-3 text-gray-400 group-hover:text-[#352f99]"></i>
                                            Laporan Keuangan
                                        </a>

                                        <div class="border-t border-gray-100 my-1"></div>

                                        <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                            Sistem
                                        </div>
                                            <a href="{{ route('router.index') }}"
                                                class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-[#352f99]">
                                                <i
                                                    class="fas fa-network-wired mr-3 text-gray-400 group-hover:text-[#352f99]"></i>
                                                Router Mikrotik
                                            </a>
                                        <a href="{{ route('company.index') }}"
                                            class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-[#352f99]">
                                            <i class="fas fa-building mr-3 text-gray-400 group-hover:text-[#352f99]"></i>
                                            Profil Perusahaan
                                        </a>
                                        <a href="{{ route('users.index') }}"
                                            class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-[#352f99]">
                                            <i class="fas fa-user-shield mr-3 text-gray-400 group-hover:text-[#352f99]"></i>
                                            Admin Users
                                        </a>
                                        <a href="{{ route('whatsapp.index') }}"
                                            class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-[#352f99]">
                                            <i class="fab fa-whatsapp mr-3 text-gray-400 group-hover:text-[#352f99]"></i>
                                            WhatsApp API
                                        </a>
                                        @if(auth()->user()->isSuperAdmin())
                                            <a href="{{ route('mail.index') }}"
                                                class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-[#352f99]">
                                                <i class="fas fa-mail-bulk mr-3 text-gray-400 group-hover:text-[#352f99]"></i>
                                                SMTP Setting
                                            </a>
                                            <div class="border-t border-gray-100 my-1"></div>
                                            <a href="{{ route('plans.index') }}"
                                                class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-[#352f99]">
                                                <i class="fas fa-box-open mr-3 text-gray-400 group-hover:text-[#352f99]"></i>
                                                Manajemen Paket
                                            </a>
                                            <a href="{{ route('payment.index') }}"
                                                class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-[#352f99]">
                                                <i class="fas fa-credit-card mr-3 text-gray-400 group-hover:text-[#352f99]"></i>
                                                Pengaturan Pembayaran
                                            </a>
                                            <a href="{{ route('site.index') }}"
                                                class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-[#352f99]">
                                                <i class="fas fa-cog mr-3 text-gray-400 group-hover:text-[#352f99]"></i>
                                                Pengaturan Situs
                                            </a>
                                            <a href="{{ route('system.index') }}"
                                                class="group flex items-center px-4 py-2 text-sm text-[#352f99] bg-indigo-50 hover:bg-indigo-100">
                                                <i class="fas fa-sync mr-3 text-[#352f99]"></i> Update Aplikasi
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Profile Dropdown (Right Side) -->
            <div class="hidden sm:ml-6 sm:flex sm:items-center">
                <!-- Notification Bell (Superadmin Only) -->
                @if(auth()->user()->isSuperAdmin())
                    <div class="relative mr-2" x-data="{ 
                        notifOpen: false, 
                        notifs: [], 
                        unreadCount: 0,
                        async fetchNotifs() {
                            const res = await fetch('{{ route('notifications.index') }}');
                            this.notifs = await res.json();
                            this.unreadCount = this.notifs.length;
                        },
                        async markRead() {
                            await fetch('{{ route('notifications.markRead') }}', {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                            });
                            this.unreadCount = 0;
                        }
                    }" x-init="fetchNotifs()">
                        <button @click="notifOpen = !notifOpen; if(notifOpen) markRead()" type="button"
                            class="rounded-full p-2 text-slate-400 hover:text-slate-500 dark:text-slate-200 dark:hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-[#352f99]">
                            <span class="sr-only">View notifications</span>
                            <div class="relative">
                                <i class="fas fa-bell text-lg"></i>
                                <template x-if="unreadCount > 0">
                                    <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white ring-2 ring-white dark:ring-slate-800" x-text="unreadCount"></span>
                                </template>
                            </div>
                        </button>

                        <div x-show="notifOpen" @click.away="notifOpen = false" 
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 z-50 mt-2 w-80 origin-top-right rounded-xl bg-white dark:bg-slate-800 py-2 shadow-2xl ring-1 ring-black ring-opacity-5 focus:outline-none overflow-hidden" style="display: none;">
                            <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-900/50">
                                <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Permintaan Pending</h3>
                                <span class="text-[10px] text-slate-500" x-text="notifs.length + ' Pesan'"></span>
                            </div>
                            <div class="max-h-96 overflow-y-auto">
                                <template x-if="notifs.length === 0">
                                    <div class="p-8 text-center">
                                        <i class="fas fa-check-circle text-slate-300 dark:text-slate-600 text-3xl mb-3"></i>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Tidak ada permintaan baru.</p>
                                    </div>
                                </template>
                                <template x-for="n in notifs" :key="n.id">
                                    <a :href="n.data.action_url" class="block px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors border-b border-slate-50 dark:border-slate-700/50 last:border-0">
                                        <div class="flex items-start gap-3">
                                            <div class="flex-shrink-0 mt-0.5">
                                                <template x-if="n.data.type === 'registration'">
                                                    <div class="h-8 w-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                                                        <i class="fas fa-user-plus text-xs"></i>
                                                    </div>
                                                </template>
                                                <template x-if="n.data.type === 'router_activation'">
                                                    <div class="h-8 w-8 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400">
                                                        <i class="fas fa-microchip text-xs"></i>
                                                    </div>
                                                </template>
                                                <template x-if="n.data.type === 'password_reset'">
                                                    <div class="h-8 w-8 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center text-rose-600 dark:text-rose-400">
                                                        <i class="fas fa-key text-xs"></i>
                                                    </div>
                                                </template>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs font-bold text-slate-900 dark:text-white truncate" x-text="n.data.user_name"></p>
                                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5" x-text="n.data.message"></p>
                                                <p class="text-[9px] text-primary-500 font-bold mt-2 uppercase flex items-center">
                                                    Klik untuk proses <i class="fas fa-chevron-right ml-1 text-[7px]"></i>
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Dark Mode Toggle -->
                <button @click="toggleTheme()" type="button"
                    class="rounded-full p-2 text-slate-400 hover:text-slate-500 dark:text-slate-200 dark:hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-[#352f99] mr-2">
                    <i class="fas fa-sun text-lg" x-show="!darkMode"></i>
                    <i class="fas fa-moon text-lg" x-show="darkMode" style="display: none;"></i>
                </button>
                <div class="relative ml-3" x-data="{ open: false }">
                    <div>
                        <button @click="open = !open" @click.away="open = false" type="button"
                            class="flex items-center max-w-xs rounded-full bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#352f99] focus:ring-offset-2 dark:focus:ring-offset-slate-800"
                            id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                            <span class="sr-only">Open user menu</span>
                            <div class="flex flex-col text-right mr-3 hidden lg:block">
                                <span
                                    class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ auth()->user()->name }}</span>
                                <span
                                    class="text-[10px] uppercase text-slate-500 tracking-wider bg-slate-100 px-1.5 py-0.5 rounded-sm inline-block w-fit ml-auto">{{ auth()->user()->role }}</span>
                            </div>
                            <span
                                class="h-9 w-9 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-400">
                                <i class="fas fa-user"></i>
                            </span>
                        </button>
                    </div>

                    <div x-show="open" x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95" style="display: none;"
                        class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white dark:bg-slate-800 py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="w-full text-left block px-4 py-2 text-sm text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-red-600">
                                <i class="fas fa-sign-out-alt mr-2"></i> Log out
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Mobile menu button -->
            <div class="-mr-2 flex items-center sm:hidden">
                <span
                    class="mr-2 text-xs font-medium text-slate-500 bg-slate-100 px-2 py-1 rounded">{{ auth()->user()->role }}</span>
                <button @click="mobileMenuOpen = !mobileMenuOpen" type="button"
                    class="inline-flex items-center justify-center rounded-md bg-white dark:bg-slate-800 p-2 text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-700 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-[#352f99] focus:ring-offset-2 dark:focus:ring-offset-slate-800">
                    <span class="sr-only">Open main menu</span>
                    <i class="fas fa-bars" x-show="!mobileMenuOpen"></i>
                    <i class="fas fa-times" x-show="mobileMenuOpen" style="display: none;"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Drawer -->
    <div x-show="mobileMenuOpen" style="display: none;"
        class="sm:hidden border-t border-gray-100 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-lg absolute w-full left-0 z-50">
        <div class="space-y-1 pb-3 pt-2">
            <a href="{{ route('pppoe.dashboard') }}"
                class="block border-l-4 {{ request()->routeIs('pppoe.dashboard') ? 'border-[#352f99] bg-indigo-50 dark:bg-indigo-900/50 text-[#352f99] dark:text-indigo-300' : 'border-transparent text-gray-600 dark:text-slate-400 hover:border-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 hover:text-gray-800 dark:hover:text-white' }} py-2 pl-3 pr-4 text-base font-medium">
                <i class="fas fa-tachometer-alt w-6 text-center"></i> Dashboard
            </a>
            <a href="{{ route('maps.index') }}"
                class="block border-l-4 {{ request()->routeIs('maps.index') ? 'border-[#352f99] bg-indigo-50 text-[#352f99]' : 'border-transparent text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800' }} py-2 pl-3 pr-4 text-base font-medium">
                <i class="fas fa-map-marked-alt w-6 text-center"></i> Peta
            </a>
            @if(auth()->user()->role == 'operator' || auth()->user()->isSuperAdmin())
                <a href="{{ route('customers.index') }}"
                    class="block border-l-4 {{ request()->routeIs('customers.index') ? 'border-[#352f99] bg-indigo-50 text-[#352f99]' : 'border-transparent text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800' }} py-2 pl-3 pr-4 text-base font-medium">
                    <i class="fas fa-users w-6 text-center"></i> Pelanggan
                </a>
                <a href="{{ route('billing.index') }}"
                    class="block border-l-4 {{ request()->routeIs('billing.index') ? 'border-[#352f99] bg-indigo-50 text-[#352f99]' : 'border-transparent text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800' }} py-2 pl-3 pr-4 text-base font-medium">
                    <i class="fas fa-file-invoice-dollar w-6 text-center"></i> Tagihan
                </a>
            @endif

            @if(auth()->user()->isAdmin() || auth()->user()->role == 'operator' || auth()->user()->isSuperAdmin())
                <div class="border-t border-gray-100 mt-2 pt-2">
                    <div class="px-3 py-1 text-xs font-bold text-gray-400 uppercase">Hotspot</div>
                    <a href="{{ route('hotspot.monitor') }}"
                        class="block py-2 pl-3 pr-4 text-base font-medium {{ request()->routeIs('hotspot.monitor') ? 'bg-indigo-50 text-[#352f99]' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-800' }}">
                        <i class="fas fa-desktop w-6 text-center"></i> Monitor Hotspot
                    </a>
                    <a href="{{ route('hotspot.generate') }}"
                        class="block py-2 pl-3 pr-4 text-base font-medium {{ request()->routeIs('hotspot.generate') ? 'bg-indigo-50 text-[#352f99]' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-800' }}">
                        <i class="fas fa-user-plus w-6 text-center"></i> Generate Akun
                    </a>
                    <div class="border-t border-gray-100 mt-2 pt-2">
                        <div class="px-3 py-1 text-xs font-bold text-gray-400 uppercase">Monitoring</div>
                    <a href="{{ route('monitor.dhcp-leases') }}"
                        class="block py-2 pl-3 pr-4 text-base font-medium {{ request()->routeIs('monitor.dhcp-leases') ? 'bg-indigo-50 text-[#352f99]' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-800' }}">
                        <i class="fas fa-network-wired w-6 text-center"></i> DHCP Leases
                    </a>
                    <a href="{{ route('monitor.static-users') }}"
                        class="block py-2 pl-3 pr-4 text-base font-medium {{ request()->routeIs('monitor.static-users') ? 'bg-indigo-50 text-[#352f99]' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-800' }}">
                        <i class="fas fa-user-tag w-6 text-center"></i> Static Users
                    </a>
                    <a href="{{ route('monitor.simple-queues') }}"
                        class="block py-2 pl-3 pr-4 text-base font-medium {{ request()->routeIs('monitor.simple-queues') ? 'bg-indigo-50 text-[#352f99]' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-800' }}">
                        <i class="fas fa-stream w-6 text-center"></i> Simple Queues
                    </a>
                </div>
            @endif

            @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                <div class="border-t border-gray-100 mt-2 pt-2">
                    <div class="px-3 py-1 text-xs font-bold text-gray-400 uppercase">Administrasi</div>
                    <a href="{{ route('traffic.index') }}"
                        class="block py-2 pl-3 pr-4 text-base font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-800">
                        <i class="fas fa-chart-area w-6 text-center"></i> Traffic
                    </a>
                    <a href="{{ route('customers.index') }}"
                        class="block py-2 pl-3 pr-4 text-base font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-800">
                        <i class="fas fa-users w-6 text-center"></i> Pelanggan
                    </a>
                    <a href="{{ route('billing.index') }}"
                        class="block py-2 pl-3 pr-4 text-base font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-800">
                        <i class="fas fa-file-invoice-dollar w-6 text-center"></i> Tagihan
                    </a>
                    <a href="{{ route('accounting.index') }}"
                        class="block py-2 pl-3 pr-4 text-base font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-800">
                        <i class="fas fa-money-bill-wave w-6 text-center"></i> Pengeluaran
                    </a>
                    <a href="{{ route('report.index') }}"
                        class="block py-2 pl-3 pr-4 text-base font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-800">
                        <i class="fas fa-print w-6 text-center"></i> Laporan
                    </a>
                </div>

                <div class="border-t border-gray-100 mt-2 pt-2">
                    <div class="px-3 py-1 text-xs font-bold text-gray-400 uppercase">System</div>
                    <a href="{{ route('router.index') }}"
                        class="block py-2 pl-3 pr-4 text-base font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-800">
                        <i class="fas fa-network-wired w-6 text-center"></i> Router
                    </a>
                    <a href="{{ route('plans.public') }}"
                        class="block py-2 pl-3 pr-4 text-base font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-800">
                        <i class="fas fa-box w-6 text-center"></i> Paket Plan
                    </a>
                    <a href="{{ route('company.index') }}"
                        class="block py-2 pl-3 pr-4 text-base font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-800">
                        <i class="fas fa-building w-6 text-center"></i> Perusahaan
                    </a>
                    <a href="{{ route('users.index') }}"
                        class="block py-2 pl-3 pr-4 text-base font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-800">
                        <i class="fas fa-user-shield w-6 text-center"></i> User Admin
                    </a>
                    <a href="{{ route('whatsapp.index') }}"
                        class="block py-2 pl-3 pr-4 text-base font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-800">
                        <i class="fab fa-whatsapp w-6 text-center"></i> WhatsApp
                    </a>
                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('mail.index') }}"
                            class="block py-2 pl-3 pr-4 text-base font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-800">
                            <i class="fas fa-mail-bulk w-6 text-center"></i> Email
                        </a>
                        <a href="{{ route('plans.index') }}"
                            class="block py-2 pl-3 pr-4 text-base font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-800">
                            <i class="fas fa-box-open w-6 text-center"></i> Manajemen Paket
                        </a>
                        <a href="{{ route('system.index') }}"
                            class="block py-2 pl-3 pr-4 text-base font-medium text-[#352f99] bg-indigo-50">
                            <i class="fas fa-sync w-6 text-center"></i> Update App
                        </a>
                    @endif
                </div>
            @endif
        </div>
        <div class="border-t border-gray-200 dark:border-slate-700 pb-3 pt-4 bg-gray-50 dark:bg-slate-900">
            <div class="flex items-center px-4">
                <div class="flex-shrink-0">
                    <div
                        class="h-10 w-10 rounded-full bg-white dark:bg-slate-700 flex items-center justify-center border border-gray-300 dark:border-slate-600 text-gray-400 font-bold text-xl">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                </div>
                <div class="ml-3">
                    <div class="text-base font-medium text-gray-800 dark:text-white">{{ auth()->user()->name }}</div>
                    <div class="text-sm font-medium text-gray-500 dark:text-slate-400">{{ auth()->user()->email }}</div>
                </div>
            </div>
            <div class="mt-3 space-y-1">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="block w-full text-left px-4 py-2 text-base font-medium text-gray-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-800 hover:text-gray-800 dark:hover:text-white">
                        <i class="fas fa-sign-out-alt mr-2"></i> Log out
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>