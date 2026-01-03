<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" x-data="{ 
    darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
    toggleTheme() {
        this.darkMode = !this.darkMode;
        localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
        if (this.darkMode) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }
}"
    x-init="$watch('darkMode', val => val ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark')); if(darkMode) document.documentElement.classList.add('dark');">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Harga Paket Langganan - BillNesia</title>
    <link rel="icon" href="{{ $global_favicon ?? asset('favicon.ico') }}">

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind & Alpine -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        primary: {
                            50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc', 400: '#818cf8',
                            500: '#6366f1', 600: '#4f46e5', 700: '#4338ca', 800: '#3730a3', 900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>
</head>

<body
    class="h-full font-sans antialiased text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-900 transition-colors duration-300">

    <div class="min-h-full flex flex-col">
        <!-- Navigation -->
        <nav
            class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200 dark:border-white/5 sticky top-0 z-50">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between items-center">
                    <div class="flex items-center gap-2">
                        <div class="bg-primary-600 p-2 rounded-lg">
                            <i class="fas fa-wifi text-white text-lg"></i>
                        </div>
                        <span class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Bill<span
                                class="text-primary-600">Nesia</span></span>
                    </div>
                    <div class="flex items-center gap-4">
                        <button @click="toggleTheme()"
                            class="p-2 text-slate-500 hover:text-primary-600 transition-colors">
                            <i class="fas" :class="darkMode ? 'fa-sun' : 'fa-moon'"></i>
                        </button>
                        @auth
                            <a href="{{ url('/dashboard') }}"
                                class="text-sm font-semibold text-slate-700 dark:text-slate-200 hover:text-primary-600">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}"
                                class="text-sm font-semibold text-slate-700 dark:text-slate-200 hover:text-primary-600">Log
                                in</a>
                            <a href="{{ route('register') }}"
                                class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-primary-500 transition-all">Daftar</a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1">
            <div class="bg-white dark:bg-slate-900 py-24 sm:py-32">
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <div class="mx-auto max-w-4xl text-center">
                        <h2 class="text-base font-semibold leading-7 text-primary-600 dark:text-primary-400">Harga</h2>
                        <p class="mt-2 text-4xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-5xl">
                            Pilih Paket yang Sesuai dengan Kebutuhan Anda</p>
                        <p class="mt-6 text-lg leading-8 text-slate-600 dark:text-slate-400">Solusi terbaik untuk
                            manajemen Mikrotik Router Anda dengan harga yang terjangkau.</p>
                    </div>

                    <div x-data="{ cycle: 'monthly' }" class="mt-16 flex flex-col items-center">
                        <!-- Cycle Toggle -->
                        <div class="flex items-center gap-x-4">
                            <span :class="cycle === 'monthly' ? 'text-slate-900 dark:text-white' : 'text-slate-500'"
                                class="text-sm font-semibold transition-colors">Bulanan</span>
                            <button
                                @click="cycle = cycle === 'monthly' ? 'semester' : (cycle === 'semester' ? 'annual' : 'monthly')"
                                class="relative flex h-6 w-11 flex-none cursor-pointer rounded-full bg-slate-200 dark:bg-slate-700 p-1 transition-colors duration-200 ease-in-out focus:outline-none ring-1 ring-inset ring-slate-900/5 dark:ring-white/10">
                                <span
                                    :class="cycle === 'monthly' ? 'translate-x-0' : (cycle === 'semester' ? 'translate-x-[1.25rem]' : 'translate-x-[1.25rem]')"
                                    class="h-4 w-4 transform rounded-full bg-white shadow-sm ring-1 ring-slate-900/5 transition duration-200 ease-in-out"></span>
                            </button>
                            <div class="flex flex-col">
                                <span
                                    :class="cycle === 'semester' ? 'text-slate-900 dark:text-white' : 'text-slate-500'"
                                    class="text-sm font-semibold transition-colors">6 Bulan</span>
                                <span :class="cycle === 'annual' ? 'text-slate-900 dark:text-white' : 'text-slate-500'"
                                    class="text-sm font-semibold transition-colors">12 Bulan <span
                                        class="text-green-500 text-[10px] ml-1">Hemat!</span></span>
                            </div>
                        </div>

                        <!-- Pricing Cards -->
                        <div class="mt-10 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 w-full">
                            @foreach($plans as $p)
                                <div
                                    class="flex flex-col justify-between rounded-3xl bg-white dark:bg-slate-800 p-8 ring-1 ring-slate-200 dark:ring-slate-700 xl:p-10 hover:ring-primary-500 dark:hover:ring-primary-500 transition-all duration-300 shadow-sm hover:shadow-xl">
                                    <div>
                                        <h3 class="text-xl font-bold leading-7 text-slate-900 dark:text-white">
                                            {{ $p->name }}</h3>
                                        <div
                                            class="mt-4 flex items-baseline gap-x-2 border-b border-slate-100 dark:border-slate-700 pb-6">
                                            <span class="text-4xl font-bold tracking-tight text-slate-900 dark:text-white"
                                                x-text="'Rp' + (cycle === 'monthly' ? '{{ number_format($p->price_monthly, 0, ',', '.') }}' : (cycle === 'semester' ? '{{ number_format($p->price_semester, 0, ',', '.') }}' : '{{ number_format($p->price_annual, 0, ',', '.') }}'))"></span>
                                            <span class="text-sm font-semibold leading-6 text-slate-500"
                                                x-text="'/' + (cycle === 'monthly' ? 'bln' : (cycle === 'semester' ? '6 bln' : 'thn'))"></span>
                                        </div>
                                        <ul role="list"
                                            class="mt-8 space-y-3 text-sm leading-6 text-slate-600 dark:text-slate-400">
                                            <li class="flex gap-x-3 items-center">
                                                <i class="fas fa-check text-primary-500"></i>
                                                <span>Maks. <strong>{{ $p->max_routers }}</strong> Mikrotik Router</span>
                                            </li>
                                            <li class="flex gap-x-3 items-center">
                                                <i class="fas fa-check text-primary-500"></i>
                                                <span>Maks. <strong>{{ $p->max_vouchers }}</strong> Voucher Hotspot</span>
                                            </li>
                                            <li class="flex gap-x-3 items-center">
                                                <i class="fas fa-check text-primary-500"></i>
                                                <span>Maks. <strong>{{ $p->max_customers }}</strong> Pelanggan
                                                    Database</span>
                                            </li>
                                            <li
                                                class="flex gap-x-3 items-center {{ $p->wa_gateway ? '' : 'opacity-50 line-through' }}">
                                                <i
                                                    class="fas {{ $p->wa_gateway ? 'fa-check text-primary-500' : 'fa-times text-slate-400' }}"></i>
                                                <span>WhatsApp Gateway</span>
                                            </li>
                                            <li
                                                class="flex gap-x-3 items-center {{ $p->customer_support ? '' : 'opacity-50 line-through' }}">
                                                <i
                                                    class="fas {{ $p->customer_support ? 'fa-check text-primary-500' : 'fa-times text-slate-400' }}"></i>
                                                <span>Layanan Customer Support</span>
                                            </li>
                                        </ul>
                                    </div>
                                    <a href="{{ route('register', ['plan' => $p->id]) }}"
                                        class="mt-8 block rounded-xl bg-primary-600 px-3 py-4 text-center text-sm font-bold leading-6 text-white shadow-lg shadow-primary-500/25 hover:bg-primary-500 hover:-translate-y-1 transition-all">Mulai
                                        Sekarang</a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-white/5 mt-auto py-8">
            <div class="mx-auto max-w-7xl px-6 text-center">
                <div class="flex justify-center gap-6 mb-4">
                    <a href="{{ route('frontend.about') }}"
                        class="text-sm text-slate-500 hover:text-primary-600 transition-colors">Tentang Kami</a>
                    <a href="{{ route('frontend.terms') }}"
                        class="text-sm text-slate-500 hover:text-primary-600 transition-colors">Syarat & Ketentuan</a>
                </div>
                <p class="text-xs leading-5 text-slate-500 dark:text-indigo-200">&copy; {{ date('Y') }} BillNesia. All
                    rights reserved.</p>
            </div>
        </footer>
    </div>
</body>

</html>
bitumen