@extends('layouts.app2')

@section('title', 'Dashboard PPPoE')
@section('header', 'Monitor PPPoE')
@section('subheader', 'Real-time monitoring user online & offline Mikrotik')

@section('content')

    <!-- Controls & Router Info -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <!-- Auto Refresh Controls -->
        <div
            class="inline-flex items-center rounded-lg bg-white dark:bg-slate-800 p-2 shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex items-center px-2">
                <span class="relative flex h-3 w-3 mr-2">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                </span>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Live</span>
            </div>

            <div class="h-6 w-px bg-slate-200 dark:bg-slate-700 mx-2"></div>

            <label class="flex items-center cursor-pointer mr-3">
                <div class="relative">
                    <input type="checkbox" id="switchAutoRefresh" class="sr-only peer" checked>
                    <div class="block bg-slate-200 dark:bg-slate-700 w-10 h-6 rounded-full transition-colors duration-300 peer-checked:bg-green-500"
                        id="switchBg">
                    </div>
                    <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform duration-300 transform peer-checked:translate-x-4 shadow"
                        id="switchDot"></div>
                </div>
                <span class="ml-2 text-sm font-medium text-slate-600 dark:text-slate-300">Auto</span>
            </label>

            <select id="selectInterval"
                class="block w-24 rounded-md border-0 py-1.5 text-slate-900 dark:text-white dark:bg-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-xs sm:leading-6 cursor-pointer">
                <option value="5">5s</option>
                <option value="15">15s</option>
                <option value="30">30s</option>
                <option value="60">1m</option>
                <option value="180">3m</option>
            </select>

            <div class="ml-3 hidden sm:flex items-center text-xs text-slate-400 dark:text-slate-500 font-mono">
                <i class="fas fa-history mr-1.5"></i> <span id="timerDisplay">--</span>s
            </div>
        </div>

        <!-- Router Info -->
        <div class="flex items-center gap-3">
            <div class="text-right hidden sm:block">
                <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $routerInfo->host ?? 'No Host' }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $routerInfo->username ?? '-' }}</p>
            </div>

            @if(isset($isConnected) && $isConnected)
                <div class="flex items-center justify-center h-10 w-10 rounded-full bg-green-100 dark:bg-green-900/50 text-green-600 dark:text-green-400 shadow-sm"
                    title="Terhubung">
                    <i class="fas fa-link"></i>
                </div>
            @else
                <div class="flex items-center justify-center h-10 w-10 rounded-full bg-red-100 text-red-600 shadow-sm animate-pulse"
                    title="Terputus">
                    <i class="fas fa-unlink"></i>
                </div>
            @endif

            @if(auth()->user()->role == 'admin')
                <a href="{{ route('router.index') }}"
                    class="flex items-center justify-center h-10 w-10 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-primary-600 hover:border-primary-600 transition shadow-sm"
                    title="Konfigurasi">
                    <i class="fas fa-cog"></i>
                </a>
            @endif
        </div>
    </div>

    <!-- Stats Cards -->
    @if(isset($secrets) && isset($actives))
        @php
            $totalUser = count($secrets);
            $onlineUser = $actives->count();
            $offlineUser = $totalUser - $onlineUser;
        @endphp
        <dl class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-8">
            <!-- Total -->
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-700 to-slate-900 border border-slate-600 p-6 shadow-lg shadow-slate-900/20 hover:shadow-xl transition-shadow group">
                <dt class="truncate text-sm font-medium text-slate-300">Total Pelanggan</dt>
                <dd class="mt-2 flex items-baseline text-3xl font-bold tracking-tight text-white">
                    {{ $totalUser }}
                </dd>
                <div class="absolute right-4 top-4 text-white/10 group-hover:text-white/20 transition-colors">
                    <i class="fas fa-users fa-3x transform rotate-12"></i>
                </div>
            </div>

            <!-- Online -->
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 border border-emerald-400 p-6 shadow-lg shadow-emerald-500/20 hover:shadow-xl transition-shadow group">
                <dt class="truncate text-sm font-medium text-emerald-100">Online</dt>
                <dd class="mt-2 flex items-baseline text-3xl font-bold tracking-tight text-white">
                    {{ $onlineUser }}
                </dd>
                <div class="absolute right-4 top-4 text-white/20 group-hover:text-white/30 transition-colors">
                    <i class="fas fa-wifi fa-3x"></i>
                </div>
            </div>

            <!-- Offline -->
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-rose-500 to-pink-600 border border-rose-400 p-6 shadow-lg shadow-rose-500/20 hover:shadow-xl transition-shadow group">
                <dt class="truncate text-sm font-medium text-rose-100">Offline</dt>
                <dd class="mt-2 flex items-baseline text-3xl font-bold tracking-tight text-white">
                    {{ $offlineUser }}
                    <span class="ml-2 text-sm font-medium text-rose-200/80">/ {{ $totalUser }}</span>
                </dd>
                <div class="absolute right-4 top-4 text-white/20 group-hover:text-white/30 transition-colors">
                    <i class="fas fa-power-off fa-3x"></i>
                </div>
            </div>
        </dl>
    @endif

    <!-- Table Section -->
    @if(isset($secrets))
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-900/5 overflow-hidden">
            <div
                class="border-b border-slate-200 dark:border-slate-700 px-4 py-5 sm:px-6 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
                <h3 class="text-base font-semibold leading-6 text-slate-900 dark:text-white">Daftar Pelanggan</h3>
                <span
                    class="inline-flex items-center rounded-md bg-primary-50 dark:bg-primary-900/20 px-2 py-1 text-xs font-medium text-primary-700 dark:text-primary-400 ring-1 ring-inset ring-primary-700/10 dark:ring-primary-400/20">{{ count($secrets) }}
                    User</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700" id="tableUser">
                    <thead class="bg-slate-50 dark:bg-slate-700">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                                Username</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                                Profile</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wider hidden sm:table-cell">
                                IP Address</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wider hidden sm:table-cell">
                                Uptime</th>
                            @if(auth()->user()->role == 'admin')
                                <th scope="col" class="relative px-6 py-3 text-right">
                                    <span class="sr-only">Actions</span>
                                </th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                        @foreach($secrets as $secret)
                            @php
                                $name = $secret['name'];
                                $isActive = $actives->has($name);
                                $activeData = $isActive ? $actives[$name] : null;
                                $isDisabled = isset($secret['disabled']) && $secret['disabled'] == 'true';
                            @endphp
                            <tr
                                class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors {{ $isDisabled ? 'bg-slate-50 dark:bg-slate-900 opacity-60' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if($isDisabled)
                                            <span class="h-2.5 w-2.5 rounded-full bg-slate-400 mr-2"></span>
                                            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Disabled</span>
                                        @elseif($isActive)
                                            <span class="relative flex h-2.5 w-2.5 mr-2">
                                                <span
                                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                                            </span>
                                            <span class="text-xs font-medium text-emerald-700 dark:text-emerald-400">Online</span>
                                        @else
                                            <span class="h-2.5 w-2.5 rounded-full bg-rose-400 mr-2"></span>
                                            <span class="text-xs font-medium text-rose-600 dark:text-rose-400">Offline</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ $name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center rounded-md bg-blue-50 dark:bg-blue-900/30 px-2 py-1 text-xs font-medium text-blue-700 dark:text-blue-300 ring-1 ring-inset ring-blue-700/10 dark:ring-blue-500/20">
                                        {{ $secret['profile'] ?? 'default' }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300 hidden sm:table-cell">
                                    {{ $activeData['address'] ?? '-' }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300 hidden sm:table-cell font-mono">
                                    {{ $activeData['uptime'] ?? '-' }}
                                </td>
                                @if(auth()->user()->role == 'admin')
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            @if($isActive)
                                                <form action="{{ route('pppoe.kick') }}" method="POST"
                                                    onsubmit="return confirm('Kick user {{ $name }}?');">
                                                    @csrf
                                                    <input type="hidden" name="username" value="{{ $name }}">
                                                    <button type="submit"
                                                        class="text-amber-500 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300 transition-colors"
                                                        title="Reset/Kick Connection">
                                                        <i class="fas fa-sync-alt"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            <form action="{{ route('pppoe.toggle') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="username" value="{{ $name }}">
                                                @if($isDisabled)
                                                    <input type="hidden" name="action" value="enable">
                                                    <button type="submit"
                                                        class="text-green-500 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300 transition-colors"
                                                        title="Enable User">
                                                        <i class="fas fa-user-check"></i>
                                                    </button>
                                                @else
                                                    <input type="hidden" name="action" value="disable">
                                                    <button type="submit"
                                                        class="text-rose-500 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300 transition-colors"
                                                        onclick="return confirm('Disable user {{ $name }}?');" title="Disable User">
                                                        <i class="fas fa-user-slash"></i>
                                                    </button>
                                                @endif
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

@endsection

@push('styles')
    <!-- DataTables Tailwind -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.tailwindcss.css">
    <style>
        /* Custom switch for Auto Refresh - Handled by Tailwind peer classes */

        /* DataTable customization to match clean theme */
        div.dt-container .dt-paging .dt-paging-button {
            @apply px-3 py-1 text-sm rounded bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-600 hover:text-slate-900 dark:hover:text-white ml-1 !important;
        }

        div.dt-container .dt-paging .dt-paging-button.current {
            @apply bg-primary-600 text-white border-primary-600 hover:bg-primary-700 hover:text-white !important;
        }

        div.dt-container .dt-search input {
            @apply rounded-md border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm !important;
        }

        div.dt-container select {
            @apply rounded-md border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm py-1 !important;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.tailwindcss.js"></script>
    <script>
        $(document).ready(function () {
            $('#tableUser').DataTable({
                responsive: true,
                language: {
                    search: "",
                    searchPlaceholder: "Search user...",
                    lengthMenu: "Show _MENU_"
                },
                dom: '<"flex flex-col sm:flex-row justify-between items-center bg-white dark:bg-slate-800 px-4 py-3 border-b border-slate-200 dark:border-slate-700"f<"mt-2 sm:mt-0"l>>t<"flex flex-col sm:flex-row justify-between items-center bg-white dark:bg-slate-800 px-4 py-3"ip>',
            });

            // --- Auto Refresh Logic ---
            let savedInterval = localStorage.getItem('dashboard_refresh_interval') || 30;
            let savedStatus = localStorage.getItem('dashboard_refresh_active');
            let isRefreshOn = (savedStatus === 'false') ? false : true;

            $('#selectInterval').val(savedInterval);
            $('#switchAutoRefresh').prop('checked', isRefreshOn);

            let timeLeft = parseInt(savedInterval);
            let timerElement = document.getElementById('timerDisplay');
            let intervalId;

            function startTimer() {
                if (intervalId) clearInterval(intervalId);
                if (!isRefreshOn) {
                    timerElement.innerHTML = "OFF";
                    return;
                }
                timerElement.innerHTML = timeLeft;

                intervalId = setInterval(function () {
                    // Check if user is interacting with something
                    if ($('.dt-search input').is(':focus')) return;;

                    if (timeLeft <= 0) {
                        window.location.reload();
                    } else {
                        timeLeft--;
                        timerElement.innerHTML = timeLeft;
                    }
                }, 1000);
            }

            $('#switchAutoRefresh').change(function () {
                isRefreshOn = $(this).is(':checked');
                localStorage.setItem('dashboard_refresh_active', isRefreshOn);
                if (isRefreshOn) {
                    timeLeft = parseInt($('#selectInterval').val());
                    startTimer();
                } else {
                    clearInterval(intervalId);
                    timerElement.innerHTML = "OFF";
                }
            });

            $('#selectInterval').change(function () {
                let newVal = $(this).val();
                localStorage.setItem('dashboard_refresh_interval', newVal);
                timeLeft = parseInt(newVal);
                if (isRefreshOn) timerElement.innerHTML = timeLeft;
            });

            startTimer();
        });
    </script>
@endpush