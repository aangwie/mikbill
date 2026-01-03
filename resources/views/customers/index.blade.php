@extends('layouts.app2')

@section('title', 'Manajemen Pelanggan')
@section('header', 'Manajemen Pelanggan')
@section('subheader', 'Kelola data pelanggan, paket, dan lokasi.')

@section('content')

    <div x-data="{ 
                                            showAddModal: false, 
                                            showEditModal: false, 
                                            showImportModal: false, 
                                            showSyncModal: false,
                                            showDeleteAllModal: false,
                                            deleteMethod: '0'
                                        }" @open-edit-modal.window="showEditModal = true">

        <!-- Toolbar -->
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            @if(Auth::user()->role !== 'superadmin')
                <div class="flex gap-2">
                    <button @click="showAddModal = true"
                        class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 transition-all">
                        <i class="fas fa-plus mr-2"></i> Tambah Baru
                    </button>
                    <button @click="showSyncModal = true"
                        class="inline-flex items-center rounded-lg bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 transition-all">
                        <i class="fas fa-sync mr-2 text-slate-400"></i> Sinkron
                    </button>
                </div>
            @endif
            <div class="flex gap-2">
                @if(Auth::user()->role !== 'superadmin')
                    <button @click="showImportModal = true"
                        class="inline-flex items-center rounded-lg bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 px-3 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 transition-all">
                        <i class="fas fa-file-upload mr-2 text-blue-500"></i> Impor
                    </button>
                @endif
                <a href="{{ route('customers.export') }}"
                    class="inline-flex items-center rounded-lg bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 px-3 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 transition-all">
                    <i class="fas fa-file-excel mr-2 text-green-600"></i> Ekspor
                </a>
                @if(Auth::user()->role !== 'superadmin')
                    <button @click="showDeleteAllModal = true"
                        class="inline-flex items-center rounded-lg bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-900/50 px-3 py-2 text-sm font-medium text-rose-600 dark:text-rose-400 shadow-sm hover:bg-rose-100 dark:hover:bg-rose-900/40 transition-all">
                        <i class="fas fa-trash-alt mr-2"></i> Hapus Semua
                    </button>
                @endif
            </div>
        </div>

        <!-- Table Card -->
        <div
            class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm ring-1 ring-slate-900/5 dark:ring-slate-700/50 overflow-hidden">
            <div class="overflow-x-auto p-4">
                <table id="tableCust" class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th
                                class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider py-3 px-4 bg-slate-50 dark:bg-slate-700/50 rounded-l-lg">
                                No. Internet</th>
                            <th
                                class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider py-3 px-4 bg-slate-50 dark:bg-slate-700/50">
                                Nama Pelanggan</th>
                            <th
                                class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider py-3 px-4 bg-slate-50 dark:bg-slate-700/50 hidden sm:table-cell">
                                Operator (PJ)</th>
                            <th
                                class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider py-3 px-4 bg-slate-50 dark:bg-slate-700/50 hidden sm:table-cell">
                                No. HP</th>
                            <th
                                class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider py-3 px-4 bg-slate-50 dark:bg-slate-700/50">
                                Harga Paket</th>
                            <th
                                class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider py-3 px-4 bg-slate-50 dark:bg-slate-700/50 rounded-r-lg text-right">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach($customers as $c)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors group">
                                <td class="px-4 py-3 align-middle">
                                    <span
                                        class="inline-flex items-center rounded-md bg-slate-100 dark:bg-slate-700 px-2 py-1 text-xs font-medium text-slate-700 dark:text-slate-300 font-mono">
                                        {{ $c->internet_number ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 align-middle">
                                    <div class="font-medium text-slate-900 dark:text-white">{{ $c->name }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1">
                                        <i class="fas fa-user-circle"></i> {{ $c->pppoe_username }}
                                        @if(!empty($c->notes))
                                            <span class="ml-2 text-amber-500 cursor-help" title="{{ $c->notes }}">
                                                <i class="fas fa-sticky-note"></i>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 align-middle hidden sm:table-cell">
                                    @if($c->operator)
                                        <span
                                            class="inline-flex items-center rounded-full bg-indigo-50 dark:bg-indigo-900/30 px-2 py-1 text-xs font-medium text-indigo-700 dark:text-indigo-300">
                                            {{ $c->operator->name }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 align-middle hidden sm:table-cell">
                                    @if($c->phone)
                                        <a href="https://wa.me/{{ $c->phone }}" target="_blank"
                                            class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 text-sm flex items-center gap-1">
                                            <i class="fab fa-whatsapp"></i> {{ $c->phone }}
                                        </a>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 align-middle font-medium text-slate-700 dark:text-slate-300">
                                    Rp {{ number_format($c->monthly_price, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 align-middle text-right">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            class="btn-edit p-1.5 text-amber-500 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-md transition-colors"
                                            data-id="{{ $c->id }}" data-internet="{{ $c->internet_number }}"
                                            data-name="{{ $c->name }}" data-phone="{{ $c->phone }}"
                                            data-price="{{ $c->monthly_price }}" data-operator="{{ $c->operator_id }}"
                                            data-address="{{ $c->address }}" data-lat="{{ $c->latitude }}"
                                            data-lng="{{ $c->longitude }}" data-profile="{{ $c->profile }}"
                                            data-notes="{{ $c->notes }}">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                        @if(Auth::user()->role !== 'superadmin')
                                            <button type="button" onclick="confirmDelete('{{ $c->id }}', '{{ $c->name }}')"
                                                class="p-1.5 text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODAL ADD (Tailwind + Alpine) -->
        <div x-show="showAddModal" class="relative z-500" aria-labelledby="modal-title" role="dialog" aria-modal="true"
            style="display: none;">
            <div x-show="showAddModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity"></div>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="showAddModal" x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl"
                        @click.away="showAddModal = false">

                        <form action="{{ route('customers.store') }}" method="POST">
                            @csrf
                            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                <h3 class="text-xl font-bold leading-6 text-slate-900 mb-6" id="modal-title">Tambah
                                    Pelanggan Baru</h3>

                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                    <!-- Col 1: Administratif -->
                                    <div>
                                        <h4
                                            class="text-sm font-bold text-primary-600 uppercase tracking-widest mb-4 border-b border-primary-100 pb-2">
                                            Administratif</h4>

                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-sm font-medium leading-6 text-slate-900">Nomor
                                                    Internet (ID)</label>
                                                <div class="mt-1 flex rounded-md shadow-sm">
                                                    <input type="text" name="internet_number" id="addInetNum"
                                                        class="block w-full rounded-l-md border-0 py-1.5 text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
                                                        required maxlength="8">
                                                    <button type="button" onclick="generateRandomInet()"
                                                        class="relative -ml-px inline-flex items-center gap-x-1.5 rounded-r-md px-3 py-2 text-sm font-semibold text-slate-600 ring-1 ring-inset ring-slate-300 hover:bg-slate-50">
                                                        <i class="fas fa-random"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-slate-900">Nama
                                                    Pelanggan</label>
                                                <input type="text" name="name"
                                                    class="mt-1 block w-full rounded-md border-0 py-1.5 text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
                                                    required>
                                            </div>

                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-slate-900">No. HP
                                                        (WhatsApp)</label>
                                                    <input type="text" name="phone"
                                                        class="mt-1 block w-full rounded-md border-0 py-1.5 text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
                                                        placeholder="628xxx">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-slate-900">Harga Paket
                                                        (Rp)</label>
                                                    <input type="number" name="monthly_price"
                                                        class="mt-1 block w-full rounded-md border-0 py-1.5 text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
                                                        value="150000" required>
                                                </div>
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-slate-900">Operator
                                                    (PJ)</label>
                                                <select name="operator_id"
                                                    class="mt-1 block w-full rounded-md border-0 py-1.5 text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-primary-600 sm:text-sm sm:leading-6">
                                                    <option value="">-- Admin Langsung --</option>
                                                    @if(isset($operators)) @foreach($operators as $op) <option
                                                    value="{{ $op->id }}">{{ $op->name }}</option> @endforeach @endif
                                                </select>
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-slate-700">Catatan</label>
                                                <textarea name="notes" rows="2"
                                                    class="mt-1 block w-full rounded-md border-0 py-1.5 text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Col 2: Teknis & Lokasi -->
                                    <div>
                                        <h4
                                            class="text-sm font-bold text-red-600 uppercase tracking-widest mb-4 border-b border-red-100 pb-2">
                                            Teknis (Mikrotik)</h4>

                                        <div class="grid grid-cols-2 gap-4 mb-4">
                                            <div>
                                                <label class="block text-sm font-medium text-slate-900">Username
                                                    PPPoE</label>
                                                <input type="text" name="pppoe_username"
                                                    class="mt-1 block w-full rounded-md border-0 py-1.5 text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
                                                    required>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-900">Password
                                                    PPPoE</label>
                                                <input type="text" name="pppoe_password"
                                                    class="mt-1 block w-full rounded-md border-0 py-1.5 text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="mb-6">
                                            <label class="block text-sm font-medium text-slate-900">Server Profile</label>
                                            <select name="profile"
                                                class="mt-1 block w-full rounded-md border-0 py-1.5 text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-primary-600 sm:text-sm sm:leading-6">
                                                @if(isset($profiles)) @foreach($profiles as $p) <option
                                                value="{{ $p['name'] }}">{{ $p['name'] }}</option> @endforeach @else
                                                    <option value="default">Default</option> @endif
                                            </select>
                                        </div>

                                        <h4
                                            class="text-sm font-bold text-green-600 uppercase tracking-widest mb-4 border-b border-green-100 pb-2">
                                            Lokasi</h4>
                                        <div id="mapAdd" class="h-48 w-full rounded-lg border border-slate-300 mb-4 z-10">
                                        </div>
                                        <div class="grid grid-cols-2 gap-4 mb-4">
                                            <input type="text" name="latitude" id="latAdd"
                                                class="block w-full rounded-md border-0 py-1.5 text-xs text-slate-500 bg-slate-50 ring-1 ring-inset ring-slate-200"
                                                placeholder="Latitude">
                                            <input type="text" name="longitude" id="lngAdd"
                                                class="block w-full rounded-md border-0 py-1.5 text-xs text-slate-500 bg-slate-50 ring-1 ring-inset ring-slate-200"
                                                placeholder="Longitude">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-900">Alamat Lengkap</label>
                                            <textarea name="address" rows="2"
                                                class="mt-1 block w-full rounded-md border-0 py-1.5 text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <button type="submit"
                                    class="inline-flex w-full justify-center rounded-md bg-primary-600 px-3 py-2 text-sm font-bold text-white shadow-sm hover:bg-primary-500 sm:ml-3 sm:w-auto">Simpan
                                    Data</button>
                                <button type="button" @click="showAddModal = false"
                                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL EDIT (Tailwind + Alpine) -->
        <div x-show="showEditModal" class="relative z-500" aria-labelledby="modal-title" role="dialog" aria-modal="true"
            style="display: none;">
            <div x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity"></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="showEditModal" x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl"
                        @click.away="showEditModal = false">

                        <form id="formEdit" method="POST">
                            @csrf @method('PUT')
                            <div class="bg-white dark:bg-slate-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                <h3 class="text-xl font-bold leading-6 text-slate-900 dark:text-white mb-6">Edit Data
                                    Pelanggan</h3>
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                    <!-- Col 1 -->
                                    <div>
                                        <h4
                                            class="text-sm font-bold text-amber-600 uppercase tracking-widest mb-4 border-b border-amber-100 pb-2">
                                            Data Utama</h4>
                                        <div class="space-y-4">
                                            <div>
                                                <label
                                                    class="block text-sm font-medium text-slate-900 dark:text-white">Nomor
                                                    Internet</label>
                                                <div class="mt-1 flex rounded-md shadow-sm">
                                                    <input type="text" name="internet_number" id="editInet"
                                                        class="block w-full rounded-l-md border-0 py-1.5 text-slate-900 dark:text-white dark:bg-slate-700 ring-1 ring-inset ring-slate-300 dark:ring-slate-600 sm:text-sm"
                                                        required @if(Auth::user()->role === 'superadmin') readonly disabled
                                                        @endif>
                                                    <button type="button" onclick="generateRandomInetEdit()"
                                                        class="relative -ml-px inline-flex items-center gap-x-1.5 rounded-r-md px-3 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 ring-1 ring-inset ring-slate-300 dark:ring-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700"><i
                                                            class="fas fa-random"></i></button>
                                                </div>
                                            </div>
                                            <div><label
                                                    class="block text-sm font-medium text-slate-900 dark:text-white">Nama
                                                    Pelanggan</label><input type="text" name="name" id="editName"
                                                    class="mt-1 block w-full rounded-md border-0 py-1.5 ring-1 ring-inset ring-slate-300 dark:ring-slate-600 text-slate-900 dark:text-white bg-white dark:bg-slate-700 sm:text-sm"
                                                    @if(Auth::user()->role === 'superadmin') readonly disabled @endif>
                                            </div>
                                            <div><label class="block text-sm font-medium text-slate-900 dark:text-white">No.
                                                    HP</label><input type="text" name="phone" id="editPhone"
                                                    class="mt-1 block w-full rounded-md border-0 py-1.5 ring-1 ring-inset ring-slate-300 dark:ring-slate-600 text-slate-900 dark:text-white bg-white dark:bg-slate-700 sm:text-sm"
                                                    @if(Auth::user()->role === 'superadmin') readonly disabled @endif>
                                            </div>
                                            <div><label
                                                    class="block text-sm font-medium text-slate-900 dark:text-white">Harga
                                                    Paket</label><input type="number" name="monthly_price" id="editPrice"
                                                    class="mt-1 block w-full rounded-md border-0 py-1.5 ring-1 ring-inset ring-slate-300 dark:ring-slate-600 text-slate-900 dark:text-white bg-white dark:bg-slate-700 sm:text-sm"
                                                    @if(Auth::user()->role === 'superadmin') readonly disabled @endif>
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-sm font-medium text-slate-900 dark:text-white">Operator</label>
                                                <select name="operator_id" id="editOperator"
                                                    class="mt-1 block w-full rounded-md border-0 py-1.5 ring-1 ring-inset ring-slate-300 dark:ring-slate-600 text-slate-900 dark:text-white bg-white dark:bg-slate-700 sm:text-sm"
                                                    @if(Auth::user()->role === 'superadmin') disabled @endif>
                                                    <option value="">-- Admin Langsung --</option>
                                                    @if(isset($operators)) @foreach($operators as $op) <option
                                                    value="{{ $op->id }}">{{ $op->name }}</option> @endforeach @endif
                                                </select>
                                            </div>
                                            <div><label
                                                    class="block text-sm font-medium text-slate-700 dark:text-slate-300">Catatan</label><textarea
                                                    name="notes" id="editNotes" rows="2"
                                                    class="mt-1 block w-full rounded-md border-0 py-1.5 ring-1 ring-inset ring-slate-300 dark:ring-slate-600 text-slate-900 dark:text-white bg-white dark:bg-slate-700 sm:text-sm"
                                                    @if(Auth::user()->role === 'superadmin') readonly disabled
                                                    @endif></textarea>
                                            </div>
                                        </div>
                                        <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-700">
                                            <label class="block text-sm font-bold text-slate-900 dark:text-white">Paket
                                                Internet
                                                (Profile)</label>
                                            <select name="profile" id="editProfile"
                                                class="mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 ring-1 ring-inset ring-slate-300 dark:ring-slate-600 text-slate-900 dark:text-white bg-white dark:bg-slate-700 focus:ring-2 focus:ring-primary-600 sm:text-sm sm:leading-6"
                                                @if(Auth::user()->role === 'superadmin') disabled @endif>
                                                @if(isset($profiles)) @foreach($profiles as $p) <option
                                                value="{{ $p['name'] }}">{{ $p['name'] }}</option> @endforeach @else
                                                    <option value="default">Default</option> @endif
                                            </select>
                                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Perubahan profile
                                                akan langsung
                                                diterapkan ke Router Mikrotik.</p>
                                        </div>
                                    </div>
                                    <!-- Col 2 -->
                                    <div>
                                        <h4
                                            class="text-sm font-bold text-green-600 uppercase tracking-widest mb-4 border-b border-green-100 pb-2">
                                            Update Lokasi</h4>
                                        <div id="mapEdit"
                                            class="h-64 w-full rounded-lg border border-slate-300 mb-4 z-10 relative"></div>
                                        <div class="grid grid-cols-2 gap-4 mb-4">
                                            <input type="text" name="latitude" id="editLat"
                                                class="block w-full rounded-md border-0 py-1.5 text-xs bg-slate-50 dark:bg-slate-700 dark:text-white ring-1 ring-inset ring-slate-200 dark:ring-slate-600"
                                                @if(Auth::user()->role === 'superadmin') readonly disabled @endif>
                                            <input type="text" name="longitude" id="editLng"
                                                class="block w-full rounded-md border-0 py-1.5 text-xs bg-slate-50 dark:bg-slate-700 dark:text-white ring-1 ring-inset ring-slate-200 dark:ring-slate-600"
                                                @if(Auth::user()->role === 'superadmin') readonly disabled @endif>
                                        </div>
                                        <div><label class="block text-sm font-medium text-slate-900 dark:text-white">Alamat
                                                Lengkap</label><textarea name="address" id="editAddress" rows="3"
                                                class="mt-1 block w-full rounded-md border-0 py-1.5 ring-1 ring-inset ring-slate-300 dark:ring-slate-600 text-slate-900 dark:text-white bg-white dark:bg-slate-700 sm:text-sm"
                                                @if(Auth::user()->role === 'superadmin') readonly disabled @endif></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-amber-50 dark:bg-slate-700/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                @if(Auth::user()->role !== 'superadmin')
                                    <button type="submit"
                                        class="inline-flex w-full justify-center rounded-md bg-amber-600 px-3 py-2 text-sm font-bold text-white shadow-sm hover:bg-amber-500 sm:ml-3 sm:w-auto">Update
                                        Data</button>
                                @endif
                                <button type="button" @click="showEditModal = false"
                                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-slate-700 px-3 py-2 text-sm font-semibold text-slate-900 dark:text-slate-200 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 hover:bg-slate-50 dark:hover:bg-slate-600 sm:mt-0 sm:w-auto">
                                    {{ Auth::user()->role === 'superadmin' ? 'Tutup' : 'Batal' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL IMPORT (Alpine) -->
        <div x-show="showImportModal" class="relative z-500" style="display:none;">
            <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-slate-800 text-left shadow-xl transition-all sm:w-full sm:max-w-lg"
                        @click.away="showImportModal = false">
                        <form action="{{ route('customers.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="bg-white dark:bg-slate-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                <h3 class="text-lg font-semibold leading-6 text-slate-900 dark:text-white mb-2">Impor Data
                                    Excel</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Pastikan format file sesuai
                                    template.</p>
                                <input type="file" name="file"
                                    class="block w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 dark:file:bg-primary-900/50 file:text-primary-700 dark:file:text-primary-300 hover:file:bg-primary-100 dark:hover:file:bg-primary-900"
                                    required accept=".xlsx, .xls, .csv">
                                <div class="mt-4"><a href="{{ route('customers.template') }}"
                                        class="text-sm text-primary-600 dark:text-primary-400 hover:underline">Download
                                        Template Excel</a></div>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-700/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <button type="submit"
                                    class="inline-flex w-full justify-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 sm:ml-3 sm:w-auto">Upload</button>
                                <button type="button" @click="showImportModal = false"
                                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-slate-700 px-3 py-2 text-sm font-semibold text-slate-900 dark:text-slate-200 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 hover:bg-slate-50 dark:hover:bg-slate-600 sm:mt-0 sm:w-auto">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL SYNC (Alpine) -->
        <div x-show="showSyncModal" class="relative z-500" style="display:none;">
            <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-slate-800 text-left shadow-xl transition-all sm:w-full sm:max-w-lg"
                        @click.away="showSyncModal = false">
                        <div class="bg-white dark:bg-slate-800 px-4 pb-4 pt-5 sm:p-6">
                            <div
                                class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/50 mb-4">
                                <i class="fas fa-sync text-blue-600 dark:text-blue-400 text-xl"></i>
                            </div>
                            <div class="text-center">
                                <h3 class="text-base font-semibold leading-6 text-slate-900 dark:text-white">Sinkronisasi
                                    Database</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">Proses ini akan mencocokkan data
                                    lokal dengan data di
                                    Router Mikrotik.</p>

                                <!-- Initial -->
                                <div id="syncInitial" class="mt-6">
                                    <button onclick="startSync()"
                                        class="inline-flex w-full justify-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500">Mulai
                                        Sinkronisasi</button>
                                </div>

                                <!-- Progress -->
                                <div id="syncProgress" style="display:none;" class="mt-6 space-y-4">
                                    <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2.5">
                                        <div id="progressBar" class="bg-primary-600 h-2.5 rounded-full" style="width: 0%">
                                        </div>
                                    </div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 font-mono" id="syncStatusText">
                                        Waiting...</div>
                                    <ul id="syncLog"
                                        class="h-40 overflow-y-auto text-left text-xs bg-slate-50 dark:bg-slate-900 p-2 rounded border border-slate-200 dark:border-slate-700 space-y-1 text-slate-600 dark:text-slate-400">
                                    </ul>
                                </div>

                                <!-- Done -->
                                <div id="syncDone" style="display:none;" class="mt-6">
                                    <button onclick="location.reload()"
                                        class="inline-flex w-full justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500">Selesai
                                        & Refresh</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL DELETE ALL (Alpine) -->
        <div x-show="showDeleteAllModal" class="relative z-500" style="display:none;" x-cloak>
            <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-slate-800 text-left shadow-xl transition-all sm:w-full sm:max-w-lg"
                        @click.away="showDeleteAllModal = false">
                        <form action="{{ route('customers.destroyAll') }}" method="POST">
                            @csrf
                            <div class="bg-white dark:bg-slate-800 px-4 pb-4 pt-5 sm:p-6">
                                <div
                                    class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-rose-100 dark:bg-rose-900/50 mb-4">
                                    <i class="fas fa-exclamation-triangle text-rose-600 dark:text-rose-400 text-xl"></i>
                                </div>
                                <div class="text-center">
                                    <h3 class="text-lg font-bold leading-6 text-slate-900 dark:text-white">Hapus Seluruh
                                        Pelanggan</h3>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">
                                        Pilih metode penghapusan data pelanggan. Tindakan ini tidak dapat dibatalkan!
                                    </p>
                                </div>

                                <div class="mt-6 space-y-3">
                                    <!-- Option 1: DB Only -->
                                    <label
                                        :class="deleteMethod == '0' ? 'border-primary-500 ring-1 ring-primary-500 bg-primary-50/30 dark:bg-primary-900/10' : 'border-slate-200 dark:border-slate-600'"
                                        class="relative flex cursor-pointer rounded-lg border p-4 shadow-sm focus:outline-none hover:border-primary-500 transition-all bg-white dark:bg-slate-700">
                                        <input type="radio" name="delete_mikrotik" value="0" x-model="deleteMethod"
                                            class="sr-only">
                                        <span class="flex flex-1">
                                            <span class="flex flex-col">
                                                <span
                                                    class="block text-sm font-bold text-slate-900 dark:text-white">Database
                                                    Web Saja</span>
                                                <span
                                                    class="mt-1 flex items-center text-xs text-slate-500 dark:text-slate-400">
                                                    Hanya menghapus data dari panel ini. Data di Mikrotik tetap aman.
                                                </span>
                                            </span>
                                        </span>
                                        <div class="ml-4 flex items-center">
                                            <div :class="deleteMethod == '0' ? 'border-primary-500 bg-primary-500' : 'border-slate-300 dark:border-slate-500 bg-transparent'"
                                                class="h-4 w-4 rounded-full border flex items-center justify-center transition-all">
                                                <div x-show="deleteMethod == '0'" class="h-1.5 w-1.5 rounded-full bg-white">
                                                </div>
                                            </div>
                                            <i class="fas fa-database text-slate-400 ml-3"></i>
                                        </div>
                                    </label>

                                    <!-- Option 2: DB + Mikrotik -->
                                    <label
                                        :class="deleteMethod == '1' ? 'border-rose-500 ring-1 ring-rose-500 bg-rose-50/30 dark:bg-rose-900/10' : 'border-slate-200 dark:border-slate-600'"
                                        class="relative flex cursor-pointer rounded-lg border p-4 shadow-sm focus:outline-none hover:border-rose-500 transition-all bg-white dark:bg-slate-700">
                                        <input type="radio" name="delete_mikrotik" value="1" x-model="deleteMethod"
                                            class="sr-only">
                                        <span class="flex flex-1">
                                            <span class="flex flex-col">
                                                <span
                                                    class="block text-sm font-bold text-rose-600 dark:text-rose-400">Database
                                                    & Mikrotik</span>
                                                <span
                                                    class="mt-1 flex items-center text-xs text-slate-500 dark:text-slate-400">
                                                    Hapus data dari Database DAN hapus PPPoE Secret dari Router Mikrotik.
                                                </span>
                                            </span>
                                        </span>
                                        <div class="ml-4 flex items-center">
                                            <div :class="deleteMethod == '1' ? 'border-rose-500 bg-rose-500' : 'border-slate-300 dark:border-slate-500 bg-transparent'"
                                                class="h-4 w-4 rounded-full border flex items-center justify-center transition-all">
                                                <div x-show="deleteMethod == '1'" class="h-1.5 w-1.5 rounded-full bg-white">
                                                </div>
                                            </div>
                                            <i class="fas fa-network-wired text-rose-400 ml-3"></i>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-700/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <button type="submit"
                                    onclick="return confirm('KONFIRMASI AKHIR: Anda yakin ingin menghapus SEMUA data?')"
                                    class="inline-flex w-full justify-center rounded-md bg-rose-600 px-3 py-2 text-sm font-bold text-white shadow-sm hover:bg-rose-500 sm:ml-3 sm:w-auto">
                                    Ya, Hapus Sekarang
                                </button>
                                <button type="button" @click="showDeleteAllModal = false"
                                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-slate-700 px-3 py-2 text-sm font-semibold text-slate-900 dark:text-slate-200 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 hover:bg-slate-50 dark:hover:bg-slate-600 sm:mt-0 sm:w-auto">
                                    Batal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Hidden Form for Enhanced Deletion -->
    <form id="enhancedDeleteForm" method="POST" style="display:none;">
        @csrf @method('DELETE')
        <input type="hidden" name="delete_mikrotik" id="deleteMikrotikFlag" value="0">
    </form>

@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.tailwindcss.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
            margin-top: 1rem;
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .dataTables_wrapper .dataTables_info {
            padding-left: 1rem;
            padding-bottom: 1rem;
        }

        .dataTables_wrapper .dataTables_paginate {
            padding-right: 1rem;
            padding-bottom: 1rem;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.tailwindcss.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        var mapAdd, markerAdd, mapEdit, markerEdit;
        var defaultLat = -6.200000, defaultLng = 106.816666;

        $(document).ready(function () {
            $('#tableCust').DataTable({ responsive: true });

            // Logic Open Modal Edit via jQuery -> Alpine
            $('#tableCust').on('click', '.btn-edit', function () {
                let id = $(this).data('id');
                let rawLat = $(this).data('lat');
                let rawLng = $(this).data('lng');
                let lat = rawLat ? rawLat : defaultLat;
                let lng = rawLng ? rawLng : defaultLng;

                $('#editInet').val($(this).data('internet'));
                $('#editName').val($(this).data('name'));
                $('#editPhone').val($(this).data('phone'));
                $('#editPrice').val($(this).data('price'));
                $('#editOperator').val($(this).data('operator'));
                $('#editProfile').val($(this).data('profile'));
                $('#editAddress').val($(this).data('address'));
                $('#editNotes').val($(this).data('notes'));
                $('#editLat').val(rawLat);
                $('#editLng').val(rawLng);
                $('#formEdit').attr('action', '/customers/' + id);

                // Trigger Alpine Modal
                window.dispatchEvent(new CustomEvent('open-edit-modal'));

                // Init Map with delay for modal animation
                setTimeout(function () {
                    if (mapEdit) { mapEdit.remove(); mapEdit = null; }
                    mapEdit = L.map('mapEdit').setView([lat, lng], 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(mapEdit);
                    markerEdit = L.marker([lat, lng], { draggable: true }).addTo(mapEdit);
                    markerEdit.on('dragend', function (e) { var latlng = markerEdit.getLatLng(); $('#editLat').val(latlng.lat); $('#editLng').val(latlng.lng); });
                    mapEdit.on('click', function (e) { markerEdit.setLatLng(e.latlng); $('#editLat').val(e.latlng.lat); $('#editLng').val(e.latlng.lng); });
                    mapEdit.invalidateSize();
                }, 350);
            });

            // Watch Alpine Modal State Changes for Add Map
            // We use a small interval or Alpine's @click to init map, but cleaner is to listen to the button click that sets showAddModal = true
            $('[x-on\\:click="showAddModal = true"], button:contains("Tambah Baru")').click(function () {
                setTimeout(function () {
                    if (!mapAdd) {
                        mapAdd = L.map('mapAdd').setView([defaultLat, defaultLng], 13);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(mapAdd);
                        markerAdd = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(mapAdd);
                        markerAdd.on('dragend', function (e) { var latlng = markerAdd.getLatLng(); $('#latAdd').val(latlng.lat); $('#lngAdd').val(latlng.lng); });
                        mapAdd.on('click', function (e) { markerAdd.setLatLng(e.latlng); $('#latAdd').val(e.latlng.lat); $('#lngAdd').val(e.latlng.lng); });
                    }
                    mapAdd.invalidateSize();
                }, 350);
            });

            // Map Edit Lat/Lng Sync
            $('#editLat, #editLng').on('change', function () {
                let inputLat = $('#editLat').val(), inputLng = $('#editLng').val();
                if (inputLat && inputLng && mapEdit && markerEdit) {
                    let newLatLng = new L.LatLng(inputLat, inputLng);
                    markerEdit.setLatLng(newLatLng);
                    mapEdit.panTo(newLatLng);
                }
            });
        });

        function generateRandomInet() { document.getElementById('addInetNum').value = Math.floor(10000000 + Math.random() * 90000000); }
        function generateRandomInetEdit() { document.getElementById('editInet').value = Math.floor(10000000 + Math.random() * 90000000); }
        function startSync() {
            $('#syncInitial').hide(); $('#syncProgress').show();
            $.ajax({
                url: "{{ route('sync.list') }}", type: "GET",
                success: function (res) {
                    if (res.status === 'success') processQueue(res.data, res.total, 0);
                    else { alert(res.message); location.reload(); }
                },
                error: function () { alert("Koneksi Error"); }
            });
        }
        function processQueue(secrets, total, index) {
            if (index >= total) { $('#progressBar').css('width', '100%'); $('#syncProgress').hide(); $('#syncDone').show(); return; }
            let item = secrets[index];
            let percent = Math.round(((index + 1) / total) * 100);
            $('#progressBar').css('width', percent + '%');
            $('#syncStatusText').text("Memproses: " + item.name);
            $.ajax({
                url: "{{ route('sync.process') }}", type: "POST",
                data: { _token: $('meta[name="csrf-token"]').attr('content'), secret: item },
                success: function (res) {
                    let badge = res.status === 'created' ? '<span class="text-green-600 font-bold">[NEW]</span>' : '<span class="text-blue-600 font-bold">[UPD]</span>';
                    $(`#syncLog`).prepend(`<li>` + badge + ` ` + res.name + `</li>`);
                    processQueue(secrets, total, index + 1);
                },
                error: function (xhr) {
                    let res = xhr.responseJSON;
                    if (res && res.stop) {
                        $(`#syncLog`).prepend(`<li class="text-rose-600 font-bold">[STOP] ` + res.message + `</li>`);
                        $(`#syncStatusText`).text(`Sinkronisasi Terhenti: ` + res.message).addClass(`text-rose-600`);
                        $(`#syncDone`).show();
                        return;
                    }
                    processQueue(secrets, total, index + 1);
                }
            });
        }

        // Enhanced Delete with SweetAlert2
        function confirmDelete(id, name) {
            Swal.fire({
                title: 'Hapus Pelanggan?',
                text: "Anda akan menghapus " + name + ". Pilih metode penghapusan:",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5', // Primary
                cancelButtonColor: '#f43f5e', // Rose
                confirmButtonText: '<i class="fas fa-database mr-2"></i> Database Saja',
                cancelButtonText: '<i class="fas fa-network-wired mr-2"></i> DB + Mikrotik',
                showDenyButton: true,
                denyButtonText: 'Batal',
                reverseButtons: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Database Only
                    submitDelete(id, '0');
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    // DB + Mikrotik
                    Swal.fire({
                        title: 'Konfirmasi Akhir',
                        text: "Secret PPPoE di Mikrotik juga akan dihapus. Lanjutkan?",
                        icon: 'error',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Hapus Keduanya',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#f43f5e'
                    }).then((final) => {
                        if (final.isConfirmed) submitDelete(id, '1');
                    });
                }
            });
        }

        function submitDelete(id, mikrotikFlag) {
            let form = $('#enhancedDeleteForm');
            form.attr('action', '/customers/' + id);
            $('#deleteMikrotikFlag').val(mikrotikFlag);
            form.submit();
        }
    </script>
@endpush