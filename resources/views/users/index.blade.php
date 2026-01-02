@extends('layouts.app2')

@section('title', 'Manajemen User')
@section('header', 'Manajemen User')
@section('subheader', 'Kelola akun administrator dan operator sistem.')

@section('content')

    <div x-data="{ 
                showModal: false, 
                editMode: false, 
                formAction: '{{ route('users.store') }}', 
                formMethod: 'POST',
                userId: '',
                name: '',
                email: '',
                role: 'operator',
                is_activated: false,
                is_verified: false,
                passwordPlaceholder: '******',

                openAddModal() {
                    this.showModal = true;
                    this.editMode = false;
                    this.formAction = '{{ route('users.store') }}';
                    this.formMethod = 'POST';
                    this.userId = '';
                    this.name = '';
                    this.email = '';
                    this.role = 'operator';
                    this.is_activated = false;
                    this.is_verified = false;
                    this.passwordPlaceholder = '******';
                },

                openEditModal(user) {
                    this.showModal = true;
                    this.editMode = true;
                    this.formAction = '/users/' + user.id; 
                    this.formMethod = 'POST'; 
                    this.userId = user.id;
                    this.name = user.name;
                    this.email = user.email;
                    this.role = user.role;
                    this.is_activated = !!user.is_activated;
                    this.is_verified = !!user.email_verified_at;
                    this.passwordPlaceholder = 'Kosongkan jika tidak diganti';
                }
            }">

        <!-- Toolbar -->
        <div class="mb-6 flex items-center justify-between">
            <div class="hidden sm:block">
                <h3 class="text-sm font-semibold text-slate-500 dark:text-slate-400">Daftar Pengguna</h3>
            </div>
            <button @click="openAddModal()"
                class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-primary-500 transition-all">
                <i class="fas fa-plus mr-2"></i> Tambah User
            </button>
        </div>

        <!-- User Table -->
        <div
            class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-700/50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                Nama User</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                Email Login</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                Role & Akses</th>
                            @if(auth()->user()->isSuperAdmin())
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                Informasi Paket</th>
                            @endif
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                Terdaftar</th>
                            <th scope="col" class="relative px-6 py-3 text-right text-slate-500 dark:text-slate-400">Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                        @foreach($users as $user)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="h-8 w-8 rounded-full bg-primary-100 dark:bg-primary-900/50 flex items-center justify-center text-primary-600 dark:text-primary-400 font-bold mr-3">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $user->name }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-400">
                                    {{ $user->email }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($user->role == 'superadmin')
                                        <span class="inline-flex items-center rounded-full bg-purple-50 dark:bg-purple-900/30 px-2 py-1 text-xs font-medium text-purple-700 dark:text-purple-400 ring-1 ring-inset ring-purple-600/20 dark:ring-purple-500/30">SUPERADMIN</span>
                                    @elseif($user->role == 'admin')
                                        <span class="inline-flex items-center rounded-full bg-rose-50 dark:bg-rose-900/30 px-2 py-1 text-xs font-medium text-rose-700 dark:text-rose-400 ring-1 ring-inset ring-rose-600/20 dark:ring-rose-500/30">ADMINISTRATOR</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-green-50 dark:bg-green-900/30 px-2 py-1 text-xs font-medium text-green-700 dark:text-green-400 ring-1 ring-inset ring-green-600/20 dark:ring-green-500/30">OPERATOR</span>
                                    @endif

                                    <div class="mt-1 space-y-1">
                                        <!-- Email Status -->
                                        @if($user->email_verified_at)
                                            <span class="inline-flex items-center text-[10px] font-bold text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 px-1.5 py-0.5 rounded">
                                                <i class="fas fa-envelope mr-1"></i> EMAIL VERIFIKASI
                                            </span>
                                        @else
                                            <span class="inline-flex items-center text-[10px] font-bold text-amber-500 bg-amber-50 dark:bg-amber-900/20 px-1.5 py-0.5 rounded">
                                                <i class="fas fa-envelope mr-1"></i> BELUM VERIFIKASI
                                            </span>
                                        @endif

                                        <!-- Router Status -->
                                        @if($user->is_activated)
                                            <span class="inline-flex items-center text-[10px] font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 px-1.5 py-0.5 rounded">
                                                <i class="fas fa-microchip mr-1"></i> ROUTER AKTIF
                                            </span>
                                        @else
                                            <span class="inline-flex items-center text-[10px] font-bold text-red-500 bg-red-50 dark:bg-red-900/20 px-1.5 py-0.5 rounded">
                                                <i class="fas fa-microchip mr-1"></i> ROUTER NONAKTIF
                                            </span>
                                        @endif
                                    </div>
                                    
                                    @if(auth()->user()->isSuperAdmin() && $user->parent)
                                        <div class="text-[10px] text-slate-400 mt-1 uppercase tracking-tighter">Under: {{ $user->parent->name }}</div>
                                    @endif
                                </td>

                                @if(auth()->user()->isSuperAdmin())
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($user->role === 'admin')
                                        @if($user->plan)
                                            <div class="text-xs font-bold text-slate-900 dark:text-white">{{ $user->plan->name }}</div>
                                            <div class="text-[10px] text-slate-500 dark:text-slate-400">
                                                Berakhir: {{ $user->plan_expires_at ? $user->plan_expires_at->format('d/m/Y') : '-' }}
                                            </div>
                                            @if($user->plan_expires_at && $user->plan_expires_at->isPast())
                                                <span class="text-[10px] text-red-500 font-bold uppercase">Sudah Kadaluarsa</span>
                                            @endif
                                        @else
                                            <span class="text-[10px] text-slate-400 italic">Tanpa Paket</span>
                                        @endif
                                    @else
                                        <span class="text-[10px] text-slate-400">-</span>
                                    @endif
                                </td>
                                @endif

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                                    {{ $user->created_at->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <button @click="openEditModal({{ json_encode($user) }})"
                                            class="text-amber-500 hover:text-amber-700 dark:hover:text-amber-400 p-1.5 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition-colors"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        @if(auth()->user()->id != $user->id)
                                            @if(auth()->user()->isSuperAdmin() && $user->role === 'admin' && $user->plan_id)
                                                <form action="{{ route('users.suspend', $user->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" 
                                                        class="{{ $user->is_activated ? 'text-red-500 hover:text-red-700 hover:bg-red-50' : 'text-emerald-500 hover:text-emerald-700 hover:bg-emerald-50' }} p-1.5 rounded-lg transition-colors"
                                                        title="{{ $user->is_activated ? 'Suspend Paket' : 'Aktifkan Paket' }}">
                                                        <i class="fas {{ $user->is_activated ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                                    </button>
                                                </form>

                                                <button type="button" 
                                                    onclick="confirmRemovePlan('{{ $user->id }}', '{{ addslashes($user->name) }}')"
                                                    class="text-slate-500 hover:text-red-600 p-1.5 hover:bg-red-50 rounded-lg transition-colors"
                                                    title="Hapus/Reset Paket">
                                                    <i class="fas fa-box-open"></i>
                                                </button>
                                                <form id="remove-plan-form-{{ $user->id }}" action="{{ route('users.removePlan', $user->id) }}" method="POST" style="display: none;">
                                                    @csrf
                                                </form>
                                            @elseif(auth()->user()->isSuperAdmin() && $user->role === 'admin')
                                                <form action="{{ route('users.suspend', $user->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" 
                                                        class="{{ $user->is_activated ? 'text-red-500 hover:text-red-700 hover:bg-red-50' : 'text-emerald-500 hover:text-emerald-700 hover:bg-emerald-50' }} p-1.5 rounded-lg transition-colors"
                                                        title="{{ $user->is_activated ? 'Suspend Paket' : 'Aktifkan Paket' }}">
                                                        <i class="fas {{ $user->is_activated ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            <button type="button" 
                                                onclick="confirmDelete('{{ $user->id }}', '{{ addslashes($user->name) }}', '{{ $user->role }}')"
                                                class="text-red-600 hover:text-red-700 dark:hover:text-red-400 p-1.5 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                                title="Hapus">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                            <form id="delete-form-{{ $user->id }}" action="{{ route('users.destroy', $user->id) }}" method="POST" style="display: none;">
                                                @csrf @method('DELETE')
                                            </form>
                                        @else
                                            <button class="text-slate-300 cursor-not-allowed p-1.5" disabled title="Akun Sendiri"><i
                                                    class="fas fa-trash-alt"></i></button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

@push('scripts')
<script>
    function confirmRemovePlan(id, name) {
        Swal.fire({
            title: "Hapus Paket?",
            text: "Apakah Anda yakin ingin menghapus/reset paket aktif untuk " + name + "? Ini akan membatalkan akses fitur router mereka.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Ya, Reset Paket",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                var form = document.getElementById('remove-plan-form-' + id);
                if(form) {
                    form.submit();
                }
            }
        });
    }

    function confirmDelete(id, name, role) {
        console.log("Attempting to delete user:", {id, name, role});
        
        let title = "Hapus User?";
        let text = "Apakah Anda yakin ingin menghapus " + name + "?";
        let confirmButtonText = "Ya, Hapus";

        if (role === 'admin') {
            title = "HAPUS ADMINISTRATOR?";
            text = "PERINGATAN: Menghapus Admin akan menghapus SELURUH data terkait (Pelanggan, Tagihan, Pengaturan Router, dsb). Ini tidak dapat dibatalkan!";
            confirmButtonText = "Lanjut ke Konfirmasi Akhir";
        }

        Swal.fire({
            title: title,
            text: text,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: confirmButtonText,
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                if (role === 'admin') {
                    // Double confirmation for Admin
                    Swal.fire({
                        title: "KONFIRMASI TERAKHIR",
                        text: "Semua data layanan admin " + name + " akan segera DIHAPUS PERMANEN. Benar-benar ingin lanjut?",
                        icon: "error",
                        showCancelButton: true,
                        confirmButtonColor: "#ff0000",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "SAYA YAKIN, HAPUS SEMUA DATA",
                        cancelButtonText: "Batalkan"
                    }).then((finalResult) => {
                        if (finalResult.isConfirmed) {
                            var form = document.getElementById('delete-form-' + id);
                            if(form) {
                                form.submit();
                            } else {
                                console.error("Form not found: delete-form-" + id);
                            }
                        }
                    });
                } else {
                    var form = document.getElementById('delete-form-' + id);
                    if(form) {
                        form.submit();
                    } else {
                        console.error("Form not found: delete-form-" + id);
                    }
                }
            }
        });
    }
</script>
@endpush

        <!-- Modal (Alpine) -->
        <div x-show="showModal" class="relative z-50" style="display:none;">
            <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm" x-transition.opacity></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-800 text-left shadow-xl transition-all sm:w-full sm:max-w-md"
                        @click.away="showModal = false" x-transition.scale>

                        <div class="bg-primary-600 dark:bg-primary-700 px-4 py-3 sm:px-6 flex justify-between items-center">
                            <h3 class="text-base font-bold leading-6 text-white"
                                x-text="editMode ? 'Edit User' : 'Tambah User Baru'"></h3>
                            <button @click="showModal = false" class="text-white hover:text-primary-200"><i
                                    class="fas fa-times"></i></button>
                        </div>

                        <form :action="formAction" method="POST">
                            @csrf
                            <template x-if="editMode">
                                <input type="hidden" name="_method" value="PUT">
                            </template>

                            <div class="px-4 py-5 sm:p-6 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-900 dark:text-white mb-1">Nama
                                        Lengkap</label>
                                    <input type="text" name="name" x-model="name"
                                        class="block w-full rounded-md border-0 py-1.5 text-slate-900 dark:text-white dark:bg-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
                                        required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-900 dark:text-white mb-1">Email
                                        (Login)</label>
                                    <input type="email" name="email" x-model="email"
                                        class="block w-full rounded-md border-0 py-1.5 text-slate-900 dark:text-white dark:bg-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
                                        required>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-slate-900 dark:text-white mb-1">Password</label>
                                    <input type="password" name="password"
                                        class="block w-full rounded-md border-0 py-1.5 text-slate-900 dark:text-white dark:bg-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
                                        :placeholder="passwordPlaceholder">
                                </div>
                                
                                @if(auth()->user()->isSuperAdmin())
                                <div>
                                    <label class="block text-sm font-medium text-slate-900 dark:text-white mb-1">Role / Hak
                                        Akses</label>
                                    <select name="role" x-model="role"
                                        class="block w-full rounded-md border-0 py-1.5 text-slate-900 dark:text-white dark:bg-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
                                        required>
                                        <option value="operator">Operator (Staff)</option>
                                        <option value="admin">Administrator (Tenant Owner)</option>
                                        <option value="superadmin">Superadmin (Global access)</option>
                                    </select>
                                </div>

                                <div x-show="role == 'operator'">
                                    <label class="block text-sm font-medium text-slate-900 dark:text-white mb-1">Assign to Admin</label>
                                    <select name="parent_id"
                                        class="block w-full rounded-md border-0 py-1.5 text-slate-900 dark:text-white dark:bg-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
                                        <option value="">-- Tanpa Admin (Global) --</option>
                                        @foreach($admins as $admin)
                                            <option value="{{ $admin->id }}">{{ $admin->name }} ({{ $admin->email }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                @endif

                                <div class="space-y-4">
                                    <div class="p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg border border-slate-200 dark:border-slate-600">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-bold text-slate-900 dark:text-white">Status Verifikasi Email</p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">Izinkan pengguna untuk login ke dashboard.</p>
                                            </div>
                                            @if(auth()->user()->isSuperAdmin())
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="is_verified" value="1" x-model="is_verified" class="sr-only peer">
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-600"></div>
                                            </label>
                                            @else
                                                <div class="text-xs font-bold" :class="is_verified ? 'text-emerald-500' : 'text-amber-500'" x-text="is_verified ? 'TERVERIFIKASI' : 'BELUM VERIFIKASI'"></div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg border border-slate-200 dark:border-slate-600">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-bold text-slate-900 dark:text-white">Aktivasi Fitur Router</p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">Berikan akses untuk menambah dan mengelola router.</p>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="is_activated" value="1" x-model="is_activated" class="sr-only peer">
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                @if(!auth()->user()->isSuperAdmin())
                                    <input type="hidden" name="role" value="operator">
                                    <div class="p-3 bg-slate-50 dark:bg-slate-700 rounded-lg">
                                        <p class="text-xs text-slate-500 uppercase font-bold">Role Otomatis</p>
                                        <p class="text-sm text-slate-700 dark:text-slate-300">Operator</p>
                                    </div>
                                @endif
                            </div>

                            <div class="bg-slate-50 dark:bg-slate-700/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <button type="submit"
                                    class="inline-flex w-full justify-center rounded-lg bg-primary-600 px-3 py-2 text-sm font-bold text-white shadow-sm hover:bg-primary-500 sm:ml-3 sm:w-auto"
                                    x-text="editMode ? 'Simpan Perubahan' : 'Simpan User'"></button>
                                <button type="button" @click="showModal = false"
                                    class="mt-3 inline-flex w-full justify-center rounded-lg bg-white dark:bg-slate-700 px-3 py-2 text-sm font-semibold text-slate-900 dark:text-slate-200 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 hover:bg-slate-50 dark:hover:bg-slate-600 sm:mt-0 sm:w-auto">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection