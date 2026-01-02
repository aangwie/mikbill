@extends('layouts.app2')

@section('title', 'Manajemen Router')
@section('header', 'Router Mikrotik')
@section('subheader', 'Konfigurasi koneksi ke Router Mikrotik.')

@section('content')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column: Form / Request Activation -->
        <div class="lg:col-span-1">
            @if(auth()->user()->is_activated)
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 sticky top-24 overflow-hidden">
                    <div class="bg-primary-600 dark:bg-primary-700 px-6 py-4 border-b border-primary-500 dark:border-primary-600">
                        <h3 class="text-base font-bold text-white flex items-center" id="formTitle">
                            <i class="fas fa-plus-circle mr-2"></i> Tambah / Edit Router
                        </h3>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('router.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id" id="inputId">

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-900 dark:text-white mb-1">Label / Nama Router</label>
                                    <input type="text" name="label" id="inputLabel"
                                        class="block w-full rounded-md border-0 py-1.5 text-slate-900 dark:text-white dark:bg-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
                                        placeholder="Cth: Router Utama" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-900 dark:text-slate-300 mb-1">IP Address (Host)</label>
                                    <input type="text" name="host" id="inputHost"
                                        class="block w-full rounded-md border-0 py-1.5 text-slate-900 dark:text-white dark:bg-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
                                        placeholder="192.168.88.1" required>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-900 dark:text-slate-300 mb-1">User API</label>
                                        <input type="text" name="username" id="inputUser"
                                            class="block w-full rounded-md border-0 py-1.5 text-slate-900 dark:text-white dark:bg-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
                                            placeholder="admin" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-900 dark:text-slate-300 mb-1">Port API</label>
                                        <input type="number" name="port" id="inputPort"
                                            class="block w-full rounded-md border-0 py-1.5 text-slate-900 dark:text-white dark:bg-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
                                            value="8728" required>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-900 dark:text-slate-300 mb-1">Password</label>
                                    <input type="password" name="password" id="inputPass"
                                        class="block w-full rounded-md border-0 py-1.5 text-slate-900 dark:text-white dark:bg-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
                                        placeholder="******">
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">*Kosongkan jika tidak ingin mengubah password saat
                                        edit.</p>
                                </div>
                            </div>

                            <div class="mt-6 flex flex-col gap-2">
                                <button type="submit" id="btnSave"
                                    class="w-full justify-center rounded-md bg-primary-600 hover:bg-primary-500 text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 px-3 py-2 text-sm font-bold transition-all">
                                    Simpan Konfigurasi
                                </button>
                                <button type="button" id="btnCancel" onclick="resetForm()"
                                    class="hidden w-full justify-center rounded-md bg-white dark:bg-slate-700 px-3 py-2 text-sm font-semibold text-slate-900 dark:text-slate-200 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 hover:bg-slate-50 dark:hover:bg-slate-600 transition-all">
                                    Batal Edit
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border-t-4 border-amber-500 overflow-hidden sticky top-24">
                    <div class="p-8 text-center">
                        <div class="mx-auto h-20 w-20 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center text-amber-600 dark:text-amber-400 text-4xl mb-6">
                            <i class="fas fa-lock"></i>
                        </div>
                        <h3 class="text-xl font-extrabold text-slate-900 dark:text-white mb-2">Fitur Terkunci</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-8 leading-relaxed">
                            Akun Anda belum diaktifkan oleh Superadmin untuk mengakses manajemen router.
                            Silakan ajukan permintaan aktivasi di bawah ini.
                        </p>

                        <form action="{{ route('request.activation') }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-amber-500 px-6 py-4 text-sm font-bold text-white shadow-lg shadow-amber-500/30 hover:bg-amber-600 hover:-translate-y-0.5 transition-all duration-200">
                                <i class="fas fa-paper-plane mr-2"></i> AJUKAN AKTIVASI
                            </button>
                        </form>

                        <div class="mt-8 pt-8 border-t border-slate-100 dark:border-slate-700">
                            <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 tracking-widest uppercase">Estimasi Waktu</p>
                            <p class="text-sm font-medium text-slate-600 dark:text-slate-300 mt-1">Biasanya dalam 1-24 jam kerja.</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Column: Table -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm ring-1 ring-slate-900/5 dark:ring-slate-700/50 overflow-hidden">
                <div class="border-b border-slate-200 dark:border-slate-700 px-4 py-5 sm:px-6 bg-slate-50/50 dark:bg-slate-900/50">
                    <h3 class="text-base font-semibold leading-6 text-slate-900 dark:text-white">Daftar Konfigurasi Tersimpan</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-700">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                                    Status</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                                    Label</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                                    Host & Port</th>
                                <th scope="col" class="relative px-6 py-3 text-right text-slate-500 dark:text-slate-300">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach($routers as $r)
                                <tr class="{{ $r->is_active ? 'bg-indigo-50/50 dark:bg-indigo-900/20' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($r->is_active)
                                            <span
                                                class="inline-flex items-center rounded-full bg-green-50 dark:bg-green-900/30 px-2 py-1 text-xs font-medium text-green-700 dark:text-green-400 ring-1 ring-inset ring-green-600/20 dark:ring-green-500/30">
                                                <div class="h-1.5 w-1.5 rounded-full bg-green-500 mr-1.5"></div> AKTIF
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-700 px-2 py-1 text-xs font-medium text-slate-600 dark:text-slate-400 ring-1 ring-inset ring-slate-500/10 dark:ring-slate-400/20">Cadangan</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $r->label ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-slate-900 dark:text-white">{{ $r->host }}:{{ $r->port }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">User: {{ $r->username }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        @if(auth()->user()->is_activated)
                                            <div class="flex justify-end gap-2">
                                                @if(!$r->is_active)
                                                    <form action="{{ route('router.activate', $r->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit"
                                                            class="p-1.5 rounded-md text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors"
                                                            title="Gunakan Router Ini">
                                                            <i class="fas fa-power-off"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                <button class="p-1.5 rounded-md text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors"
                                                    onclick="editRouter({{ json_encode($r) }})" title="Edit">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </button>

                                                @if(!$r->is_active)
                                                    <form action="{{ route('router.destroy', $r->id) }}" method="POST"
                                                        onsubmit="return confirm('Hapus konfigurasi ini?')">
                                                        @csrf @method('DELETE')
                                                        <button class="p-1.5 rounded-md text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                                            title="Hapus">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <button class="p-1.5 rounded-md text-slate-300 cursor-not-allowed" disabled><i
                                                            class="fas fa-trash-alt"></i></button>
                                                @endif
                                            </div>
                                        @else
                                            <div class="text-slate-400 dark:text-slate-600 text-xs italic">
                                                <i class="fas fa-lock text-[10px] mr-1"></i> Terkunci
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function editRouter(data) {
            document.getElementById('formTitle').innerHTML = '<i class="fas fa-edit mr-2"></i> Edit Router: ' + data.label;
            document.getElementById('inputId').value = data.id;
            document.getElementById('inputLabel').value = data.label;
            document.getElementById('inputHost').value = data.host;
            document.getElementById('inputUser').value = data.username;
            document.getElementById('inputPort').value = data.port;
            document.getElementById('inputPass').value = '';

            const btnSave = document.getElementById('btnSave');
            btnSave.innerText = 'Update Perubahan';
            btnSave.classList.remove('bg-primary-600', 'hover:bg-primary-500');
            btnSave.classList.add('bg-amber-600', 'hover:bg-amber-500');

            document.getElementById('btnCancel').classList.remove('hidden');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function resetForm() {
            document.getElementById('formTitle').innerHTML = '<i class="fas fa-plus-circle mr-2"></i> Tambah / Edit Router';
            document.getElementById('inputId').value = '';
            document.getElementById('inputLabel').value = '';
            document.getElementById('inputHost').value = '';
            document.getElementById('inputUser').value = '';
            document.getElementById('inputPort').value = '8728';
            document.getElementById('inputPass').value = '';

            const btnSave = document.getElementById('btnSave');
            btnSave.innerText = 'Simpan Konfigurasi';
            btnSave.classList.add('bg-primary-600', 'hover:bg-primary-500');
            btnSave.classList.remove('bg-amber-600', 'hover:bg-amber-500');

            document.getElementById('btnCancel').classList.add('hidden');
        }
    </script>

@endsection