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
                    this.passwordPlaceholder = '******';
                },

                openEditModal(user) {
                    this.showModal = true;
                    this.editMode = true;
                    this.formAction = '/users/' + user.id; // Assuming standard resource route
                    this.formMethod = 'POST'; // Laravel method spoofing handled inside form
                    this.userId = user.id;
                    this.name = user.name;
                    this.email = user.email;
                    this.role = user.role;
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
                                Role</th>
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
                                    @if($user->role == 'admin')
                                        <span
                                            class="inline-flex items-center rounded-full bg-rose-50 dark:bg-rose-900/30 px-2 py-1 text-xs font-medium text-rose-700 dark:text-rose-400 ring-1 ring-inset ring-rose-600/20 dark:ring-rose-500/30">ADMINISTRATOR</span>
                                    @else
                                        <span
                                            class="inline-flex items-center rounded-full bg-green-50 dark:bg-green-900/30 px-2 py-1 text-xs font-medium text-green-700 dark:text-green-400 ring-1 ring-inset ring-green-600/20 dark:ring-green-500/30">OPERATOR</span>
                                    @endif
                                </td>
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
                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline"
                                                onsubmit="return confirm('Yakin hapus user {{ $user->name }}?');">
                                                @csrf @method('DELETE')
                                                <button
                                                    class="text-red-600 hover:text-red-700 dark:hover:text-red-400 p-1.5 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                                    title="Hapus">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
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
                                <div>
                                    <label class="block text-sm font-medium text-slate-900 dark:text-white mb-1">Role / Hak
                                        Akses</label>
                                    <select name="role" x-model="role"
                                        class="block w-full rounded-md border-0 py-1.5 text-slate-900 dark:text-white dark:bg-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
                                        required>
                                        <option value="operator">Operator (Hanya Billing)</option>
                                        <option value="admin">Administrator (Full Akses)</option>
                                    </select>
                                </div>
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