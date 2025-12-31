@extends('layouts.app2')

@section('title', 'Profil Perusahaan')
@section('header', 'Profil Perusahaan')
@section('subheader', 'Identitas provider, kontak, dan informasi pembayaran.')

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

    <form action="{{ route('company.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Left Column: Identity & Bank -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Identity Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center gap-3">
                        <div class="bg-indigo-100 text-indigo-600 p-2 rounded-lg"><i class="fas fa-building"></i></div>
                        <h3 class="text-base font-bold text-slate-900">Identitas Umum</h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-900 mb-1">Nama Perusahaan / ISP</label>
                            <input type="text" name="company_name"
                                class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
                                value="{{ $company->company_name }}" placeholder="Contoh: NetWiz Internet" required>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-900 mb-1">Nama Pemilik / Direktur</label>
                                <input type="text" name="owner_name"
                                    class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
                                    value="{{ $company->owner_name }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-900 mb-1">Nomor WhatsApp /
                                    Hotline</label>
                                <input type="text" name="phone"
                                    class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
                                    value="{{ $company->phone }}">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-900 mb-1">Alamat Lengkap</label>
                            <textarea name="address" rows="3"
                                class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">{{ $company->address }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Bank Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center gap-3">
                        <div class="bg-emerald-100 text-emerald-600 p-2 rounded-lg"><i class="fas fa-money-check-alt"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Info Pembayaran (Rekening)</h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                            <div class="sm:col-span-1">
                                <label class="block text-sm font-medium text-slate-900 mb-1">Nama Bank</label>
                                <input type="text" name="bank_name"
                                    class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
                                    value="{{ $company->bank_name }}" placeholder="Cth: BCA">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-slate-900 mb-1">Nomor Rekening</label>
                                <input type="text" name="account_number"
                                    class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
                                    value="{{ $company->account_number }}" placeholder="Cth: 1234567890">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-900 mb-1">Atas Nama (Pemilik
                                Rekening)</label>
                            <input type="text" name="account_holder"
                                class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
                                value="{{ $company->account_holder }}" placeholder="Cth: PT. NetWiz Indonesia">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Branding -->
            <div class="lg:col-span-1 space-y-8">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden sticky top-24">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center gap-3">
                        <div class="bg-orange-100 text-orange-600 p-2 rounded-lg"><i class="fas fa-image"></i></div>
                        <h3 class="text-base font-bold text-slate-900">Branding</h3>
                    </div>
                    <div class="p-6 space-y-8">

                        <!-- Logo Input -->
                        <div>
                            <label class="block text-sm font-bold text-slate-900 mb-2">Logo Perusahaan</label>
                            <div class="flex items-center gap-4 mb-4">
                                @if($company->logo_path)
                                    <div
                                        class="h-20 w-20 rounded-lg border border-slate-200 p-1 bg-slate-50 flex items-center justify-center overflow-hidden">
                                        <img src="{{ asset('uploads/' . $company->logo_path) }}" class="max-h-full max-w-full"
                                            alt="Logo">
                                    </div>
                                @else
                                    <div
                                        class="h-20 w-20 rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 flex items-center justify-center text-slate-400">
                                        <i class="fas fa-image text-2xl"></i>
                                    </div>
                                @endif
                            </div>
                            <input type="file" name="logo"
                                class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
                                accept="image/*">
                            <p class="mt-2 text-xs text-slate-500">Format: JPG/PNG. Otomatis jadi Favicon.</p>
                        </div>

                        <hr class="border-slate-100">

                        <!-- Signature Input -->
                        <div>
                            <label class="block text-sm font-bold text-slate-900 mb-2">Tanda Tangan / Stempel</label>
                            <div class="flex items-center gap-4 mb-4">
                                @if($company->signature_path)
                                    <div
                                        class="h-20 w-auto min-w-[5rem] rounded-lg border border-slate-200 p-1 bg-slate-50 flex items-center justify-center overflow-hidden">
                                        <img src="{{ asset('uploads/' . $company->signature_path) }}" class="max-h-full"
                                            alt="Signature">
                                    </div>
                                @else
                                    <div
                                        class="h-20 w-20 rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 flex items-center justify-center text-slate-400">
                                        <i class="fas fa-file-signature text-2xl"></i>
                                    </div>
                                @endif
                            </div>
                            <input type="file" name="signature"
                                class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
                                accept="image/*">
                            <p class="mt-2 text-xs text-slate-500">Akan muncul di bagian bawah Invoice.</p>
                        </div>

                        <div class="pt-4">
                            <button type="submit"
                                class="w-full flex justify-center items-center rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-primary-500 transition-all">
                                <i class="fas fa-save mr-2"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

@endsection