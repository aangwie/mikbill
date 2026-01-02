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
    <title>Forgot Password - Mikrotik App</title>
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
                            50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc', 400: '#818cf8', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca', 800: '#3730a3', 900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>
</head>

<body
    class="min-h-full font-sans antialiased text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-900 transition-colors duration-300 relative selection:bg-indigo-500 selection:text-white text-slate-900">

    <!-- Theme Toggle -->
    <div class="absolute top-4 right-4 z-50">
        <button @click="toggleTheme()" type="button"
            class="rounded-full p-2 bg-white/10 backdrop-blur-md border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors focus:outline-none focus:ring-2 focus:ring-[#352f99]">
            <i class="fas fa-sun text-lg" x-show="!darkMode"></i>
            <i class="fas fa-moon text-lg" x-show="darkMode" style="display: none;"></i>
        </button>
    </div>

    <!-- Background Pattern -->
    <div class="fixed inset-0 -z-10 h-full w-full object-cover overflow-hidden pointer-events-none">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-50 to-white dark:hidden opacity-80"></div>
        <div
            class="absolute inset-0 bg-gradient-to-br from-[#352f99] to-indigo-900 mix-blend-multiply opacity-90 hidden dark:block">
        </div>
    </div>

    <div class="flex min-h-full flex-col justify-center py-12 pb-24 sm:px-6 lg:px-8 relative z-10">
        <div class="sm:mx-auto sm:w-full sm:max-w-[420px]">
            <div
                class="bg-white dark:bg-slate-800/95 backdrop-blur-sm py-10 px-6 shadow-2xl rounded-2xl sm:px-10 border border-slate-200 dark:border-white/10">

                <div class="text-center mb-8">
                    <div
                        class="mx-auto h-16 w-16 bg-amber-500 rounded-2xl flex items-center justify-center text-white text-3xl mb-4 shadow-xl">
                        <i class="fas fa-key"></i>
                    </div>
                    <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Lupa Password?
                    </h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Masukkan email Anda untuk menerima link
                        reset password.</p>
                </div>

                @if (session('success'))
                    <div class="mb-6 rounded-lg bg-green-50 p-4 border-l-4 border-green-500">
                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-lg bg-red-50 p-4 border-l-4 border-red-500">
                        <ul class="list-disc list-inside text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="space-y-6" action="{{ route('password.email') }}" method="POST">
                    @csrf
                    <div>
                        <label for="email"
                            class="block text-sm font-semibold leading-6 text-slate-900 dark:text-white">Email
                            Address</label>
                        <div class="relative mt-2 rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <i class="fas fa-envelope text-slate-400 sm:text-sm"></i>
                            </div>
                            <input id="email" name="email" type="email" autocomplete="email" required
                                class="block w-full rounded-lg border-0 py-3 pl-10 text-slate-900 dark:text-white dark:bg-slate-700/50 ring-1 ring-inset ring-slate-300 dark:ring-slate-600 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-[#352f99] sm:text-sm sm:leading-6 transition-shadow"
                                placeholder="admin@example.com">
                        </div>
                    </div>

                    <div>
                        <button type="submit"
                            class="flex w-full justify-center rounded-lg bg-[#352f99] px-3 py-3 text-sm font-bold leading-6 text-white shadow-lg shadow-indigo-500/30 hover:bg-indigo-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#352f99] transition-all duration-200 transform hover:-translate-y-0.5">
                            Kirim Link Reset <i class="fas fa-paper-plane ml-2 mt-0.5"></i>
                        </button>
                    </div>
                </form>

                <div class="mt-8 text-center">
                    <a href="{{ route('login') }}"
                        class="text-sm font-medium text-slate-500 hover:text-[#352f99] flex items-center justify-center gap-2 transition-colors">
                        <i class="fas fa-arrow-left"></i> Kembali ke Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>