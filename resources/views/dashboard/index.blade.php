@extends('layouts.app2')

@section('title', 'Dashboard')
@section('header', 'Dashboard')
@section('subheader', 'Ringkasan pelanggan, tagihan, pembayaran & informasi sistem')

@section('content')

    {{-- ════════════════════════════════════════════════════════════════
    CUSTOMER SUMMARY CARDS
    ════════════════════════════════════════════════════════════════ --}}
    <div class="mb-8">
        <h3 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">
            <i class="fas fa-users mr-2 text-indigo-500"></i>Ringkasan Pelanggan
        </h3>
        <dl class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            {{-- Total Customers --}}
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-700 to-slate-900 border border-slate-600 p-6 shadow-lg shadow-slate-900/20 hover:shadow-xl hover:scale-[1.02] transition-all duration-300 group cursor-default">
                <dt class="truncate text-sm font-medium text-slate-300">Total Pelanggan</dt>
                <dd class="mt-2 flex items-baseline text-3xl font-bold tracking-tight text-white">
                    {{ number_format($totalCustomers) }}
                </dd>
                <div class="absolute right-4 top-4 text-white/10 group-hover:text-white/20 transition-colors">
                    <i class="fas fa-users fa-3x transform rotate-12"></i>
                </div>
                <div class="mt-3 flex items-center text-xs text-slate-400">
                    @if($isConnected)
                        <span class="flex items-center text-emerald-400">
                            <i class="fas fa-signal mr-1"></i> Mikrotik (Real-time)
                        </span>
                    @else
                        <span class="flex items-center text-amber-400">
                            <i class="fas fa-database mr-1"></i> Database (Router Offline)
                        </span>
                    @endif
                </div>
            </div>

            {{-- Active Customers --}}
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 border border-emerald-400 p-6 shadow-lg shadow-emerald-500/20 hover:shadow-xl hover:scale-[1.02] transition-all duration-300 group cursor-default">
                <dt class="truncate text-sm font-medium text-emerald-100">Pelanggan Aktif</dt>
                <dd class="mt-2 flex items-baseline text-3xl font-bold tracking-tight text-white">
                    {{ number_format($activeCustomers) }}
                </dd>
                <div class="absolute right-4 top-4 text-white/20 group-hover:text-white/30 transition-colors">
                    <i class="fas fa-user-check fa-3x"></i>
                </div>
                <div class="mt-3 flex items-center text-xs text-emerald-200/80">
                    <i class="fas fa-chart-line mr-1"></i>
                    {{ $isConnected ? 'Koneksi Online' : 'Statistik Database' }}
                </div>
            </div>

            {{-- Disabled Customers --}}
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-rose-500 to-pink-600 border border-rose-400 p-6 shadow-lg shadow-rose-500/20 hover:shadow-xl hover:scale-[1.02] transition-all duration-300 group cursor-default">
                <dt class="truncate text-sm font-medium text-rose-100">Pelanggan Non-Aktif</dt>
                <dd class="mt-2 flex items-baseline text-3xl font-bold tracking-tight text-white">
                    {{ number_format($disabledCustomers) }}
                    <span class="ml-2 text-sm font-medium text-rose-200/80">/ {{ number_format($totalCustomers) }}</span>
                </dd>
                <div class="absolute right-4 top-4 text-white/20 group-hover:text-white/30 transition-colors">
                    <i class="fas fa-user-slash fa-3x"></i>
                </div>
                <div class="mt-3 flex items-center text-xs text-rose-200/80">
                    <i class="fas fa-times-circle mr-1"></i>
                    {{ $totalCustomers > 0 ? round(($disabledCustomers / $totalCustomers) * 100, 1) : 0 }}% dari total
                </div>
            </div>
        </dl>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
    BILLING SUMMARY CARDS
    ════════════════════════════════════════════════════════════════ --}}
    <div class="mb-8">
        <h3 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">
            <i class="fas fa-file-invoice-dollar mr-2 text-indigo-500"></i>Informasi Tagihan —
            {{ \Carbon\Carbon::now()->locale('id')->isoFormat('MMMM YYYY') }}
        </h3>
        <dl class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            {{-- Total Billing --}}
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 border border-indigo-400 p-6 shadow-lg shadow-indigo-500/20 hover:shadow-xl hover:scale-[1.02] transition-all duration-300 group cursor-default">
                <dt class="truncate text-sm font-medium text-indigo-100">Total Tagihan</dt>
                <dd class="mt-2 flex items-baseline text-2xl font-bold tracking-tight text-white">
                    Rp {{ number_format($totalBilling, 0, ',', '.') }}
                </dd>
                <div class="absolute right-4 top-4 text-white/10 group-hover:text-white/20 transition-colors">
                    <i class="fas fa-receipt fa-3x transform -rotate-12"></i>
                </div>
            </div>

            {{-- Paid --}}
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-green-600 border border-emerald-400 p-6 shadow-lg shadow-emerald-500/20 hover:shadow-xl hover:scale-[1.02] transition-all duration-300 group cursor-default">
                <dt class="truncate text-sm font-medium text-emerald-100">Sudah Dibayar (Paid)</dt>
                <dd class="mt-2 flex items-baseline text-2xl font-bold tracking-tight text-white">
                    Rp {{ number_format($paidBilling, 0, ',', '.') }}
                </dd>
                <div class="absolute right-4 top-4 text-white/20 group-hover:text-white/30 transition-colors">
                    <i class="fas fa-check-double fa-3x"></i>
                </div>
                <div class="mt-3 flex items-center text-xs text-emerald-200/80">
                    <i class="fas fa-chart-pie mr-1"></i>
                    {{ $totalBilling > 0 ? round(($paidBilling / $totalBilling) * 100, 1) : 0 }}% terbayar
                </div>
            </div>

            {{-- Unpaid --}}
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 border border-amber-400 p-6 shadow-lg shadow-amber-500/20 hover:shadow-xl hover:scale-[1.02] transition-all duration-300 group cursor-default">
                <dt class="truncate text-sm font-medium text-amber-100">Belum Dibayar (Unpaid)</dt>
                <dd class="mt-2 flex items-baseline text-2xl font-bold tracking-tight text-white">
                    Rp {{ number_format($unpaidBilling, 0, ',', '.') }}
                </dd>
                <div class="absolute right-4 top-4 text-white/20 group-hover:text-white/30 transition-colors">
                    <i class="fas fa-exclamation-triangle fa-3x"></i>
                </div>
                <div class="mt-3 flex items-center text-xs text-amber-200/80">
                    <i class="fas fa-clock mr-1"></i>
                    {{ $totalBilling > 0 ? round(($unpaidBilling / $totalBilling) * 100, 1) : 0 }}% belum bayar
                </div>
            </div>
        </dl>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
    PAYMENT CHART
    ════════════════════════════════════════════════════════════════ --}}
    <div class="mb-8">
        <div
            class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm ring-1 ring-slate-900/5 dark:ring-slate-700 overflow-hidden">
            <div
                class="border-b border-slate-200 dark:border-slate-700 px-6 py-4 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
                <h3 class="text-base font-semibold leading-6 text-slate-900 dark:text-white">
                    <i class="fas fa-chart-bar mr-2 text-indigo-500"></i>Grafik Pembayaran (6 Bulan Terakhir)
                </h3>
                <span
                    class="inline-flex items-center rounded-md bg-indigo-50 dark:bg-indigo-900/20 px-2 py-1 text-xs font-medium text-indigo-700 dark:text-indigo-400 ring-1 ring-inset ring-indigo-700/10 dark:ring-indigo-400/20">
                    Chart.js
                </span>
            </div>
            <div class="p-6">
                <canvas id="paymentChart" height="100"></canvas>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
    SYSTEM INFORMATION
    ════════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        {{-- Server Info --}}
        <div
            class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm ring-1 ring-slate-900/5 dark:ring-slate-700 overflow-hidden">
            <div class="border-b border-slate-200 dark:border-slate-700 px-6 py-4 bg-slate-50/50 dark:bg-slate-800/50">
                <h3 class="text-base font-semibold leading-6 text-slate-900 dark:text-white">
                    <i class="fas fa-server mr-2 text-indigo-500"></i>Informasi Server
                </h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between py-3 border-b border-slate-100 dark:border-slate-700">
                    <span class="text-sm font-medium text-slate-500 dark:text-slate-400">PHP Version</span>
                    <span
                        class="inline-flex items-center rounded-lg bg-blue-50 dark:bg-blue-900/20 px-3 py-1.5 text-sm font-bold text-blue-700 dark:text-blue-400 ring-1 ring-inset ring-blue-700/10 dark:ring-blue-500/20">
                        <i class="fab fa-php mr-2 text-lg"></i>{{ $phpVersion }}
                    </span>
                </div>
                <div class="flex items-center justify-between py-3 border-b border-slate-100 dark:border-slate-700">
                    <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Laravel Version</span>
                    <span
                        class="inline-flex items-center rounded-lg bg-red-50 dark:bg-red-900/20 px-3 py-1.5 text-sm font-bold text-red-700 dark:text-red-400 ring-1 ring-inset ring-red-700/10 dark:ring-red-500/20">
                        <i class="fab fa-laravel mr-2 text-lg"></i>{{ $laravelVersion }}
                    </span>
                </div>
                <div class="flex items-center justify-between py-3 border-b border-slate-100 dark:border-slate-700">
                    <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Database Version</span>
                    <span
                        class="inline-flex items-center rounded-lg bg-amber-50 dark:bg-amber-900/20 px-3 py-1.5 text-sm font-bold text-amber-700 dark:text-amber-400 ring-1 ring-inset ring-amber-700/10 dark:ring-amber-500/20">
                        <i class="fas fa-database mr-2"></i>{{ $dbVersion }}
                    </span>
                </div>
                <div class="flex items-center justify-between py-3">
                    <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Server OS</span>
                    <span
                        class="inline-flex items-center rounded-lg bg-slate-100 dark:bg-slate-700 px-3 py-1.5 text-sm font-bold text-slate-700 dark:text-slate-300">
                        <i class="fas fa-desktop mr-2"></i>{{ PHP_OS }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Library Status --}}
        <div
            class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm ring-1 ring-slate-900/5 dark:ring-slate-700 overflow-hidden">
            <div class="border-b border-slate-200 dark:border-slate-700 px-6 py-4 bg-slate-50/50 dark:bg-slate-800/50">
                <h3 class="text-base font-semibold leading-6 text-slate-900 dark:text-white">
                    <i class="fas fa-puzzle-piece mr-2 text-indigo-500"></i>PHP Extensions & Libraries
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-3">
                    @foreach($libraries as $lib)
                        <div
                            class="flex items-center justify-between rounded-xl px-4 py-3 {{ $lib['loaded'] ? 'bg-emerald-50 dark:bg-emerald-900/10 ring-1 ring-emerald-500/20' : 'bg-rose-50 dark:bg-rose-900/10 ring-1 ring-rose-500/20' }} transition-all duration-200 hover:scale-[1.02]">
                            <span
                                class="text-sm font-semibold {{ $lib['loaded'] ? 'text-emerald-800 dark:text-emerald-300' : 'text-rose-800 dark:text-rose-300' }}">
                                {{ $lib['name'] }}
                            </span>
                            @if($lib['loaded'])
                                <span class="flex items-center gap-1 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                    <i class="fas fa-check-circle"></i> Active
                                </span>
                            @else
                                <span class="flex items-center gap-1 text-xs font-bold text-rose-600 dark:text-rose-400">
                                    <i class="fas fa-times-circle"></i> Missing
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? 'rgba(148,163,184,0.1)' : 'rgba(0,0,0,0.06)';
            const textColor = isDark ? '#94a3b8' : '#64748b';

            const ctx = document.getElementById('paymentChart').getContext('2d');

            const gradient1 = ctx.createLinearGradient(0, 0, 0, 400);
            gradient1.addColorStop(0, 'rgba(16,185,129,0.8)');
            gradient1.addColorStop(1, 'rgba(16,185,129,0.1)');

            const gradient2 = ctx.createLinearGradient(0, 0, 0, 400);
            gradient2.addColorStop(0, 'rgba(245,158,11,0.8)');
            gradient2.addColorStop(1, 'rgba(245,158,11,0.1)');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($chartLabels),
                    datasets: [
                        {
                            label: 'Paid (Rp)',
                            data: @json($chartPaid),
                            backgroundColor: gradient1,
                            borderColor: 'rgba(16,185,129,1)',
                            borderWidth: 2,
                            borderRadius: 8,
                            borderSkipped: false,
                        },
                        {
                            label: 'Unpaid (Rp)',
                            data: @json($chartUnpaid),
                            backgroundColor: gradient2,
                            borderColor: 'rgba(245,158,11,1)',
                            borderWidth: 2,
                            borderRadius: 8,
                            borderSkipped: false,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                color: textColor,
                                usePointStyle: true,
                                pointStyle: 'rectRounded',
                                padding: 20,
                                font: { size: 12, weight: '600' }
                            }
                        },
                        tooltip: {
                            backgroundColor: isDark ? '#1e293b' : '#ffffff',
                            titleColor: isDark ? '#f1f5f9' : '#0f172a',
                            bodyColor: isDark ? '#cbd5e1' : '#475569',
                            borderColor: isDark ? '#334155' : '#e2e8f0',
                            borderWidth: 1,
                            cornerRadius: 12,
                            padding: 12,
                            callbacks: {
                                label: function (context) {
                                    let value = context.parsed.y || 0;
                                    return context.dataset.label + ': Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: textColor, font: { size: 11, weight: '500' } }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: gridColor },
                            ticks: {
                                color: textColor,
                                font: { size: 11 },
                                callback: function (value) {
                                    if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(1) + 'jt';
                                    if (value >= 1000) return 'Rp ' + (value / 1000).toFixed(0) + 'rb';
                                    return 'Rp ' + value;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
@endpush