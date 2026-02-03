@extends('layouts.app2')

@section('title', 'Pengaturan Situs')
@section('header', 'Pengaturan Situs')
@section('subheader', 'Kelola informasi Tentang Kami dan Syarat Ketentuan.')

@section('content')
    <div class="max-w-5xl">
        <form action="{{ route('site.update') }}" method="POST">
            @csrf
            <div class="space-y-6">
                <!-- About Us Section -->
                <div
                    class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50">
                        <div class="flex items-center gap-3">
                            <div
                                class="h-10 w-10 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <h3 class="font-bold text-slate-900 dark:text-white">Tentang Kami</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <label class="block text-sm font-medium text-slate-500 mb-2">Konten Halaman Tentang Kami</label>
                        <textarea name="about_us" rows="10"
                            class="block w-full rounded-xl border-slate-300 dark:bg-slate-700 dark:border-slate-600 dark:text-white focus:ring-primary-500 focus:border-primary-500 transition-all font-sans"
                            placeholder="Tuliskan sejarah, visi, dan misi layanan Anda...">{{ $setting->about_us }}</textarea>
                        <p class="mt-2 text-xs text-slate-400 italic">Mendukung format teks biasa. Gunakan enter untuk
                            paragraf baru.</p>
                    </div>
                </div>

                <!-- Connection Mode Section -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h3 class="font-bold text-slate-900 dark:text-white">Protokol Koneksi</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <label class="block text-sm font-medium text-slate-500 mb-4">Paksa Protokol Koneksi</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="relative flex cursor-pointer rounded-xl border border-slate-200 dark:border-slate-700 p-4 focus:outline-none transition-all hover:bg-slate-50 dark:hover:bg-slate-900/50">
                                <input type="radio" name="connection_mode" value="auto" class="sr-only" {{ ($setting->connection_mode ?? 'auto') == 'auto' ? 'checked' : '' }}>
                                <div class="flex flex-col">
                                    <span class="block text-sm font-bold text-slate-900 dark:text-white">Automatic</span>
                                    <span class="mt-1 flex items-center text-xs text-slate-50">Gunakan protokol yang sedang berjalan.</span>
                                </div>
                                <div class="absolute -top-px -right-px h-6 w-6 rounded-tr-xl rounded-bl-xl bg-primary-600 flex items-center justify-center opacity-0 transition-opacity peer-checked:opacity-100" style="display: none;">
                                    <i class="fas fa-check text-[10px] text-white"></i>
                                </div>
                            </label>
                            
                            <label class="relative flex cursor-pointer rounded-xl border border-slate-200 dark:border-slate-700 p-4 focus:outline-none transition-all hover:bg-slate-50 dark:hover:bg-slate-900/50">
                                <input type="radio" name="connection_mode" value="http" class="sr-only" {{ ($setting->connection_mode ?? 'auto') == 'http' ? 'checked' : '' }}>
                                <div class="flex flex-col">
                                    <span class="block text-sm font-bold text-slate-900 dark:text-white">Force HTTP</span>
                                    <span class="mt-1 flex items-center text-xs text-slate-50">Paksa semua akses menggunakan HTTP.</span>
                                </div>
                            </label>

                            <label class="relative flex cursor-pointer rounded-xl border border-slate-200 dark:border-slate-700 p-4 focus:outline-none transition-all hover:bg-slate-50 dark:hover:bg-slate-900/50">
                                <input type="radio" name="connection_mode" value="https" class="sr-only" {{ ($setting->connection_mode ?? 'auto') == 'https' ? 'checked' : '' }}>
                                <div class="flex flex-col">
                                    <span class="block text-sm font-bold text-slate-900 dark:text-white">Force HTTPS</span>
                                    <span class="mt-1 flex items-center text-xs text-slate-50">Paksa semua akses menggunakan HTTPS.</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <style>
                    input[type="radio"]:checked + div, 
                    input[type="radio"]:checked ~ div {
                        border-color: rgb(var(--primary-600));
                    }
                    label:has(input[type="radio"]:checked) {
                        border-color: #3b82f6;
                        background-color: rgba(59, 130, 246, 0.05);
                    }
                </style>

                <!-- Terms & Conditions Section -->
                <div
                    class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50">
                        <div class="flex items-center gap-3">
                            <div
                                class="h-10 w-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600">
                                <i class="fas fa-file-contract"></i>
                            </div>
                            <h3 class="font-bold text-slate-900 dark:text-white">Syarat & Ketentuan</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <label class="block text-sm font-medium text-slate-500 mb-2">Konten Halaman Syarat dan
                            Ketentuan</label>
                        <textarea name="terms_conditions" rows="10"
                            class="block w-full rounded-xl border-slate-300 dark:bg-slate-700 dark:border-slate-600 dark:text-white focus:ring-primary-500 focus:border-primary-500 transition-all font-sans"
                            placeholder="Tuliskan aturan penggunaan layanan, kebijakan privasi, dll...">{{ $setting->terms_conditions }}</textarea>
                        <p class="mt-2 text-xs text-slate-400 italic">Pastikan informasi jelas dan transparan untuk
                            pelanggan Anda.</p>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center px-6 py-3 border border-transparent text-base font-bold rounded-xl shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all">
                        <i class="fas fa-save mr-2"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection