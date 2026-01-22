@extends('layouts.app2')

@section('title', 'System Update')
@section('header', 'System Update')
@section('subheader', 'Pembaruan sistem dan perawatan rutin.')

@section('content')

    <div class="max-w-3xl mx-auto">
        <!-- Version Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-8">
            <div class="p-8 text-center">
                <div
                    class="inline-flex items-center justify-center h-20 w-20 rounded-full bg-slate-100 text-slate-800 mb-6">
                    <i class="fab fa-github text-4xl"></i>
                </div>

                <h2 class="text-base font-semibold text-slate-500 uppercase tracking-wide mb-2">Versi Terinstall Saat Ini
                </h2>
                <div
                    class="inline-block bg-indigo-50 text-indigo-700 px-6 py-2 rounded-lg font-mono text-xl font-bold border border-indigo-100 mb-6">
                    {{ $currentVersion ?? 'v1.0.0' }}
                </div>

                Pastikan Anda telah membackup database dan file konfigurasi sebelum melakukan update.
                Sumber: <a href="#" class="text-indigo-600 hover:text-indigo-500 font-medium">Repository GitHub</a>
                </p>

                <!-- GitHub Token Form -->
                <div
                    class="mb-8 max-w-lg mx-auto bg-slate-50 dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700">
                    <form action="{{ route('system.saveToken') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="text-left">
                            <label for="github_token"
                                class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">GitHub Personal
                                Access Token</label>
                            <div class="relative rounded-md shadow-sm">
                                <input type="password" name="github_token" id="github_token"
                                    class="block w-full rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-slate-900 dark:border-slate-600 dark:text-white dark:placeholder-slate-400"
                                    placeholder="github_pat_..." value="{{ $setting->github_token ?? '' }}">
                            </div>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Token digunakan untuk autentikasi
                                private repository.</p>
                        </div>
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent bg-slate-800 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-all">
                            <i class="fas fa-save mr-2 mt-0.5"></i> Simpan Token
                        </button>
                    </form>
                </div>

                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <form action="{{ route('system.update') }}" method="POST"
                        onsubmit="return confirm('Yakin ingin melakukan update? Pastikan tidak ada file yang diedit manual di hosting.');">
                        @csrf
                        <button type="submit"
                            class="w-full sm:w-auto inline-flex items-center justify-center rounded-lg bg-primary-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-primary-600/30 hover:bg-primary-500 hover:shadow-primary-600/40 transition-all transform hover:-translate-y-0.5">
                            <i class="fas fa-cloud-download-alt mr-2"></i> Cek & Update Sistem
                        </button>
                    </form>

                    <form action="{{ route('system.clear-cache') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="w-full sm:w-auto inline-flex items-center justify-center rounded-lg bg-white border border-slate-300 px-6 py-3 text-sm font-bold text-slate-700 shadow-sm hover:bg-slate-50 hover:text-slate-900 transition-all transform hover:-translate-y-0.5">
                            <i class="fas fa-broom mr-2 text-amber-500"></i> Clear Cache
                        </button>
                    </form>

                    <form action="{{ route('system.migrate') }}" method="POST"
                        onsubmit="return confirm('Yakin ingin menjalankan migrasi database? Pastikan Anda sudah membackup database.');">
                        @csrf
                        <button type="submit"
                            class="w-full sm:w-auto inline-flex items-center justify-center rounded-lg bg-white border border-slate-300 px-6 py-3 text-sm font-bold text-slate-700 shadow-sm hover:bg-slate-50 hover:text-slate-900 transition-all transform hover:-translate-y-0.5">
                            <i class="fas fa-database mr-2 text-indigo-500"></i> Run Migration
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Log Terminal -->
        @if(session('log'))
            <div class="bg-slate-900 rounded-2xl shadow-lg border border-slate-800 overflow-hidden font-mono text-sm">
                <div class="bg-slate-800 px-4 py-2 border-b border-slate-700 flex items-center gap-2">
                    <div class="flex gap-1.5">
                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                        <div class="w-3 h-3 rounded-full bg-green-500"></div>
                    </div>
                    <span class="ml-2 text-slate-400 text-xs text-center flex-1">Update Log Terminal</span>
                </div>
                <div
                    class="p-4 text-green-400 max-h-[400px] overflow-y-auto whitespace-pre-wrap leading-relaxed custom-scrollbar">
                    {{ session('log') }}
                </div>
                <div class="bg-slate-800 px-4 py-3 border-t border-slate-700">
                    @if(session('status') == 'success')
                        <div class="text-green-400 font-bold flex items-center"><i class="fas fa-check-circle mr-2"></i>
                            {{ session('message') }}</div>
                    @elseif(session('status') == 'info')
                        <div class="text-blue-400 font-bold flex items-center"><i class="fas fa-info-circle mr-2"></i>
                            {{ session('message') }}</div>
                    @else
                        <div class="text-red-400 font-bold flex items-center"><i class="fas fa-exclamation-triangle mr-2"></i>
                            {{ session('message') }}</div>
                    @endif
                </div>
            </div>
        @endif
    </div>

@endsection

@push('styles')
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            bg: #1e293b;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            bg: #334155;
            border-radius: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            bg: #475569;
        }
    </style>
@endpush