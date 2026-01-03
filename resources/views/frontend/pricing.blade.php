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
    class="h-full font-sans antialiased text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-900 transition-all duration-300">

    <div class="min-h-full flex flex-col">
        <!-- Navbar -->
        <nav
            class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-md sticky top-0 z-50 border-b border-slate-200 dark:border-white/10 shadow-sm transition-colors duration-300">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between items-center">
                    <div class="flex items-center gap-2">
                        @if($company && $company->logo_path)
                            <img src="{{ asset('uploads/' . $company->logo_path) }}" alt="Logo" class="h-8 w-auto rounded">
                        @else
                            <div class="bg-primary-600 text-white p-1.5 rounded-lg shadow-sm">
                                <i class="fas fa-wifi text-sm"></i>
                            </div>
                        @endif
                        <span class="text-xl font-bold text-primary-900 dark:text-white tracking-tight">
                            <a href="{{ config('app.url') }}">{{ $company->name ?? 'BillNesia' }}</a>
                        </span>
                    </div>
                    <div class="hidden md:flex items-center gap-8">
                        <a href="{{ route('frontend.index') }}#cek-tagihan"
                            class="text-sm font-medium {{ request()->routeIs('frontend.index') ? 'text-[#352f99] dark:text-white font-bold' : 'text-slate-600 dark:text-slate-300' }} hover:text-[#352f99] transition-colors">Cek
                            Tagihan</a>
                        <a href="{{ route('frontend.pricing') }}"
                            class="text-sm font-medium {{ request()->routeIs('frontend.pricing') ? 'text-[#352f99] dark:text-white font-bold' : 'text-slate-600 dark:text-slate-300' }} hover:text-[#352f99] transition-colors">Harga</a>
                        <a href="{{ route('frontend.about') }}"
                            class="text-sm font-medium {{ request()->routeIs('frontend.about') ? 'text-[#352f99] dark:text-white font-bold' : 'text-slate-600 dark:text-slate-300' }} hover:text-[#352f99] transition-colors">Tentang
                            Kami</a>
                    </div>
                    <div class="flex items-center gap-4">
                        <!-- Theme Toggle -->
                        <button @click="toggleTheme()" type="button"
                            class="rounded-full p-2 text-slate-500 hover:text-[#352f99] dark:text-slate-300 dark:hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-[#352f99]">
                            <i class="fas fa-sun text-lg" x-show="!darkMode"></i>
                            <i class="fas fa-moon text-lg" x-show="darkMode" style="display: none;"></i>
                        </button>

                        <a href="{{ route('register') }}"
                            class="hidden sm:inline-flex items-center justify-center rounded-full bg-white dark:bg-emerald-500/10 px-4 py-2 text-sm font-medium text-slate-700 dark:text-emerald-400 border border-slate-200 dark:border-emerald-500/20 hover:bg-slate-50 dark:hover:bg-emerald-500/20 transition-all">
                            Daftar
                        </a>
                        <a href="{{ route('login') }}"
                            class="group inline-flex items-center justify-center rounded-full bg-[#352f99] px-4 py-2 text-sm font-medium text-white hover:bg-indigo-800 transition-all shadow-md shadow-indigo-500/20">
                            Login Admin <i class="fas fa-arrow-right ml-2 opacity-50 group-hover:opacity-100"></i>
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1">
            <div class="bg-white dark:bg-slate-900 py-24 sm:py-32 transition-colors duration-300 px-6">
                <div class="mx-auto max-w-7xl">
                    <div class="mx-auto max-w-4xl text-center">
                        <h2 class="text-base font-semibold leading-7 text-[#352f99] dark:text-indigo-400">Harga</h2>
                        <p class="mt-2 text-4xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-5xl">
                            Pilih Paket yang Sesuai dengan Kebutuhan Anda</p>
                        <p class="mt-6 text-lg leading-8 text-slate-600 dark:text-slate-400">Solusi terbaik untuk
                            manajemen Mikrotik Router Anda dengan harga yang terjangkau.</p>
                    </div>

                    <div x-data="{ cycle: 'monthly' }" class="mt-16 flex flex-col items-center">
                        <!-- Cycle Selection (Radio Group) -->
                        <div
                            class="inline-flex p-1 bg-slate-100 dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-inner">
                            <button @click="cycle = 'monthly'"
                                :class="cycle === 'monthly' ? 'bg-white dark:bg-[#352f99] shadow-md text-[#352f99] dark:text-white scale-105' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'"
                                class="px-8 py-2.5 rounded-xl text-sm font-bold transition-all duration-300 transform">
                                Bulanan
                            </button>
                            <button @click="cycle = 'semester'"
                                :class="cycle === 'semester' ? 'bg-white dark:bg-[#352f99] shadow-md text-[#352f99] dark:text-white scale-105' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'"
                                class="px-8 py-2.5 rounded-xl text-sm font-bold transition-all duration-300 transform">
                                6 Bulan
                            </button>
                            <button @click="cycle = 'annual'"
                                :class="cycle === 'annual' ? 'bg-white dark:bg-[#352f99] shadow-md text-[#352f99] dark:text-white scale-105' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'"
                                class="px-8 py-2.5 rounded-xl text-sm font-bold transition-all duration-300 transform relative">
                                12 Bulan
                                <span
                                    class="absolute -top-1.5 -right-1.5 bg-green-500 text-white text-[9px] font-black px-2 py-0.5 rounded-full shadow-sm animate-pulse">HEMAT!</span>
                            </button>
                        </div>

                        <!-- Pricing Cards -->
                        <div class="mt-10 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 w-full max-w-7xl">
                            @foreach($plans as $p)
                                <div
                                    class="flex flex-col justify-between rounded-3xl bg-white dark:bg-slate-800 p-8 ring-1 ring-slate-200 dark:ring-slate-700 xl:p-10 hover:ring-[#352f99] dark:hover:ring-[#352f99] transition-all duration-300 shadow-sm hover:shadow-xl">
                                    <div>
                                        <h3 class="text-xl font-bold leading-7 text-slate-900 dark:text-white">
                                            {{ $p->name }}
                                        </h3>
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
                                                <i class="fas fa-check text-emerald-500"></i>
                                                <span>Maks. <strong>{{ $p->max_routers }}</strong> Mikrotik Router</span>
                                            </li>
                                            <li class="flex gap-x-3 items-center">
                                                <i class="fas fa-check text-emerald-500"></i>
                                                <span>Maks. <strong>{{ $p->max_vouchers }}</strong> Voucher Hotspot</span>
                                            </li>
                                            <li class="flex gap-x-3 items-center">
                                                <i class="fas fa-check text-emerald-500"></i>
                                                <span>Maks. <strong>{{ $p->max_customers }}</strong> Pelanggan
                                                    Database</span>
                                            </li>
                                            <li
                                                class="flex gap-x-3 items-center {{ $p->wa_gateway ? '' : 'opacity-50 line-through' }}">
                                                <i
                                                    class="fas {{ $p->wa_gateway ? 'fa-check text-emerald-500' : 'fa-times text-slate-400' }}"></i>
                                                <span>WhatsApp Gateway</span>
                                            </li>
                                            <li
                                                class="flex gap-x-3 items-center {{ $p->customer_support ? '' : 'opacity-50 line-through' }}">
                                                <i
                                                    class="fas {{ $p->customer_support ? 'fa-check text-emerald-500' : 'fa-times text-slate-400' }}"></i>
                                                <span>Layanan Customer Support</span>
                                            </li>
                                        </ul>
                                    </div>
                                    <a href="{{ route('register', ['plan' => $p->id]) }}"
                                        class="mt-8 block rounded-xl bg-[#352f99] px-3 py-4 text-center text-sm font-bold leading-6 text-white shadow-lg shadow-indigo-500/25 hover:bg-indigo-700 hover:-translate-y-1 transition-all">Mulai
                                        Sekarang</a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer
            class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-white/5 mt-auto py-8 transition-colors duration-300">
            <div class="mx-auto max-w-7xl px-6 text-center">
                <div class="flex justify-center gap-6 mb-4">
                    <a href="{{ route('frontend.about') }}" class="text-sm text-slate-500 hover:text-[#352f99]">Tentang
                        Kami</a>
                    <a href="{{ route('frontend.terms') }}" class="text-sm text-slate-500 hover:text-[#352f99]">Syarat &
                        Ketentuan</a>
                </div>
                <p class="text-xs leading-5 text-slate-500 dark:text-indigo-200">&copy; {{ date('Y') }} BillNesia.
                    Developed by Aangwi.</p>
            </div>
        </footer>
    </div>
</body>

</html>