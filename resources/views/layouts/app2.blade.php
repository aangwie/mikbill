<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" x-data="{ 
          darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
          sidebarOpen: false,
          sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
          toggleTheme() {
              this.darkMode = !this.darkMode;
              localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
              if (this.darkMode) {
                  document.documentElement.classList.add('dark');
              } else {
                  document.documentElement.classList.remove('dark');
              }
          },
          toggleSidebar() {
              this.sidebarCollapsed = !this.sidebarCollapsed;
              localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
          }
      }"
    x-init="$watch('darkMode', val => val ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark')); if(darkMode) document.documentElement.classList.add('dark');">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Mikrotik App')</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ $global_favicon ?? asset('favicon.ico') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind CSS & Alpine.js -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
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
                            950: '#1e1b4b',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] {
            display: none !important;
        }
        /* Sidebar scrollbar */
        .scrollbar-thin::-webkit-scrollbar { width: 4px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background-color: rgba(148, 163, 184, 0.3); border-radius: 20px; }
        .scrollbar-thin::-webkit-scrollbar-thumb:hover { background-color: rgba(148, 163, 184, 0.5); }
    </style>
    @stack('styles')
</head>

<body
    class="h-full font-sans text-slate-800 antialiased bg-slate-50 dark:bg-slate-900 dark:text-slate-100 transition-colors duration-300">

    {{-- Sidebar --}}
    @include('layouts.sidebar')

    {{-- Navbar --}}
    @include('layouts.navbar_tail')

    {{-- Main Content --}}
    <main class="py-10 pb-24 transition-all duration-300" :class="sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64'">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            {{-- Page Header --}}
            @if (trim($__env->yieldContent('header')))
                <header class="mb-8">
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">@yield('header')</h1>
                    @if (trim($__env->yieldContent('subheader')))
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">@yield('subheader')</p>
                    @endif
                </header>
            @endif

            {{-- Alert Messages --}}
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-transition
                    class="mb-6 rounded-md bg-green-50 p-4 border border-green-200">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                        <div class="ml-auto pl-3">
                            <div class="-mx-1.5 -my-1.5">
                                <button @click="show = false" type="button"
                                    class="inline-flex rounded-md bg-green-50 p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2 focus:ring-offset-green-50">
                                    <span class="sr-only">Dismiss</span>
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" x-transition
                    class="mb-6 rounded-md bg-red-50 p-4 border border-red-200">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                        </div>
                        <div class="ml-auto pl-3">
                            <button @click="show = false" type="button"
                                class="inline-flex rounded-md bg-red-50 p-1.5 text-red-500 hover:bg-red-100">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Content Slot --}}
            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>

</html>