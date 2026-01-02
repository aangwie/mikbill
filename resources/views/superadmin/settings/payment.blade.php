@extends('layouts.app2')

@section('title', 'Pengaturan Pembayaran')
@section('header', 'Pengaturan Pembayaran')
@section('subheader', 'Konfigurasi Gateway untuk aktivasi paket otomatis.')

@section('content')
    <div class="max-w-5xl" x-data="{ tab: '{{ $setting->active_provider }}' }">
        <div
            class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <form action="{{ route('payment.update') }}" method="POST" class="p-8">
                @csrf

                <div class="flex items-center gap-4 mb-8 pb-6 border-b border-slate-100 dark:border-slate-700">
                    <div
                        class="h-12 w-12 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600">
                        <i class="fas fa-credit-card text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Gerbang Pembayaran</h3>
                        <p class="text-sm text-slate-500">Pilih dan lanjuktan konfigurasi provider aktif.</p>
                    </div>
                    <div class="ml-auto flex items-center gap-6">
                        <div class="flex bg-slate-100 dark:bg-slate-900 p-1 rounded-xl">
                            <label class="cursor-pointer">
                                <input type="radio" name="active_provider" value="xendit" x-model="tab"
                                    class="sr-only peer">
                                <span
                                    class="px-4 py-1.5 rounded-lg text-xs font-bold uppercase transition-all peer-checked:bg-white dark:peer-checked:bg-slate-700 peer-checked:shadow-sm text-slate-500 peer-checked:text-slate-900 dark:peer-checked:text-white">Xendit</span>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="active_provider" value="midtrans" x-model="tab"
                                    class="sr-only peer">
                                <span
                                    class="px-4 py-1.5 rounded-lg text-xs font-bold uppercase transition-all peer-checked:bg-white dark:peer-checked:bg-slate-700 peer-checked:shadow-sm text-slate-500 peer-checked:text-slate-900 dark:peer-checked:text-white">Midtrans</span>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="docs" value="true" @click="tab = 'docs'" class="sr-only peer">
                                <span
                                    :class="tab === 'docs' ? 'bg-white dark:bg-slate-700 shadow-sm text-slate-900 dark:text-white' : 'text-slate-500'"
                                    class="px-4 py-1.5 rounded-lg text-xs font-bold uppercase transition-all">Panduan</span>
                            </label>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ $setting->is_active ? 'checked' : '' }}
                                class="sr-only peer">
                            <div
                                class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600">
                            </div>
                            <span class="ml-3 text-sm font-medium text-slate-700 dark:text-slate-300">Aktif</span>
                        </label>
                    </div>
                </div>

                <!-- Xendit Section -->
                <div x-show="tab === 'xendit'" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-2" class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Xendit Secret API
                            Key</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                <i class="fas fa-key"></i>
                            </span>
                            <input type="password" name="xendit_api_key" value="{{ $setting->xendit_api_key }}"
                                class="block w-full pl-10 pr-4 py-2.5 rounded-xl border-slate-300 dark:bg-slate-700 dark:border-slate-600 dark:text-white focus:ring-primary-500 focus:border-primary-500 transition-all"
                                placeholder="xnd_development_...">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Xendit Callback
                            Token</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                <i class="fas fa-shield-alt"></i>
                            </span>
                            <input type="text" name="xendit_callback_token" value="{{ $setting->xendit_callback_token }}"
                                class="block w-full pl-10 pr-4 py-2.5 rounded-xl border-slate-300 dark:bg-slate-700 dark:border-slate-600 dark:text-white focus:ring-primary-500 focus:border-primary-500 transition-all"
                                placeholder="Token dari Xendit Settings">
                        </div>
                    </div>

                    <div
                        class="bg-slate-50 dark:bg-slate-900/40 rounded-xl p-4 border border-slate-100 dark:border-slate-700">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Webhook URL Xendit:</p>
                        <code
                            class="text-sm text-primary-600 dark:text-primary-400 select-all">{{ route('payment.webhook') }}</code>
                    </div>
                </div>

                <!-- Midtrans Section -->
                <div x-show="tab === 'midtrans'" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-2" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Server
                                Key</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" name="midtrans_server_key"
                                    value="{{ $setting->midtrans_server_key }}"
                                    class="block w-full pl-10 pr-4 py-2.5 rounded-xl border-slate-300 dark:bg-slate-700 dark:border-slate-600 dark:text-white focus:ring-primary-500 focus:border-primary-500 transition-all"
                                    placeholder="SB-Mid-server-...">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Client
                                Key</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                    <i class="fas fa-user-shield"></i>
                                </span>
                                <input type="text" name="midtrans_client_key" value="{{ $setting->midtrans_client_key }}"
                                    class="block w-full pl-10 pr-4 py-2.5 rounded-xl border-slate-300 dark:bg-slate-700 dark:border-slate-600 dark:text-white focus:ring-primary-500 focus:border-primary-500 transition-all"
                                    placeholder="SB-Mid-client-...">
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex items-center gap-4 bg-slate-50 dark:bg-slate-900/40 p-4 rounded-xl border border-slate-100 dark:border-slate-700">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-300">Mode Produksi</p>
                            <p class="text-xs text-slate-500">Nonaktifkan untuk menggunakan Sandbox (Testing).</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="midtrans_is_production" value="1" {{ $setting->midtrans_is_production ? 'checked' : '' }} class="sr-only peer">
                            <div
                                class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600">
                            </div>
                        </label>
                    </div>

                    <div
                        class="bg-slate-50 dark:bg-slate-900/40 rounded-xl p-4 border border-slate-100 dark:border-slate-700">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Notification URL Midtrans
                            (Webhook):</p>
                        <code
                            class="text-sm text-primary-600 dark:text-primary-400 select-all">{{ route('payment.webhook') }}</code>
                    </div>
                </div>

                <!-- Documentation Section -->
                <div x-show="tab === 'docs'" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-2" class="space-y-6">
                    <div
                        class="bg-blue-50 dark:bg-blue-900/20 p-6 rounded-2xl border border-blue-100 dark:border-blue-900/30">
                        <h4 class="text-blue-900 dark:text-blue-100 font-bold mb-4 flex items-center gap-2">
                            <i class="fas fa-book"></i> Panduan Konfigurasi Midtrans
                        </h4>
                        <div class="space-y-4 text-sm text-blue-800 dark:text-blue-200 leading-relaxed">
                            <p>Untuk mengaktifkan pembayaran otomatis melalui Midtrans, silakan masukkan URL berikut pada
                                Dashboard Midtrans Anda (Menu: <b>Settings > Payment</b>):</p>

                            <div class="space-y-3">
                                <div>
                                    <p class="font-bold text-xs uppercase mb-1">Payment Notification URL (Webhook):</p>
                                    <code
                                        class="bg-white dark:bg-slate-800 px-3 py-2 rounded-lg block border border-blue-200 dark:border-blue-800 text-blue-600 dark:text-blue-400 break-all select-all">{{ route('payment.webhook') }}</code>
                                </div>

                                <div>
                                    <p class="font-bold text-xs uppercase mb-1">Finish Redirect URL:</p>
                                    <code
                                        class="bg-white dark:bg-slate-800 px-3 py-2 rounded-lg block border border-blue-200 dark:border-blue-800 text-blue-600 dark:text-blue-400 break-all select-all">{{ route('payment.finish') }}</code>
                                </div>

                                <div>
                                    <p class="font-bold text-xs uppercase mb-1">Unfinish Redirect URL:</p>
                                    <code
                                        class="bg-white dark:bg-slate-800 px-3 py-2 rounded-lg block border border-blue-200 dark:border-blue-800 text-blue-600 dark:text-blue-400 break-all select-all">{{ route('payment.unfinish') }}</code>
                                </div>

                                <div>
                                    <p class="font-bold text-xs uppercase mb-1">Error Redirect URL:</p>
                                    <code
                                        class="bg-white dark:bg-slate-800 px-3 py-2 rounded-lg block border border-blue-200 dark:border-blue-800 text-blue-600 dark:text-blue-400 break-all select-all">{{ route('payment.error') }}</code>
                                </div>
                            </div>

                            <p
                                class="mt-4 p-3 bg-amber-50 dark:bg-amber-900/20 border-l-4 border-amber-400 text-amber-800 dark:text-amber-200 italic">
                                <b>Catatan:</b> Pastikan URL di atas dapat diakses secara publik (tidak di localhost) agar
                                Midtrans dapat mengirimkan notifikasi pembayaran.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center px-6 py-3 border border-transparent text-base font-bold rounded-xl shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all">
                        <i class="fas fa-save mr-2"></i> Simpan Konfigurasi
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection