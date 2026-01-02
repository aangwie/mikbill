@extends('layouts.app2')

@section('title', 'SMTP Settings')
@section('header', 'Pengaturan Email (SMTP)')
@section('subheader', 'Konfigurasi mail server untuk pengiriman tagihan dan notifikasi via email.')

@section('content')

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show"
            class="mb-6 rounded-md bg-green-50 p-4 border border-green-200 shadow-sm relative">
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
        <div x-data="{ show: true }" x-show="show"
            class="mb-6 rounded-md bg-red-50 p-4 border border-red-200 shadow-sm relative">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button @click="show = false" type="button"
                            class="inline-flex rounded-md bg-red-50 p-1.5 text-red-500 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2 focus:ring-offset-red-50">
                            <span class="sr-only">Dismiss</span>
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="max-w-4xl mx-auto">
        <form action="{{ route('mail.update') }}" method="POST">
            @csrf
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="bg-indigo-100 text-indigo-600 p-2 rounded-lg"><i class="fas fa-envelope"></i></div>
                        <h3 class="text-base font-bold text-slate-900">Konfigurasi Mail Server</h3>
                    </div>
                    @if($setting->mail_host)
                        <span
                            class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                            Konfigurasi Aktif
                        </span>
                    @else
                        <span
                            class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-800">
                            Belum Dikonfigurasi
                        </span>
                    @endif
                </div>

                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-900 mb-1">SMTP Host</label>
                            <input type="text" name="mail_host"
                                class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
                                value="{{ $setting->mail_host }}" placeholder="smtp.gmail.com" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-900 mb-1">SMTP Port</label>
                            <input type="text" name="mail_port"
                                class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
                                value="{{ $setting->mail_port }}" placeholder="587" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-900 mb-1">SMTP Username</label>
                            <input type="text" name="mail_username"
                                class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
                                value="{{ $setting->mail_username }}" placeholder="youremail@gmail.com">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-900 mb-1">SMTP Password</label>
                            <input type="password" name="mail_password"
                                class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
                                value="{{ $setting->mail_password }}" placeholder="••••••••">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-900 mb-1">Encryption</label>
                            <select name="mail_encryption"
                                class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
                                <option value="tls" {{ $setting->mail_encryption == 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ $setting->mail_encryption == 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="" {{ $setting->mail_encryption == '' ? 'selected' : '' }}>None</option>
                            </select>
                        </div>
                    </div>

                    <hr class="border-slate-100">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-900 mb-1">Sender Email (From Address)</label>
                            <input type="email" name="mail_from_address"
                                class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
                                value="{{ $setting->mail_from_address }}" placeholder="noreply@domain.com">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-900 mb-1">Sender Name (From Name)</label>
                            <input type="text" name="mail_from_name"
                                class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
                                value="{{ $setting->mail_from_name }}" placeholder="MikBill Notification">
                        </div>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3">
                        <button type="submit"
                            class="flex justify-center items-center rounded-lg bg-primary-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-primary-500 transition-all focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
                            <i class="fas fa-save mr-2"></i> Simpan Konfigurasi
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Test Send Email Form -->
        <div class="mt-8 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center gap-3">
                <div class="bg-amber-100 text-amber-600 p-2 rounded-lg"><i class="fas fa-paper-plane"></i></div>
                <h3 class="text-base font-bold text-slate-900">Uji Coba Pengiriman (Test)</h3>
            </div>
            <div class="p-6">
                <form action="{{ route('mail.test') }}" method="POST" class="flex flex-col md:flex-row gap-4 items-end">
                    @csrf
                    <div class="flex-grow">
                        <label class="block text-sm font-bold text-slate-900 mb-1">Email Tujuan Test</label>
                        <input type="email" name="email"
                            class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
                            placeholder="nama@email.com" required>
                    </div>
                    <button type="submit"
                        class="flex justify-center items-center rounded-lg bg-amber-500 px-6 py-2 text-sm font-bold text-white shadow-sm hover:bg-amber-600 transition-all">
                        <i class="fas fa-paper-plane mr-2"></i> Kirim Email Test
                    </button>
                </form>
                <p class="mt-3 text-xs text-slate-500 italic">* Simpan konfigurasi terlebih dahulu sebelum melakukan
                    pengetesan.</p>
            </div>
        </div>

        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-xl p-4 flex gap-3">
            <div class="text-blue-500 mt-1"><i class="fas fa-info-circle text-lg"></i></div>
            <div class="text-sm text-blue-800">
                <p class="font-bold mb-1">Catatan Penting:</p>
                <ul class="list-disc ml-4 space-y-1">
                    <li>Gunakan App Password jika Anda menggunakan Gmail.</li>
                    <li>Pastikan port SMTP (587 atau 465) sudah diizinkan di firewall server Anda.</li>
                    <li>Konfigurasi ini akan digunakan secara otomatis untuk mengirim pesan email sistem.</li>
                </ul>
            </div>
        </div>
    </div>

@endsection