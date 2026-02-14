<nav class="bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-50 border-b border-slate-200 dark:border-slate-700 transition-all duration-300"
    :class="sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64'">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
            <div class="flex items-center gap-3">
                {{-- Hamburger (mobile + tablet) --}}
                <button @click.stop="sidebarOpen = !sidebarOpen" type="button"
                    class="lg:hidden inline-flex items-center justify-center rounded-md p-2 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-500 dark:hover:text-white focus:outline-none focus:ring-2 focus:ring-[#352f99] transition-colors">
                    <span class="sr-only">Open sidebar</span>
                    <i class="fas fa-bars text-lg"></i>
                </button>

                {{-- Company Logo & Name --}}
                @if(isset($company) && (!empty($company->logo_path) || !empty($company->company_name)))
                    <a href="{{ route('pppoe.dashboard') }}" class="flex items-center gap-2 group ml-[5%]"
                        style="margin-left: 5%;">
                        @if(!empty($company->logo_path))
                            <img src="{{ asset('uploads/' . $company->logo_path) }}" alt="Logo" class="h-9 w-auto rounded-lg">
                        @else
                            <div
                                class="bg-[#352f99] text-white p-1.5 rounded-lg shadow-md group-hover:bg-indigo-700 transition">
                                <i class="fas fa-building"></i>
                            </div>
                        @endif
                        <span
                            class="text-lg font-bold text-slate-800 dark:text-white tracking-tight group-hover:text-[#352f99] transition">
                            {{ $company->company_name ?? 'MikBill' }}
                        </span>
                    </a>
                @else
                    <a href="{{ route('pppoe.dashboard') }}" class="flex items-center gap-2 group ml-[5%]"
                        style="margin-left: 5%;">
                        <div
                            class="bg-[#352f99] text-white p-1.5 rounded-lg shadow-md group-hover:bg-indigo-700 transition">
                            <i class="fas fa-wifi"></i>
                        </div>
                        <span
                            class="text-lg font-bold text-slate-800 dark:text-white tracking-tight group-hover:text-[#352f99] transition">MikBill</span>
                    </a>
                @endif
            </div>

            {{-- Right Side: Notifications, Dark Mode, Profile --}}
            <div class="flex items-center gap-1">
                {{-- Notification Bell (Superadmin Only) --}}
                @if(auth()->user()->isSuperAdmin())
                    <div class="relative" x-data="{ 
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
                                    <span
                                        class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white ring-2 ring-white dark:ring-slate-800"
                                        x-text="unreadCount"></span>
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
                            class="absolute right-0 z-50 mt-2 w-80 origin-top-right rounded-xl bg-white dark:bg-slate-800 py-2 shadow-2xl ring-1 ring-black ring-opacity-5 focus:outline-none overflow-hidden"
                            style="display: none;">
                            <div
                                class="px-4 py-2 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-900/50">
                                <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                    Permintaan Pending</h3>
                                <span class="text-[10px] text-slate-500" x-text="notifs.length + ' Pesan'"></span>
                            </div>
                            <div class="max-h-96 overflow-y-auto">
                                <template x-if="notifs.length === 0">
                                    <div class="p-8 text-center">
                                        <i class="fas fa-check-circle text-slate-300 dark:text-slate-600 text-3xl mb-3"></i>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Tidak ada
                                            permintaan baru.</p>
                                    </div>
                                </template>
                                <template x-for="n in notifs" :key="n.id">
                                    <a :href="n.data.action_url"
                                        class="block px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors border-b border-slate-50 dark:border-slate-700/50 last:border-0">
                                        <div class="flex items-start gap-3">
                                            <div class="flex-shrink-0 mt-0.5">
                                                <template x-if="n.data.type === 'registration'">
                                                    <div
                                                        class="h-8 w-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                                                        <i class="fas fa-user-plus text-xs"></i>
                                                    </div>
                                                </template>
                                                <template x-if="n.data.type === 'router_activation'">
                                                    <div
                                                        class="h-8 w-8 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400">
                                                        <i class="fas fa-microchip text-xs"></i>
                                                    </div>
                                                </template>
                                                <template x-if="n.data.type === 'password_reset'">
                                                    <div
                                                        class="h-8 w-8 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center text-rose-600 dark:text-rose-400">
                                                        <i class="fas fa-key text-xs"></i>
                                                    </div>
                                                </template>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs font-bold text-slate-900 dark:text-white truncate"
                                                    x-text="n.data.user_name"></p>
                                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5"
                                                    x-text="n.data.message"></p>
                                                <p
                                                    class="text-[9px] text-primary-500 font-bold mt-2 uppercase flex items-center">
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

                {{-- Dark Mode Toggle --}}
                <button @click="toggleTheme()" type="button"
                    class="rounded-full p-2 text-slate-400 hover:text-slate-500 dark:text-slate-200 dark:hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-[#352f99]">
                    <i class="fas fa-sun text-lg" x-show="!darkMode"></i>
                    <i class="fas fa-moon text-lg" x-show="darkMode" style="display: none;"></i>
                </button>

                {{-- Profile Dropdown --}}
                <div class="relative ml-2" x-data="{ open: false }">
                    <div>
                        <button @click="open = !open" @click.away="open = false" type="button"
                            class="flex items-center max-w-xs rounded-full bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#352f99] focus:ring-offset-2 dark:focus:ring-offset-slate-800"
                            id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                            <span class="sr-only">Open user menu</span>
                            <div class="flex flex-col text-right mr-3 hidden lg:block">
                                <span
                                    class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ auth()->user()->name }}</span>
                                <span
                                    class="text-[10px] uppercase text-slate-500 tracking-wider bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded-sm inline-block w-fit ml-auto">{{ auth()->user()->role }}</span>
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
        </div>
    </div>
</nav>