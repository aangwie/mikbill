@extends('layouts.app2')

@section('title', 'Manajemen Paket Langganan')
@section('header', 'Manajemen Paket')
@section('subheader', 'Atur paket langganan, batasan fitur, dan harga.')

@section('content')
    <div x-data="{ showModal: false, editMode: false, currentPlan: {} }">
        <!-- Toolbar -->
        <div class="mb-8 flex justify-between items-center">
            <button @click="showModal = true; editMode = false; currentPlan = {}"
                class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 transition-all">
                <i class="fas fa-plus mr-2"></i> Tambah Paket Baru
            </button>
        </div>

        <!-- Plans Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($plans as $plan)
                <div
                    class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden flex flex-col">
                    <div class="p-6 flex-1">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ $plan->name }}</h3>
                            <div class="flex gap-2">
                                <button @click="showModal = true; editMode = true; currentPlan = {{ json_encode($plan) }}"
                                    class="p-1.5 text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-md transition-colors">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('plans.destroy', $plan->id) }}" method="POST"
                                    onsubmit="return confirm('Hapus paket ini?')">
                                    @csrf @method('DELETE')
                                    <button
                                        class="p-1.5 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="space-y-3 mb-6">
                            <div class="flex items-center text-sm text-slate-600 dark:text-slate-400">
                                <i class="fas fa-server w-5 text-primary-500"></i>
                                <span>Maksimal <strong>{{ $plan->max_routers }}</strong> Router</span>
                            </div>
                            <div class="flex items-center text-sm text-slate-600 dark:text-slate-400">
                                <i class="fas fa-users w-5 text-indigo-500"></i>
                                <span>Maksimal <strong>{{ $plan->max_customers }}</strong> Pelanggan</span>
                            </div>
                            <div class="flex items-center text-sm text-slate-600 dark:text-slate-400">
                                <i
                                    class="fab fa-whatsapp w-5 {{ $plan->wa_gateway ? 'text-green-500' : 'text-slate-300' }}"></i>
                                <span>WhatsApp Gateway:
                                    <strong>{{ $plan->wa_gateway ? 'Tersedia' : 'Tidak Tersedia' }}</strong></span>
                            </div>
                        </div>

                        <div class="border-t border-slate-100 dark:border-slate-700 pt-4">
                            <div class="grid grid-cols-3 gap-2 text-center">
                                <div>
                                    <p class="text-[10px] uppercase font-bold text-slate-400">Bulanan</p>
                                    <p class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                        Rp{{ number_format($plan->price_monthly, 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase font-bold text-slate-400">Semester</p>
                                    <p class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                        Rp{{ number_format($plan->price_semester, 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase font-bold text-slate-400">Tahunan</p>
                                    <p class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                        Rp{{ number_format($plan->price_annual, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Modal Form -->
        <div x-show="showModal" class="relative z-50" x-cloak>
            <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-800 shadow-xl transition-all sm:w-full sm:max-w-lg"
                        @click.away="showModal = false">
                        <form :action="editMode ? '/plans/' + currentPlan.id : '{{ route('plans.store') }}'" method="POST">
                            @csrf
                            <template x-if="editMode">
                                <input type="hidden" name="_method" value="PUT">
                            </template>

                            <div class="px-6 py-6">
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-6"
                                    x-text="editMode ? 'Edit Paket' : 'Tambah Paket Baru'"></h3>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Nama
                                            Paket</label>
                                        <input type="text" name="name" x-model="currentPlan.name" required
                                            class="mt-1 block w-full rounded-md border-slate-300 dark:bg-slate-700 dark:border-slate-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-slate-700 dark:text-slate-300">Maks.
                                                Router</label>
                                            <input type="number" name="max_routers" x-model="currentPlan.max_routers"
                                                required
                                                class="mt-1 block w-full rounded-md border-slate-300 dark:bg-slate-700 dark:border-slate-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-slate-700 dark:text-slate-300">Maks.
                                                Pelanggan</label>
                                            <input type="number" name="max_customers" x-model="currentPlan.max_customers"
                                                required
                                                class="mt-1 block w-full rounded-md border-slate-300 dark:bg-slate-700 dark:border-slate-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" name="wa_gateway" id="wa_gateway"
                                            :checked="currentPlan.wa_gateway == 1" value="1"
                                            class="rounded border-slate-300 text-primary-600 shadow-sm focus:ring-primary-500">
                                        <label for="wa_gateway"
                                            class="text-sm font-medium text-slate-700 dark:text-slate-300">WhatsApp Gateway
                                            (Include)</label>
                                    </div>

                                    <div class="grid grid-cols-3 gap-4">
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-slate-700 dark:text-slate-300 text-xs">Harga
                                                Bulanan</label>
                                            <input type="number" name="price_monthly" x-model="currentPlan.price_monthly"
                                                required
                                                class="mt-1 block w-full rounded-md border-slate-300 dark:bg-slate-700 dark:border-slate-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-slate-700 dark:text-slate-300 text-xs">Harga
                                                Semester</label>
                                            <input type="number" name="price_semester" x-model="currentPlan.price_semester"
                                                required
                                                class="mt-1 block w-full rounded-md border-slate-300 dark:bg-slate-700 dark:border-slate-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-slate-700 dark:text-slate-300 text-xs">Harga
                                                Tahunan</label>
                                            <input type="number" name="price_annual" x-model="currentPlan.price_annual"
                                                required
                                                class="mt-1 block w-full rounded-md border-slate-300 dark:bg-slate-700 dark:border-slate-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-slate-50 dark:bg-slate-700/50 px-6 py-4 flex justify-end gap-3">
                                <button type="button" @click="showModal = false"
                                    class="rounded-md bg-white dark:bg-slate-700 px-3 py-2 text-sm font-semibold text-slate-900 dark:text-slate-200 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 hover:bg-slate-50">Batal</button>
                                <button type="submit"
                                    class="rounded-md bg-primary-600 px-3 py-2 text-sm font-bold text-white shadow-sm hover:bg-primary-500"
                                    x-text="editMode ? 'Update Paket' : 'Simpan Paket'"></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection