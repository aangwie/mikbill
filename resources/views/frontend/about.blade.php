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
    <title>Tentang Kami - BillNesia</title>
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
                    <span class="text-xl font-bold text-[#352f99] dark:text-white tracking-tight"><a
                            href="{{ config('app.url') }}">BillNesia</a></span>
                </div>
                <div class="hidden md:flex items-center gap-8">
                    <a href="{{ route('frontend.index') }}#cek-tagihan" class="text-sm font-medium {{ request()->routeIs('frontend.index') ? 'text-[#352f99] dark:text-white font-bold' : 'text-slate-600 dark:text-slate-300' }} hover:text-[#352f99] transition-colors">Cek Tagihan</a>
                    <a href="{{ route('frontend.pricing') }}" class="text-sm font-medium {{ request()->routeIs('frontend.pricing') ? 'text-[#352f99] dark:text-white font-bold' : 'text-slate-600 dark:text-slate-300' }} hover:text-[#352f99] transition-colors">Harga</a>
                    <a href="{{ route('frontend.about') }}" class="text-sm font-medium {{ request()->routeIs('frontend.about') ? 'text-[#352f99] dark:text-white font-bold' : 'text-slate-600 dark:text-slate-300' }} hover:text-[#352f99] transition-colors">Tentang Kami</a>
                </div>
                <div class="flex items-center gap-4">
                    <button @click="toggleTheme()" type="button"
                        class="rounded-full p-2 text-slate-500 hover:text-[#352f99] dark:text-slate-300 dark:hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-[#352f99]">
                        <i class="fas fa-sun text-lg" x-show="!darkMode"></i>
                        <i class="fas fa-moon text-lg" x-show="darkMode" style="display: none;"></i>
                    </button>
                    <a href="{{ route('login') }}"
                        class="group inline-flex items-center justify-center rounded-full bg-[#352f99] px-4 py-2 text-sm font-medium text-white hover:bg-indigo-800 transition-all shadow-md shadow-indigo-500/20">Login
                        Admin <i class="fas fa-arrow-right ml-2 opacity-50 group-hover:opacity-100"></i></a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Content Section -->
    <div class="relative py-24 sm:py-32 overflow-hidden">
        <div class="mx-auto max-w-4xl px-6 lg:px-8 relative z-10">
            <div class="text-center mb-16">
                <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-6xl mb-6">
                    Tentang Kami</h1>
                <p class="text-lg leading-8 text-slate-600 dark:text-slate-400">Kenali lebih dekat siapa kami dan apa
                    misi yang kami bawa untuk Anda.</p>
            </div>

            <div
                class="bg-white dark:bg-slate-800 rounded-3xl shadow-xl border border-slate-200 dark:border-white/10 p-8 sm:p-12 prose dark:prose-invert max-w-none">
                {!! nl2br(e($setting->about_us)) !!}
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-white/5 mt-auto py-8">
        <div class="mx-auto max-w-7xl px-6 text-center">
            <div class="flex justify-center gap-6 mb-4">
                <a href="{{ route('frontend.about') }}" class="text-sm text-slate-500 hover:text-[#352f99]">Tentang
                    Kami</a>
                <a href="{{ route('frontend.terms') }}" class="text-sm text-slate-500 hover:text-[#352f99]">Syarat &
                    Ketentuan</a>
            </div>
            <p class="text-xs leading-5 text-slate-500 dark:text-indigo-200">&copy; {{ date('Y') }} BillNesia. All
                rights reserved.</p>
        </div>
    </footer>
</body>

</html>