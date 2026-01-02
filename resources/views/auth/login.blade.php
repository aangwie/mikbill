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
    <title>Login - Mikrotik App</title>
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
                    fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    },
                    colors: {
                        primary: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>
</head>

<body
    class="min-h-full font-sans antialiased text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-900 text-slate-900 transition-colors duration-300 relative selection:bg-indigo-500 selection:text-white">

    <!-- Theme Toggle (Absolute Top Right) -->
    <div class="absolute top-4 right-4 z-50">
        <button @click="toggleTheme()" type="button"
            class="rounded-full p-2 bg-white/10 backdrop-blur-md border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors focus:outline-none focus:ring-2 focus:ring-[#352f99]">
            <i class="fas fa-sun text-lg" x-show="!darkMode"></i>
            <i class="fas fa-moon text-lg" x-show="darkMode" style="display: none;"></i>
        </button>
    </div>

    <!-- Background Pattern -->
    <div class="fixed inset-0 -z-10 h-full w-full object-cover overflow-hidden pointer-events-none">
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

    <div class="flex min-h-full flex-col justify-center py-12 pb-24 sm:px-6 lg:px-8 relative z-10">

        <div class="sm:mx-auto sm:w-full sm:max-w-[420px]">
            <div
                class="bg-white dark:bg-slate-800/95 backdrop-blur-sm py-10 px-6 shadow-2xl rounded-2xl sm:px-10 border border-slate-200 dark:border-white/10">

                <div class="text-center mb-8">
                    @if(isset($company) && !empty($company->logo_path))
                        <img class="mx-auto h-20 w-auto mb-4 drop-shadow-lg transition-transform hover:scale-105 duration-300 rounded-lg"
                            src="{{ asset('uploads/' . $company->logo_path) }}" alt="Logo">
                    @else
                        @if(isset($global_favicon) || file_exists(public_path('favicon.ico')))
                            <img class="mx-auto h-16 w-auto mb-4 drop-shadow-lg transition-transform hover:scale-105 duration-300"
                                src="{{ $global_favicon ?? asset('favicon.ico') }}" alt="Logo">
                        @else
                            <div
                                class="mx-auto h-16 w-16 bg-indigo-600 rounded-2xl flex items-center justify-center text-white text-3xl mb-4 shadow-xl">
                                <i class="fas fa-wifi"></i>
                            </div>
                        @endif
                    @endif
                    <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Admin Portal</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                        {{ $company->company_name ?? 'Managed Service Provider' }}
                    </p>
                </div>

                @if (session('success'))
                    <div class="mb-6 rounded-lg bg-green-50 p-4 border-l-4 border-green-500">
                        <div class="flex">
                            <i class="fas fa-check-circle text-green-500 mt-1"></i>
                            <div class="ml-3 font-medium text-green-800">{{ session('success') }}</div>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 rounded-lg bg-red-50 p-4 border-l-4 border-red-500">
                        <div class="flex">
                            <i class="fas fa-exclamation-circle text-red-500 mt-1"></i>
                            <div class="ml-3 font-medium text-red-800">{{ session('error') }}</div>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-lg bg-red-50 p-4 border-l-4 border-red-500">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-500"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Login Gagal</h3>
                                <div class="mt-1 text-sm text-red-700">
                                    <ul class="list-disc list-inside">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <form class="space-y-6" action="{{ route('login.post') }}" method="POST">
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
                        <label for="password"
                            class="block text-sm font-semibold leading-6 text-slate-900 dark:text-white">Password</label>
                        <div class="relative mt-2 rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <i class="fas fa-lock text-slate-400 sm:text-sm"></i>
                            </div>
                            <input id="password" name="password" type="password" autocomplete="current-password"
                                required
                                class="block w-full rounded-lg border-0 py-3 pl-10 text-slate-900 dark:text-white dark:bg-slate-700/50 ring-1 ring-inset ring-slate-300 dark:ring-slate-600 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-[#352f99] sm:text-sm sm:leading-6 transition-shadow"
                                placeholder="••••••">
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember-me" name="remember-me" type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 dark:border-slate-600 text-[#352f99] focus:ring-[#352f99] dark:bg-slate-700">
                            <label for="remember-me" class="ml-2 block text-sm text-slate-600 dark:text-slate-300">Ingat
                                saya</label>
                        </div>
                        <div class="text-sm">
                            <a href="{{ route('password.request') }}"
                                class="font-bold text-[#352f99] dark:text-indigo-400 hover:underline">
                                Lupa password?
                            </a>
                        </div>
                    </div>

                    <div>
                        <button type="submit"
                            class="flex w-full justify-center rounded-lg bg-[#352f99] px-3 py-3 text-sm font-bold leading-6 text-white shadow-lg shadow-indigo-500/30 hover:bg-indigo-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#352f99] transition-all duration-200 transform hover:-translate-y-0.5">
                            Masuk Sistem <i class="fas fa-arrow-right ml-2 mt-0.5"></i>
                        </button>
                    </div>
                </form>

                <div class="mt-8">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-slate-200 dark:border-slate-700"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="bg-white dark:bg-slate-800 px-2 text-slate-400 dark:text-slate-500">Atau
                                kembali ke</span>
                        </div>
                    </div>

                    <div class="mt-6 text-center">
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                            Belum punya akun?
                            <a href="{{ route('register') }}"
                                class="font-bold text-[#352f99] dark:text-indigo-400 hover:underline">Daftar
                                sekarang</a>
                        </p>
                        <a href="{{ route('frontend.index') }}"
                            class="text-sm font-medium text-slate-500 hover:text-[#352f99] flex items-center justify-center gap-2 transition-colors">
                            <i class="fas fa-home"></i> Halaman Depan
                        </a>
                    </div>
                </div>
            </div>

            <p class="mt-8 text-center text-xs text-slate-500 dark:text-indigo-200">
                &copy; {{ date('Y') }} Managed Service Provider. <br>System v1.0
            </p>
        </div>
    </div>
</body>

</html>