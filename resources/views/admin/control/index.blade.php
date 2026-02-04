@extends('layouts.app2')

@section('title', 'Kontrol Sistem')
@section('header', 'Kontrol & Monitoring Sistem')
@section('subheader', 'Manajemen protokol koneksi dan tracking aktivitas sistem')

@section('content')

    <!-- Superadmin Connection Settings -->
    <div class="mb-8 grid grid-cols-1 md:grid-cols-1 gap-6">
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 rounded-lg">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Pengaturan Koneksi (Superadmin Only)</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Paksa protokol koneksi yang digunakan oleh sistem.</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('site.update') }}" method="POST" class="flex flex-wrap items-center gap-4">
                @csrf
                <div class="flex items-center gap-4 bg-slate-50 dark:bg-slate-900/50 p-1.5 rounded-xl border border-slate-200 dark:border-slate-700">
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="connection_mode" value="auto" class="sr-only peer" {{ ($siteSetting->connection_mode ?? 'auto') == 'auto' ? 'checked' : '' }}>
                        <div class="px-4 py-2 rounded-lg text-sm font-medium transition-all peer-checked:bg-white dark:peer-checked:bg-slate-700 peer-checked:text-primary-600 dark:peer-checked:text-primary-400 peer-checked:shadow-sm text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">
                            Automatic
                        </div>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="connection_mode" value="http" class="sr-only peer" {{ ($siteSetting->connection_mode ?? 'auto') == 'http' ? 'checked' : '' }}>
                        <div class="px-4 py-2 rounded-lg text-sm font-medium transition-all peer-checked:bg-white dark:peer-checked:bg-slate-700 peer-checked:text-primary-600 dark:peer-checked:text-primary-400 peer-checked:shadow-sm text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">
                            HTTP
                        </div>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="connection_mode" value="https" class="sr-only peer" {{ ($siteSetting->connection_mode ?? 'auto') == 'https' ? 'checked' : '' }}>
                        <div class="px-4 py-2 rounded-lg text-sm font-medium transition-all peer-checked:bg-white dark:peer-checked:bg-slate-700 peer-checked:text-primary-600 dark:peer-checked:text-primary-400 peer-checked:shadow-sm text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">
                            HTTPS
                        </div>
                    </label>
                </div>

                <button type="submit" class="inline-flex items-center px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl transition-all shadow-sm hover:shadow-md focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800">
                    <i class="fas fa-save mr-2"></i>
                    Simpan Perubahan
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Login Logs -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm ring-1 ring-slate-900/5 overflow-hidden">
            <div class="border-b border-slate-200 dark:border-slate-700 px-4 py-5 sm:px-6 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Last Login IP</h3>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400">Riwayat login pengguna ke sistem.</p>
                    </div>
                </div>
                <form action="{{ route('control.clearLoginLogs') }}" method="POST" onsubmit="return confirm('Bersihkan semua log login?')">
                    @csrf
                    <button type="submit" class="text-xs font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300">
                        <i class="fas fa-trash-alt mr-1"></i> Clear
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto max-h-[400px]">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-700 sticky top-0 z-10">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wider">User</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wider">IP Address</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wider">Time</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse($loginLogs as $log)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="text-xs font-medium text-slate-900 dark:text-white">{{ $log->user->name ?? 'Unknown' }}</div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400">{{ $log->user->email ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-xs text-slate-600 dark:text-slate-300 font-mono">
                                    {{ $log->ip_address }}
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-xs text-slate-600 dark:text-slate-300">
                                    {{ $log->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-xs text-slate-500 dark:text-slate-400 italic">Belum ada data login.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Cron Logs -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm ring-1 ring-slate-900/5 overflow-hidden">
            <div class="border-b border-slate-200 dark:border-slate-700 px-4 py-5 sm:px-6 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-lg">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Cronjob Execution</h3>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400">Status eksekusi tugas terjadwal.</p>
                    </div>
                </div>
                <form action="{{ route('control.clearCronLogs') }}" method="POST" onsubmit="return confirm('Bersihkan semua log cronjob?')">
                    @csrf
                    <button type="submit" class="text-xs font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300">
                        <i class="fas fa-trash-alt mr-1"></i> Clear
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto max-h-[400px]">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-700 sticky top-0 z-10">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wider">Command</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wider">Time</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse($cronLogs as $log)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="text-xs font-mono text-slate-900 dark:text-white">{{ $log->command }}</div>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    @if($log->status == 'success')
                                        <span class="inline-flex items-center rounded-md bg-emerald-50 dark:bg-emerald-900/30 px-2 py-1 text-[10px] font-medium text-emerald-700 dark:text-emerald-400 ring-1 ring-inset ring-emerald-700/10 dark:ring-emerald-400/20">Success</span>
                                    @else
                                        <span class="inline-flex items-center rounded-md bg-rose-50 dark:bg-rose-900/30 px-2 py-1 text-[10px] font-medium text-rose-700 dark:text-rose-400 ring-1 ring-inset ring-rose-700/10 dark:ring-rose-400/20">Failed</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-xs text-slate-600 dark:text-slate-300">
                                    {{ $log->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-xs text-slate-500 dark:text-slate-400 italic">Belum ada data eksekusi cron.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
