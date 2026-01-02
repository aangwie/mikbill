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