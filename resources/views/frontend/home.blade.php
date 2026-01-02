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
    <title>Cek Tagihan Internet - BillNesia</title>
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

    <!-- Navbar -->
    <nav
        class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-md sticky top-0 z-50 border-b border-slate-200 dark:border-white/10 shadow-sm transition-colors duration-300">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 justify-between items-center">
                <div class="flex items-center gap-2">
                    <div class="bg-[#352f99] text-white p-1.5 rounded-lg shadow-sm">
                        <i class="fas fa-wifi text-sm"></i>
                    </div>
                    <span class="text-xl font-bold text-[#352f99] dark:text-white tracking-tight"><a href="{{ config('app.url') }}">BillNesia</a></span>
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
                        class="group inline-flex items-center justify-center rounded-full bg-[#352f99] px-4 py-2 text-sm font-medium text-white hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all shadow-md shadow-indigo-500/20">
                        Login Admin <i class="fas fa-arrow-right ml-2 opacity-50 group-hover:opacity-100"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div
        class="relative isolate overflow-hidden bg-slate-50 dark:bg-[#352f99] py-16 sm:py-24 transition-colors duration-300">
        <!-- Background Pattern -->
        <div class="absolute inset-0 -z-10 h-full w-full object-cover">
            <!-- Light Mode Gradient -->
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-50 to-white dark:hidden opacity-80"></div>
            <!-- Dark Mode Gradient -->
            <div
                class="absolute inset-0 bg-gradient-to-br from-[#352f99] to-indigo-900 mix-blend-multiply opacity-90 hidden dark:block">
            </div>

            <svg viewBox="0 0 1097 845" aria-hidden="true"
                class="hidden transform-gpu blur-3xl sm:block opacity-20 dark:opacity-40 absolute top-[10%] left-[50%] -translate-x-1/2 w-[68.5625rem]">
                <path fill="url(#gradient)" fill-opacity=".6"
                    d="M301.174 646.641 193.541 844.786 0 546.172l301.174 100.469 193.845-356.855c1.241 164.891 42.802 431.935 199.124 180.978 195.402-313.696 143.295-58.807 284.729-419.205 98.203 190.107 163.52 471.91 75.824 512.048-18.915 8.651-69.825 29.116-105.358 45.421 27.509 17.581 123.633 46.541 292.839 26.69l-193.444-24.962L301.174 646.641Z" />
                <defs>
                    <linearGradient id="gradient" x1="1097.04" x2="-141.165" y1=".22" y2="363.075"
                        gradientUnits="userSpaceOnUse">
                        <stop stop-color="#776FFF" />
                        <stop offset="1" stop-color="#FF4694" />
                    </linearGradient>
                </defs>
            </svg>
        </div>

        <div class="mx-auto max-w-7xl px-6 lg:px-8 text-center relative z-10">
            <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-6xl mb-6">Cek
                Tagihan Internet</h1>
            <p class="text-lg leading-8 text-slate-600 dark:text-indigo-100 max-w-2xl mx-auto mb-10">
                Layanan pengecekan tagihan real-time, mudah, dan transparan. <br class="hidden sm:inline">Masukkan ID
                Pelanggan Anda untuk memulai.
            </p>

            <!-- Checking Card -->
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl p-6 sm:p-10 max-w-2xl mx-auto border border-slate-200 dark:border-white/20 backdrop-blur-sm">
                <form action="{{ route('frontend.check') }}" method="POST" class="space-y-6 text-left">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold leading-6 text-slate-900 dark:text-white">Nomor
                            Internet (ID)</label>
                        <div class="relative mt-2 rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <i class="fas fa-id-card text-slate-400"></i>
                            </div>
                            <input type="text" name="internet_number"
                                class="block w-full rounded-lg border-0 py-3 pl-10 text-slate-900 dark:text-white dark:bg-slate-700/50 ring-1 ring-inset ring-slate-300 dark:ring-slate-600 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-[#352f99] sm:text-sm sm:leading-6"
                                placeholder="Contoh: 82193822" required value="{{ request('internet_number') }}">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-sm font-semibold leading-6 text-slate-900 dark:text-white">Bulan</label>
                            <select name="month"
                                class="mt-2 block w-full rounded-lg border-0 py-3 pl-3 pr-10 text-slate-900 dark:text-white dark:bg-slate-700/50 ring-1 ring-inset ring-slate-300 dark:ring-slate-600 focus:ring-2 focus:ring-[#352f99] sm:text-sm sm:leading-6">
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ (request('month') ?? date('n')) == $i ? 'selected' : '' }}>
                                        {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-semibold leading-6 text-slate-900 dark:text-white">Tahun</label>
                            <select name="year"
                                class="mt-2 block w-full rounded-lg border-0 py-3 pl-3 pr-10 text-slate-900 dark:text-white dark:bg-slate-700/50 ring-1 ring-inset ring-slate-300 dark:ring-slate-600 focus:ring-2 focus:ring-[#352f99] sm:text-sm sm:leading-6">
                                @for ($y = date('Y'); $y >= 2023; $y--)
                                    <option value="{{ $y }}" {{ (request('year') ?? date('Y')) == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full rounded-lg bg-[#352f99] px-3.5 py-3 text-sm font-bold text-white shadow-sm hover:bg-indigo-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#352f99] transition-all duration-200 transform hover:-translate-y-0.5">
                        <i class="fas fa-search mr-2"></i> Periksa Tagihan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Result Section -->
    <div class="mx-auto max-w-3xl px-6 py-12 lg:px-8">
        @if(session('error'))
            <div class="rounded-lg bg-red-50 p-4 border-l-4 border-red-500 shadow-sm animate-pulse">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-500"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Tidak Ditemukan</h3>
                        <div class="mt-2 text-sm text-red-700">
                            {{ session('error') }}
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(isset($invoice) && isset($customer))
            <div
                class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden transform transition-all duration-500 hover:shadow-xl">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800">Detail Tagihan</h3>
                    @if($invoice->status == 'paid')
                        <span
                            class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-700 ring-1 ring-inset ring-green-600/20">
                            <i class="fas fa-check-circle mr-1.5"></i> LUNAS
                        </span>
                    @else
                        <span
                            class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-700 ring-1 ring-inset ring-red-600/10">
                            <i class="fas fa-times-circle mr-1.5"></i> BELUM BAYAR
                        </span>
                    @endif
                </div>
                <div class="px-6 py-6">
                    <div class="text-center mb-8">
                        <p class="text-sm text-slate-500 uppercase tracking-widest font-semibold mb-1">Total Tagihan</p>
                        <div class="text-4xl font-extrabold text-slate-900">
                            Rp {{ number_format($customer->monthly_price, 0, ',', '.') }}
                        </div>
                        <div class="mt-2 inline-block px-3 py-1 bg-slate-100 rounded text-sm font-medium text-slate-600">
                            {{ $customer->name }} - {{ $customer->internet_number }}
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 border-t border-slate-100 pt-6">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Periode</p>
                            <p class="mt-1 text-base font-semibold text-slate-900">
                                {{ \Carbon\Carbon::parse($invoice->due_date)->format('F Y') }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-slate-500">Jatuh Tempo</p>
                            <p
                                class="mt-1 text-base font-semibold text-slate-900 {{ $invoice->status != 'paid' && now() > $invoice->due_date ? 'text-red-600' : '' }}">
                                {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
                    @if($invoice->status == 'paid')
                        <a href="{{ route('frontend.invoice', $invoice->id) }}" target="_blank"
                            class="flex w-full justify-center items-center rounded-lg bg-green-600 px-3 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-500 transition-colors">
                            <i class="fas fa-download mr-2"></i> Download Bukti Pembayaran
                        </a>
                    @else
                        <div class="rounded-md bg-yellow-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-yellow-400"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">Perhatian</h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <p>Mohon segera lakukan pembayaran untuk menghindari isolir otomatis. Hubungi admin via
                                            WhatsApp setelah melakukan transfer.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Footer -->
    <footer
        class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-white/5 mt-auto py-8 transition-colors duration-300">
        <div class="mx-auto max-w-7xl px-6 text-center">
            <p class="text-xs leading-5 text-slate-500 dark:text-indigo-200">&copy; {{ date('Y') }} Managed Service
                Provider. Developed by
                Aangwi.</p>
        </div>
    </footer>
</body>

</html>