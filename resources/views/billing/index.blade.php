@extends('layouts.app2')

@section('title', 'Billing & Tagihan')
@section('header', 'Billing & Kasir')
@section('subheader', 'Kelola tagihan pelanggan, pembayaran, dan invoice.')

@section('content')

    <div x-data="{ 
                                    showCreateModal: false, 
                                    showGenerateModal: false,
                                    showPayModal: false,
                                    showDeleteModal: false,
                                    showDueDateModal: false,
                                    selectedInvoices: [],
                                    toggleAll() {
                                        if (this.selectedInvoices.length === {{ count($invoices->where('status', 'unpaid')) }}) {
                                            this.selectedInvoices = [];
                                        } else {
                                            this.selectedInvoices = [
                                                @foreach($invoices as $inv)
                                                    @if($inv->status == 'unpaid')
                                                        {{ $inv->id }},
                                                    @endif
                                                @endforeach
                                            ];
                                        }
                                    }
                                }">

        <!-- Filter Bar -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <div class="bg-indigo-50 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 p-2 rounded-lg">
                    <i class="fas fa-filter"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white">Filter Tagihan</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Tampilkan berdasarkan periode</p>
                </div>
            </div>
            <form action="{{ route('billing.index') }}" method="GET"
                class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                @if(auth()->user()->role == 'superadmin')
                    <select name="admin_id"
                        class="block w-full sm:w-48 rounded-md border-0 py-1.5 text-slate-900 dark:text-white dark:bg-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
                        <option value="">Semua Admin</option>
                        @foreach($admins as $admin)
                            <option value="{{ $admin->id }}" {{ $selectedAdminId == $admin->id ? 'selected' : '' }}>
                                {{ $admin->name }}{{ $admin->id == auth()->id() ? ' (Self)' : '' }}
                            </option>
                        @endforeach
                    </select>
                @endif
                <select name="month"
                    class="block w-full sm:w-40 rounded-md border-0 py-1.5 text-slate-900 dark:text-white dark:bg-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                            {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                        </option>
                    @endfor
                </select>
                <select name="year"
                    class="block w-full sm:w-32 rounded-md border-0 py-1.5 text-slate-900 dark:text-white dark:bg-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
                    @for ($y = date('Y'); $y >= 2023; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <button type="submit"
                    class="inline-flex justify-center items-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 transition-all">
                    <i class="fas fa-search mr-2"></i> Tampilkan
                </button>
            </form>
        </div>

        <!-- Stats Overview -->
        <div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 p-6 shadow-lg shadow-indigo-500/20 text-white hover:shadow-xl transition-shadow group">
                <dt class="truncate text-sm font-medium text-indigo-100">Total Tagihan (Periode Ini)</dt>
                <dd class="mt-2 text-3xl font-bold tracking-tight text-white">
                    Rp {{ number_format($total_bill ?? 0, 0, ',', '.') }}
                </dd>
                <div
                    class="absolute right-4 top-4 text-white/10 group-hover:text-white/20 transition-all transform group-hover:scale-110">
                    <i class="fas fa-file-invoice-dollar fa-3x"></i>
                </div>
            </div>
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 p-6 shadow-lg shadow-emerald-500/20 text-white hover:shadow-xl transition-shadow group">
                <dt class="truncate text-sm font-medium text-emerald-100">Sudah Dibayar</dt>
                <dd class="mt-2 text-3xl font-bold tracking-tight text-white">
                    Rp {{ number_format($paid_bill ?? 0, 0, ',', '.') }}
                </dd>
                <div
                    class="absolute right-4 top-4 text-white/10 group-hover:text-white/20 transition-all transform group-hover:scale-110">
                    <i class="fas fa-check-circle fa-3x"></i>
                </div>
            </div>
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-rose-500 to-pink-600 p-6 shadow-lg shadow-rose-500/20 text-white hover:shadow-xl transition-shadow group">
                <dt class="truncate text-sm font-medium text-rose-100">Belum Dibayar</dt>
                <dd class="mt-2 text-3xl font-bold tracking-tight text-white">
                    Rp {{ number_format($unpaid_bill ?? 0, 0, ',', '.') }}
                </dd>
                <div
                    class="absolute right-4 top-4 text-white/10 group-hover:text-white/20 transition-all transform group-hover:scale-110">
                    <i class="fas fa-exclamation-circle fa-3x"></i>
                </div>
            </div>
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 p-6 shadow-lg shadow-amber-500/20 text-white hover:shadow-xl transition-shadow group">
                <dt class="truncate text-sm font-medium text-amber-100">Piutang Pelanggan</dt>
                <dd class="mt-2 text-3xl font-bold tracking-tight text-white">
                    Rp {{ number_format($total_piutang ?? 0, 0, ',', '.') }}
                </dd>
                <p class="mt-1 text-xs text-amber-200/80">Akumulasi bulan sebelumnya</p>
                <div
                    class="absolute right-4 top-4 text-white/10 group-hover:text-white/20 transition-all transform group-hover:scale-110">
                    <i class="fas fa-hand-holding-usd fa-3x"></i>
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex gap-2">
                <button @click="showCreateModal = true"
                    class="inline-flex items-center rounded-lg bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 transition-all">
                    <i class="fas fa-plus mr-2"></i> Buat Manual
                </button>
                @if(auth()->user()->role == 'admin' || auth()->user()->role == 'superadmin')
                    <button @click="showGenerateModal = true"
                        class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 transition-all">
                        <i class="fas fa-magic mr-2"></i> Generate Massal
                    </button>
                @endif
                <button @click="showPayModal = true" x-show="selectedInvoices.length > 0"
                    class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 transition-all ml-2"
                    style="display: none;" x-transition>
                    <i class="fas fa-check-double mr-2"></i> Bayar Sekaligus (<span
                        x-text="selectedInvoices.length"></span>)
                </button>

                <button @click="showDueDateModal = true" x-show="selectedInvoices.length > 0"
                    class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 transition-all ml-2"
                    style="display: none;" x-transition>
                    <i class="fas fa-calendar-alt mr-2"></i> Ubah Jatuh Tempo (<span
                        x-text="selectedInvoices.length"></span>)
                </button>

                <button @click="showDeleteModal = true" x-show="selectedInvoices.length > 0"
                    class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 transition-all ml-2"
                    style="display: none;" x-transition>
                    <i class="fas fa-trash-alt mr-2"></i> Hapus Tagihan (<span x-text="selectedInvoices.length"></span>)
                </button>
            </div>
        </div>

        <!-- Table Card -->
        <div
            class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm ring-1 ring-slate-900/5 dark:ring-slate-700/50 overflow-hidden">
            <div class="overflow-x-auto p-4">
                <table id="tableBilling" class="w-full text-left border-collapse">
                    <thead>
                        <th
                            class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider py-3 px-4 bg-slate-50 dark:bg-slate-700/50 rounded-l-lg">
                            <input type="checkbox" @click="toggleAll()"
                                :checked="selectedInvoices.length > 0 && selectedInvoices.length === {{ count($invoices->where('status', 'unpaid')) }}"
                                class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-600">
                        </th>
                        <th
                            class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider py-3 px-4 bg-slate-50 dark:bg-slate-700/50">
                            No. Invoice</th>
                        <th
                            class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider py-3 px-4 bg-slate-50 dark:bg-slate-700/50">
                            Pelanggan</th>
                        <th
                            class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider py-3 px-4 bg-slate-50 dark:bg-slate-700/50 hidden sm:table-cell">
                            Bulan/Tahun</th>
                        <th
                            class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider py-3 px-4 bg-slate-50 dark:bg-slate-700/50">
                            Tagihan</th>
                        <th
                            class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider py-3 px-4 bg-slate-50 dark:bg-slate-700/50">
                            Saldo</th>
                        <th
                            class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider py-3 px-4 bg-slate-50 dark:bg-slate-700/50">
                            Kurang Bayar</th>
                        <th
                            class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider py-3 px-4 bg-slate-50 dark:bg-slate-700/50">
                            Status</th>
                        <th
                            class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider py-3 px-4 bg-slate-50 dark:bg-slate-700/50 rounded-r-lg text-right">
                            Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach($invoices as $inv)
                            @php
                                $displayPrice = $inv->price > 0 ? $inv->price : ($inv->customer->monthly_price ?? 0);
                                $totalDue = $displayPrice + $inv->outstanding;
                                $customerSaldo = \App\Models\CustomerBalance::where('customer_id', $inv->customer_id)->sum('amount');
                                $accumulatedTunggakan = \App\Models\Invoice::where('customer_id', $inv->customer_id)
                                    ->where('status', 'unpaid')
                                    ->get()
                                    ->sum(function($i) {
                                        $p = $i->price > 0 ? $i->price : ($i->customer->monthly_price ?? 0);
                                        return $p - $i->paid_amount;
                                    });
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors group">
                                <td class="px-4 py-3 align-middle font-mono text-sm text-slate-600 dark:text-slate-300">
                                    @if($inv->status == 'unpaid')
                                        <input type="checkbox" value="{{ $inv->id }}" x-model.number="selectedInvoices"
                                            class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-600">
                                    @endif
                                </td>
                                <td class="px-4 py-3 align-middle font-mono text-sm text-slate-600 dark:text-slate-300">
                                    #INV-{{ str_pad($inv->id, 5, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="px-4 py-3 align-middle">
                                    <div class="font-medium text-slate-900 dark:text-white">
                                        {{ $inv->customer->name ?? 'Deleted User' }}
                                    </div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ $inv->customer->internet_number ?? '-' }}
                                    </div>
                                </td>
                                <td
                                    class="px-4 py-3 align-middle hidden sm:table-cell text-sm text-slate-600 dark:text-slate-300">
                                    {{ \Carbon\Carbon::parse($inv->due_date)->isoFormat('MMMM Y') }}
                                </td>
                                <td class="px-4 py-3 align-middle font-medium text-slate-700 dark:text-slate-200">
                                    Rp {{ number_format($displayPrice, 0, ',', '.') }}
                                    @if($inv->outstanding > 0)
                                        <div class="text-xs text-amber-500 mt-0.5">
                                            + Rp {{ number_format($inv->outstanding, 0, ',', '.') }} tunggakan
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 align-middle">
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-semibold {{ $customerSaldo > 0 ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400' }}">
                                        Rp {{ number_format($customerSaldo, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 align-middle">
                                    @if($accumulatedTunggakan > 0)
                                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-bold bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">
                                            Rp {{ number_format($accumulatedTunggakan, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-xs text-emerald-500">Lunas</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 align-middle">
                                    @if($inv->status == 'paid')
                                        <div class="flex items-center justify-center">
                                            <span class="h-3 w-3 rounded-full bg-green-500" title="Lunas"></span>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-center">
                                            <span class="h-3 w-3 rounded-full bg-red-500" title="Belum Bayar"></span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 align-middle text-right">
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('billing.print', $inv->id) }}" target="_blank"
                                            class="p-1.5 text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-md transition-colors"
                                            title="Print Invoice">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <button type="button"
                                            onclick="showHistory('{{ $inv->customer_id }}', '{{ addslashes($inv->customer->name) }}')"
                                            class="p-1.5 text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-md transition-colors"
                                            title="Riwayat Pembayaran">
                                            <i class="fas fa-list-alt"></i>
                                        </button>
                                        @if($inv->status == 'unpaid')
                                            @if($displayPrice == 0 && $accumulatedTunggakan <= 0)
                                                <button type="button" disabled
                                                    class="p-1.5 text-slate-400 bg-slate-100 dark:bg-slate-800 rounded-md cursor-not-allowed"
                                                    title="Tidak ada tagihan">
                                                    <i class="fas fa-cash-register"></i>
                                                </button>
                                            @elseif($inv->paid_amount > 0)
                                                <button type="button" disabled
                                                    class="p-1.5 text-slate-400 bg-slate-100 dark:bg-slate-800 rounded-md cursor-not-allowed"
                                                    title="Sudah dilakukan pembayaran sebagian. Batal pembayaran di Riwayat jika ingin mengubah.">
                                                    <i class="fas fa-cash-register"></i>
                                                </button>
                                            @else
                                                <button type="button"
                                                    onclick="bayarInvoice('{{ $inv->id }}', '{{ addslashes($inv->customer->name) }}', {{ $displayPrice }}, {{ $accumulatedTunggakan - $displayPrice }}, {{ $customerSaldo }})"
                                                    class="p-1.5 text-green-600 hover:text-green-700 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-md transition-colors"
                                                    title="Bayar">
                                                    <i class="fas fa-cash-register"></i>
                                                </button>
                                            @endif
                                        @else
                                            <form action="{{ route('billing.cancel', $inv->id) }}" method="POST" class="d-inline"
                                                onsubmit="return confirm('Batalkan pembayaran ini?');">
                                                @csrf
                                                <button
                                                    class="p-1.5 text-orange-500 hover:text-orange-600 hover:bg-orange-50 dark:hover:bg-orange-900/20 rounded-md transition-colors"
                                                    title="Batalkan Bayar">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('billing.destroy', $inv->id) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('Hapus Invoice ini permanen?');">
                                            @csrf @method('DELETE')
                                            <button
                                                class="p-1.5 text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors"
                                                title="Hapus Invoice">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODAL CREATE MANUAL (Alpine) -->
        <div x-show="showCreateModal" id="showCreateModalContainer" class="relative z-500" style="display:none;">
            <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-800 text-left shadow-xl transition-all sm:w-full sm:max-w-lg"
                        @click.away="showCreateModal = false">
                        <form action="{{ route('billing.store') }}" method="POST">
                            @csrf
                            <div class="bg-white dark:bg-slate-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                <h3 class="text-xl font-bold leading-6 text-slate-900 dark:text-white mb-6">Buat Tagihan
                                    Manual</h3>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-900 dark:text-slate-300">Pilih
                                            Pelanggan</label>
                                        <select name="customer_id"
                                            class="mt-1 block w-full rounded-md border-0 py-1.5 text-slate-900 dark:text-white dark:bg-slate-700 ring-1 ring-inset ring-slate-300 dark:ring-slate-600 focus:ring-2 focus:ring-primary-600 sm:text-sm sm:leading-6 select2-modal">
                                            @foreach($customers as $c)
                                                <option value="{{ $c->id }}">{{ $c->name }} - {{ $c->internet_number }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-slate-900 dark:text-slate-300">Bulan</label>
                                            <select id="manualMonth"
                                                class="mt-1 block w-full rounded-md border-0 py-1.5 text-slate-900 dark:text-white dark:bg-slate-700 ring-1 ring-inset ring-slate-300 dark:ring-slate-600 sm:text-sm">
                                                @for($i = 1; $i <= 12; $i++)
                                                    <option value="{{ $i }}" {{ date('n') == $i ? 'selected' : '' }}>
                                                        {{ DateTime::createFromFormat('!m', $i)->format('F') }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-slate-900 dark:text-slate-300">Tahun</label>
                                            <select id="manualYear"
                                                class="mt-1 block w-full rounded-md border-0 py-1.5 text-slate-900 dark:text-white dark:bg-slate-700 ring-1 ring-inset ring-slate-300 dark:ring-slate-600 sm:text-sm">
                                                @for($y = date('Y') + 1; $y >= date('Y') - 1; $y--)
                                                    <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}
                                                    </option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-900 dark:text-slate-300">Jatuh
                                            Tempo (Due Date)</label>
                                        <input type="date" name="due_date" id="manualDueDate" value="{{ date('Y-m-d') }}"
                                            required
                                            class="mt-1 block w-full rounded-md border-0 py-1.5 text-slate-900 dark:text-white dark:bg-slate-700 ring-1 ring-inset ring-slate-300 dark:ring-slate-600 focus:ring-2 focus:ring-primary-600 sm:text-sm sm:leading-6">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-900 dark:text-slate-300">Nominal
                                            Tagihan
                                            (Opsional)</label>
                                        <input type="number" name="price"
                                            class="mt-1 block w-full rounded-md border-0 py-1.5 text-slate-900 dark:text-white dark:bg-slate-700 ring-1 ring-inset ring-slate-300 dark:ring-slate-600 placeholder:text-slate-400 focus:ring-2 focus:ring-primary-600 sm:text-sm sm:leading-6"
                                            placeholder="Kosongkan untuk harga default user">
                                    </div>
                                </div>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-700/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <button type="submit"
                                    class="inline-flex w-full justify-center rounded-md bg-primary-600 px-3 py-2 text-sm font-bold text-white shadow-sm hover:bg-primary-500 sm:ml-3 sm:w-auto">Simpan</button>
                                <button type="button" @click="showCreateModal = false"
                                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-slate-700 px-3 py-2 text-sm font-semibold text-slate-900 dark:text-slate-200 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 hover:bg-slate-50 dark:hover:bg-slate-600 sm:mt-0 sm:w-auto">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL GENERATE MASSAL (Alpine) -->
        <div x-show="showGenerateModal" class="relative z-500" style="display:none;">
            <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-800 text-left shadow-xl transition-all sm:w-full sm:max-w-lg"
                        @click.away="showGenerateModal = false">
                        <div class="bg-white dark:bg-slate-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div
                                class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/50 mb-4">
                                <i class="fas fa-magic text-primary-600 dark:text-primary-400 text-xl"></i>
                            </div>
                            <h3 class="text-xl font-bold leading-6 text-center text-slate-900 dark:text-white mb-2">
                                Generate Tagihan Massal</h3>
                            <p id="genDesc" class="text-sm text-center text-slate-500 dark:text-slate-400 mb-6">Sistem akan
                                membuat
                                tagihan otomatis
                                untuk pelanggan aktif @if(auth()->user()->role == 'superadmin') **Admin yang terpilih**
                                @else **Anda** @endif.
                            </p>

                            <!-- Initial Form -->
                            <div id="genInitial" class="space-y-4">
                                @if(auth()->user()->role == 'superadmin')
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-slate-900 dark:text-slate-300">Pilih
                                            Admin</label>
                                        <select id="genAdminId"
                                            class="mt-1 block w-full rounded-md border-0 py-1.5 text-slate-900 dark:text-white dark:bg-slate-700 ring-1 ring-inset ring-slate-300 dark:ring-slate-600 sm:text-sm">
                                            <option value="">-- Pilih Admin --</option>
                                            @foreach($admins as $admin)
                                                <option value="{{ $admin->id }}">
                                                    {{ $admin->name }}{{ $admin->id == auth()->id() ? ' (Self)' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-slate-900 dark:text-slate-300">Bulan</label>
                                        <select id="genMonth"
                                            class="mt-1 block w-full rounded-md border-0 py-1.5 text-slate-900 dark:text-white dark:bg-slate-700 ring-1 ring-inset ring-slate-300 dark:ring-slate-600 sm:text-sm">
                                            @for($i = 1; $i <= 12; $i++)
                                                <option value="{{ $i }}" {{ date('n') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-slate-900 dark:text-slate-300">Tahun</label>
                                        <select id="genYear"
                                            class="mt-1 block w-full rounded-md border-0 py-1.5 text-slate-900 dark:text-white dark:bg-slate-700 ring-1 ring-inset ring-slate-300 dark:ring-slate-600 sm:text-sm">
                                            @for($y = date('Y') + 1; $y >= date('Y') - 1; $y--)
                                                <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-slate-900 dark:text-slate-300">Jatuh Tempo
                                        (Tanggal Tagihan)</label>
                                    <input type="date" id="genDueDate" value="{{ date('Y-m-d') }}" required
                                        class="mt-1 block w-full rounded-md border-0 py-1.5 text-slate-900 dark:text-white dark:bg-slate-700 ring-1 ring-inset ring-slate-300 dark:ring-slate-600 focus:ring-2 focus:ring-primary-600 sm:text-sm sm:leading-6">
                                </div>
                                <button type="button" onclick="startGenerate()"
                                    class="mt-6 inline-flex w-full justify-center rounded-md bg-primary-600 px-3 py-2 text-sm font-bold text-white shadow-sm hover:bg-primary-500 w-full">Mulai
                                    Generate</button>
                            </div>

                            <!-- Progress UI -->
                            <div id="genProgress" style="display:none;" class="mt-6 space-y-4">
                                <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2.5">
                                    <div id="genProgressBar"
                                        class="bg-primary-600 h-2.5 rounded-full transition-all duration-300"
                                        style="width: 0%"></div>
                                </div>
                                <div class="text-xs text-center text-slate-500 dark:text-slate-400 font-mono"
                                    id="genStatusText">Menghubungkan...</div>
                                <ul id="genLog"
                                    class="h-48 overflow-y-auto text-left text-xs bg-slate-50 dark:bg-slate-900 p-3 rounded-lg border border-slate-200 dark:border-slate-700 space-y-1 text-slate-600 dark:text-slate-400">
                                </ul>
                            </div>

                            <!-- Done UI -->
                            <div id="genDone" style="display:none;" class="mt-6">
                                <div class="text-center py-4">
                                    <div
                                        class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-green-100 dark:bg-green-900/50 mb-4">
                                        <i class="fas fa-check text-green-600 dark:text-green-400 text-xl"></i>
                                    </div>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">Proses Selesai!</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1" id="genSummaryText"></p>
                                </div>
                                <button onclick="location.reload()"
                                    class="mt-4 inline-flex w-full justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 w-full">
                                    Selesai & Refresh
                                </button>
                            </div>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-700/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="button" @click="showGenerateModal = false" id="btnCancelGen"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-slate-700 px-3 py-2 text-sm font-semibold text-slate-900 dark:text-slate-200 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 hover:bg-slate-50 dark:hover:bg-slate-600 sm:mt-0 sm:w-auto">Batal</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- MODAL MASS PAYMENT (Alpine) -->
        <div x-show="showPayModal" class="relative z-500" style="display:none;">
            <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-800 text-left shadow-xl transition-all sm:w-full sm:max-w-lg"
                        @click.away="showPayModal = false">
                        <div class="bg-white dark:bg-slate-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div
                                class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/50 mb-4">
                                <i class="fas fa-check-double text-green-600 dark:text-green-400 text-xl"></i>
                            </div>
                            <h3 class="text-xl font-bold leading-6 text-center text-slate-900 dark:text-white mb-2">
                                Pembayaran Massal</h3>
                            <p id="payDesc" class="text-sm text-center text-slate-500 dark:text-slate-400 mb-6">
                                Anda akan memproses pembayaran untuk <span class="font-bold flex-inline"
                                    x-text="selectedInvoices.length"></span> tagihan terpilih.
                            </p>

                            <!-- Pay Initial UI -->
                            <div id="payInitial">
                                <button type="button" @click="startMassPayment(selectedInvoices)"
                                    class="mt-4 inline-flex w-full justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-bold text-white shadow-sm hover:bg-green-500 w-full">
                                    Mulai Proses Pembayaran
                                </button>
                            </div>

                            <!-- Pay Progress UI -->
                            <div id="payProgress" style="display:none;" class="mt-6 space-y-4">
                                <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2.5">
                                    <div id="payProgressBar"
                                        class="bg-green-600 h-2.5 rounded-full transition-all duration-300"
                                        style="width: 0%"></div>
                                </div>
                                <div class="text-xs text-center text-slate-500 dark:text-slate-400 font-mono"
                                    id="payStatusText">Menyiapkan...</div>
                                <ul id="payLog"
                                    class="h-48 overflow-y-auto text-left text-xs bg-slate-50 dark:bg-slate-900 p-3 rounded-lg border border-slate-200 dark:border-slate-700 space-y-1 text-slate-600 dark:text-slate-400">
                                </ul>
                            </div>

                            <!-- Pay Done UI -->
                            <div id="payDone" style="display:none;" class="mt-6">
                                <div class="text-center py-4">
                                    <div
                                        class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-green-100 dark:bg-green-900/50 mb-4">
                                        <i class="fas fa-check text-green-600 dark:text-green-400 text-xl"></i>
                                    </div>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">Proses Selesai!</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1" id="paySummaryText"></p>
                                </div>
                                <button onclick="location.reload()"
                                    class="mt-4 inline-flex w-full justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 w-full">
                                    Selesai & Refresh
                                </button>
                            </div>

                        </div>
                        <div class="bg-slate-50 dark:bg-slate-700/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="button" @click="showPayModal = false" id="btnCancelPay"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-slate-700 px-3 py-2 text-sm font-semibold text-slate-900 dark:text-slate-200 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 hover:bg-slate-50 dark:hover:bg-slate-600 sm:mt-0 sm:w-auto">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL MASS DELETE (Alpine) -->
        <div x-show="showDeleteModal" class="relative z-500" style="display:none;">
            <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-800 text-left shadow-xl transition-all sm:w-full sm:max-w-lg"
                        @click.away="showDeleteModal = false">
                        <div class="bg-white dark:bg-slate-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div
                                class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/50 mb-4">
                                <i class="fas fa-trash-alt text-red-600 dark:text-red-400 text-xl"></i>
                            </div>
                            <h3 class="text-xl font-bold leading-6 text-center text-slate-900 dark:text-white mb-2">Hapus
                                Tagihan Massal</h3>
                            <p class="text-sm text-center text-slate-500 dark:text-slate-400 mb-6">Pilih metode penghapusan
                                tagihan yang Anda inginkan. Tindakan ini tidak dapat dibatalkan.</p>

                            <div class="space-y-4">
                                <!-- Option 1: Delete Selected -->
                                <form action="{{ route('billing.bulkDestroy') }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="type" value="selected">
                                    @foreach($customers as $c)
                                        <!-- We need to pass IDs, so we use JS to append selected IDs to this form or simpler: just loop IDs in hidden inputs -->
                                    @endforeach
                                    <!-- A more efficient way: use x-data to bind selectedInvoices to a hidden input array -->
                                    <template x-for="id in selectedInvoices">
                                        <input type="hidden" name="ids[]" :value="id">
                                    </template>

                                    <button type="submit" :disabled="selectedInvoices.length === 0"
                                        class="w-full rounded-lg border border-red-200 dark:border-red-900/50 bg-red-50 dark:bg-red-900/20 px-4 py-4 text-left shadow-sm hover:bg-red-100 dark:hover:bg-red-900/30 transition-all group disabled:opacity-50 disabled:cursor-not-allowed">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="font-semibold text-red-700 dark:text-red-400">Hapus <span
                                                        x-text="selectedInvoices.length"></span> Tagihan Terpilih</p>
                                                <p class="text-xs text-red-600/70 dark:text-red-400/70 mt-1">Hanya menghapus
                                                    item yang Anda centang.</p>
                                            </div>
                                            <i
                                                class="fas fa-check-circle text-red-300 group-hover:text-red-500 transition-colors text-xl"></i>
                                        </div>
                                    </button>
                                </form>

                                <!-- Option 2: Delete All in Month -->
                                <form action="{{ route('billing.bulkDestroy') }}" method="POST"
                                    onsubmit="return confirm('PERINGATAN: Semua tagihan pada bulan ini akan dihapus permanen! Lanjutkan?');">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="type" value="all">
                                    <input type="hidden" name="month" value="{{ $month }}">
                                    <input type="hidden" name="year" value="{{ $year }}">

                                    <button type="submit"
                                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-700 px-4 py-4 text-left shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 transition-all group">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="font-semibold text-slate-800 dark:text-white">Hapus Semua Tagihan
                                                    Bulan Ini</p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                                    Bulan: {{ DateTime::createFromFormat('!m', $month)->format('F') }}
                                                    {{ $year }}
                                                </p>
                                            </div>
                                            <i
                                                class="fas fa-calendar-times text-slate-300 group-hover:text-slate-500 transition-colors text-xl"></i>
                                        </div>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-700/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="button" @click="showDeleteModal = false"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-slate-700 px-3 py-2 text-sm font-semibold text-slate-900 dark:text-slate-200 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 hover:bg-slate-50 dark:hover:bg-slate-600 sm:mt-0 sm:w-auto">Batal</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL MASS UPDATE DUE DATE (Alpine) -->
        <div x-show="showDueDateModal" class="relative z-500" style="display:none;">
            <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-800 text-left shadow-xl transition-all sm:w-full sm:max-w-lg"
                        @click.away="showDueDateModal = false">
                        <form action="{{ route('billing.bulkUpdateDueDate') }}" method="POST">
                            @csrf
                            <div class="bg-white dark:bg-slate-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                <div
                                    class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/50 mb-4">
                                    <i class="fas fa-calendar-edit text-blue-600 dark:text-blue-400 text-xl"></i>
                                </div>
                                <h3 class="text-xl font-bold leading-6 text-center text-slate-900 dark:text-white mb-2">
                                    Ubah Jatuh Tempo Massal</h3>
                                <p class="text-sm text-center text-slate-500 dark:text-slate-400 mb-6">
                                    Anda akan mengubah tanggal FALL DUE/TANGGAL BAYAR pada <span class="font-bold"
                                        x-text="selectedInvoices.length"></span> tagihan terpilih.
                                </p>

                                <div class="space-y-4">
                                    <template x-for="id in selectedInvoices">
                                        <input type="hidden" name="ids[]" :value="id">
                                    </template>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-900 dark:text-slate-300">Pilih
                                            Tanggal Baru</label>
                                        <input type="date" name="due_date" required
                                            class="mt-1 block w-full rounded-md border-0 py-1.5 text-slate-900 dark:text-white dark:bg-slate-700 ring-1 ring-inset ring-slate-300 dark:ring-slate-600 focus:ring-2 focus:ring-primary-600 sm:text-sm sm:leading-6">
                                    </div>
                                </div>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-700/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <button type="submit"
                                    class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto">Update</button>
                                <button type="button" @click="showDueDateModal = false"
                                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-slate-700 px-3 py-2 text-sm font-semibold text-slate-900 dark:text-slate-200 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 hover:bg-slate-50 dark:hover:bg-slate-600 sm:mt-0 sm:w-auto">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.tailwindcss.css">
    <!-- Select2 styling if used previously, we can replace with basic select for simplicity or keep Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Select2 Tailwind Fix */
        .select2-container .select2-selection--single {
            height: 38px;
            border-color: #d1d5db;
            border-radius: 0.375rem;
            padding-top: 5px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            top: 6px;
        }

        /* Dark Mode Fix for Select2 */
        .dark .select2-container--default .select2-selection--single {
            background-color: #334155;
            /* slate-700 */
            border-color: #475569;
            /* slate-600 */
            color: #f8fafc;
            /* slate-50 */
        }

        .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #f8fafc;
        }

        .dark .select2-dropdown {
            background-color: #1e293b;
            /* slate-800 */
            border-color: #475569;
            /* slate-600 */
            color: #f8fafc;
        }

        .dark .select2-container--default .select2-results__option {
            color: #cbd5e1;
            /* slate-300 */
        }

        .dark .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #4f46e5;
            /* primary-600 */
            color: white;
        }

        .dark .select2-container--default .select2-search--dropdown .select2-search__field {
            background-color: #334155;
            border-color: #475569;
            color: white;
        }

        /* General Visibility Fix */
        .select2-results__option {
            padding: 8px 12px;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.tailwindcss.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#tableBilling').DataTable({ responsive: true });
            // Init Select2 inside modal
            $('.select2-modal').select2({
                width: '100%',
                dropdownParent: $('#showCreateModalContainer') // Target container if needed, but let's try standard first
            });

            // Sync Manual Due Date when Month/Year changes
            $('#manualMonth, #manualYear').on('change', function () {
                const month = $('#manualMonth').val();
                const year = $('#manualYear').val();
                // Get current day from manualDueDate or default to today's day
                const currentVal = $('#manualDueDate').val();
                let day = new Date().getDate();
                if (currentVal) {
                    day = new Date(currentVal).getDate();
                }

                // Format YYYY-MM-DD
                const formattedMonth = month.toString().padStart(2, '0');
                const formattedDay = day.toString().padStart(2, '0');
                $('#manualDueDate').val(`${year}-${formattedMonth}-${formattedDay}`);
            });
        });

        async function startGenerate() {
            const adminId = $('#genAdminId').val();
            const month = $('#genMonth').val();
            const year = $('#genYear').val();
            const dueDate = $('#genDueDate').val();
            const log = $('#genLog');

            @if(auth()->user()->role == 'superadmin')
                if (!adminId) {
                    alert('Pilih Admin terlebih dahulu!');
                    return;
                }
            @endif

                            if (!dueDate) {
                alert('Pilih tanggal jatuh tempo!');
                return;
            }

            // UI Switch
            $('#genInitial').hide();
            $('#genDesc').hide();
            $('#genProgress').show();
            $('#btnCancelGen').hide();
            log.empty().append('<li><span class="text-blue-500">[INFO]</span> Mengambil daftar pelanggan...</li>');

            try {
                // 1. Get List
                const adminIdParam = adminId ? `&admin_id=${adminId}` : '';
                const listResp = await fetch(`{{ route('billing.list') }}?month=${month}&year=${year}${adminIdParam}`);
                const listData = await listResp.json();
                const customers = listData.customers;
                const total = customers.length;

                if (total === 0) {
                    log.append('<li><span class="text-yellow-500">[WARN]</span> Tidak ada pelanggan aktif ditemukan.</li>');
                    $('#genStatusText').text('Tidak ada data.');
                    $('#btnCancelGen').show();
                    return;
                }

                log.append(`<li><span class="text-blue-500">[INFO]</span> Ditemukan ${total} pelanggan. Memulai proses...</li>`);

                let created = 0;
                let skipped = 0;
                let error = 0;

                // 2. Process One by One
                for (let i = 0; i < total; i++) {
                    const customer = customers[i];
                    const progress = Math.round(((i + 1) / total) * 100);

                    $('#genProgressBar').css('width', progress + '%');
                    $('#genStatusText').text(`Memproses ${i + 1}/${total} (${progress}%)`);

                    try {
                        const res = await fetch(`{{ route('billing.process') }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                customer_id: customer.id,
                                admin_id: adminId, // Send admin_id for superadmin check
                                month,
                                year,
                                due_date: dueDate
                            })
                        });

                        const data = await res.json();
                        if (data.status === 'created') {
                            created++;
                            log.append(`<li><span class="text-green-500">[OK]</span> ${customer.name}: Tagihan dibuat.</li>`);
                        } else if (data.status === 'skipped') {
                            skipped++;
                            log.append(`<li><span class="text-slate-400">[SKIP]</span> ${customer.name}: Sudah ada tagihan.</li>`);
                        } else {
                            error++;
                            log.append(`<li><span class="text-red-500">[ERR]</span> ${customer.name}: Gagal memproses.</li>`);
                        }
                    } catch (e) {
                        error++;
                        log.append(`<li><span class="text-red-500">[ERR]</span> ${customer.name}: Error koneksi.</li>`);
                    }

                    // Auto scroll log
                    log.scrollTop(log[0].scrollHeight);
                }

                // 3. Finalize
                $('#genProgress').hide();
                $('#genDone').show();
                $('#genSummaryText').text(`Selesai: ${created} dibuat, ${skipped} dilewati, ${error} gagal.`);

            } catch (err) {
                log.append(`<li><span class="text-red-500">[FATAL]</span> Sistem error: ${err.message}</li>`);
                $('#genStatusText').text('Gagal!');
                $('#btnCancelGen').show();
            }
        }

        async function startMassPayment(invoiceIds) {
            const log = $('#payLog');

            // UI Switch
            $('#payInitial').hide();
            $('#payDesc').hide();
            $('#payProgress').show();
            $('#btnCancelPay').hide();
            log.empty().append('<li><span class="text-blue-500">[INFO]</span> Memulai pembayaran massal...</li>');

            const total = invoiceIds.length;
            let success = 0;
            let skipped = 0;
            let error = 0;

            for (let i = 0; i < total; i++) {
                const invId = invoiceIds[i];
                const progress = Math.round(((i + 1) / total) * 100);

                $('#payProgressBar').css('width', progress + '%');
                $('#payStatusText').text(`Memproses ${i + 1}/${total} (${progress}%)`);

                try {
                    const res = await fetch(`/billing/${invId}/pay-ajax`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const data = await res.json();

                    if (data.status === 'success') {
                        success++;
                        log.append(`<li><span class="text-green-500">[OK]</span> ${data.customer}: Lunas.</li>`);
                    } else if (data.status === 'skipped') {
                        skipped++;
                        log.append(`<li><span class="text-yellow-500">[SKIP]</span> ${data.customer}: Sudah lunas.</li>`);
                    } else {
                        error++;
                        log.append(`<li><span class="text-red-500">[ERR]</span> ${data.customer || 'Unknown'}: ${data.message}</li>`);
                    }

                } catch (e) {
                    error++;
                    log.append(`<li><span class="text-red-500">[ERR]</span> ID ${invId}: Koneksi Gagal.</li>`);
                }

                // Auto scroll log
                log.scrollTop(log[0].scrollHeight);
            }

            // Finalize
            $('#payProgress').hide();
            $('#payDone').show();
            $('#paySummaryText').text(`Selesai: ${success} Sukses, ${skipped} Dilewati, ${error} Gagal.`);
        }

        // SweetAlert Payment Flow
        function bayarInvoice(invoiceId, customerName, tagihanBulanIni, tunggakan, saldo) {
            const totalDue = tagihanBulanIni + (tunggakan > 0 ? tunggakan : 0);
            const formattedTagihan = 'Rp ' + Number(tagihanBulanIni).toLocaleString('id-ID');
            const formattedTunggakan = 'Rp ' + Number(tunggakan).toLocaleString('id-ID');
            const formattedDue = 'Rp ' + Number(totalDue).toLocaleString('id-ID');
            const formattedSaldo = 'Rp ' + Number(saldo).toLocaleString('id-ID');

            let detailHTML = '<p class="mb-1">Tagihan Bulan Ini: <strong>' + formattedTagihan + '</strong></p>';
            if (tunggakan > 0) {
                detailHTML += '<p class="mb-1 text-amber-600">Tunggakan Bulan Sebelumnya: <strong>' + formattedTunggakan + '</strong></p>';
            }
            detailHTML += '<p class="mb-2">Total Tagihan: <strong class="text-red-600">' + formattedDue + '</strong></p>';

            Swal.fire({
                title: 'Pembayaran Invoice',
                html:
                    '<div class="text-left text-sm">' +
                    '<p class="mb-2">Pelanggan: <strong>' + customerName + '</strong></p>' +
                    detailHTML +
                    '<p class="mb-4">Saldo Tersedia: <strong class="text-green-600">' + formattedSaldo + '</strong></p>' +
                    '<hr class="my-3">' +
                    '<p class="font-semibold mb-2">Pilih metode pembayaran:</p>' +
                    '</div>',
                showCancelButton: true,
                showDenyButton: saldo > 0,
                confirmButtonText: '<i class="fas fa-money-bill-wave mr-1"></i> Bayar Manual',
                denyButtonText: '<i class="fas fa-wallet mr-1"></i> Pakai Saldo',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#4f46e5',
                denyButtonColor: '#10b981',
            }).then((result) => {
                if (result.isConfirmed) {
                    // Fetch unpaid months first, then show manual payment dialog
                    showManualPaymentDialog(invoiceId, tagihanBulanIni, detailHTML);
                } else if (result.isDenied) {
                    // Saldo Payment
                    let payFromSaldo = Math.min(saldo, totalDue);
                    Swal.fire({
                        title: 'Konfirmasi Bayar dari Saldo',
                        html: '<p class="text-sm">Akan memotong <strong class="text-green-600">Rp ' + Number(payFromSaldo).toLocaleString('id-ID') + '</strong> dari saldo pelanggan.</p>' +
                              (payFromSaldo < totalDue ? '<p class="text-sm text-amber-600 mt-2">Saldo tidak cukup. Kurang bayar: <strong>Rp ' + Number(totalDue - payFromSaldo).toLocaleString('id-ID') + '</strong></p>' : ''),
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Potong Saldo',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#10b981',
                    }).then((r) => {
                        if (r.isConfirmed) {
                            submitPayment(invoiceId, 'saldo', 0);
                        }
                    });
                }
            });
        }

        function showManualPaymentDialog(invoiceId, tagihanBulanIni, detailHTML) {
            // Show loading while fetching unpaid months
            Swal.fire({ title: 'Memuat data...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            $.ajax({
                url: '/billing/' + invoiceId + '/unpaid-months',
                type: 'GET',
                success: function(res) {
                    let unpaidMonths = res.success ? res.data : [];
                    
                    let unpaidHTML = '';
                    if (unpaidMonths.length > 0) {
                        unpaidHTML += '<div class="mt-3 mb-3 p-3 bg-amber-50 rounded-lg border border-amber-200 text-left">';
                        unpaidHTML += '<p class="text-sm font-semibold text-amber-700 mb-2"><i class="fas fa-exclamation-triangle mr-1"></i> Kurang Bayar Bulan Sebelumnya</p>';
                        
                        unpaidMonths.forEach((m, idx) => {
                            unpaidHTML += '<div class="flex items-start gap-2 mb-2 p-2 bg-white rounded border border-amber-100">';
                            unpaidHTML += '<input type="checkbox" id="unpaidCb_' + idx + '" class="unpaid-checkbox mt-1" data-idx="' + idx + '" data-invoice-id="' + m.id + '" data-max="' + m.kurang_bayar + '">';
                            unpaidHTML += '<div class="flex-1">';
                            unpaidHTML += '<label for="unpaidCb_' + idx + '" class="text-sm font-medium cursor-pointer">' + m.periode + '</label>';
                            unpaidHTML += '<p class="text-xs text-gray-500">Kurang bayar: <strong class="text-amber-600">Rp ' + Number(m.kurang_bayar).toLocaleString('id-ID') + '</strong></p>';
                            unpaidHTML += '<div id="unpaidInput_' + idx + '" class="mt-1" style="display:none;">';
                            unpaidHTML += '<input type="number" id="unpaidAmt_' + idx + '" class="unpaid-amount w-full px-2 py-1 text-sm border rounded focus:ring-1 focus:ring-amber-400" value="' + m.kurang_bayar + '" min="1" max="' + m.kurang_bayar + '" placeholder="Jumlah bayar">';
                            unpaidHTML += '</div>';
                            unpaidHTML += '</div>';
                            unpaidHTML += '</div>';
                        });

                        unpaidHTML += '</div>';
                    }

                    Swal.fire({
                        title: 'Input Jumlah Bayar',
                        html:
                            '<div class="text-left text-sm mb-3">' +
                            detailHTML +
                            '</div>' +
                            '<div class="text-left">' +
                            '<label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Bayar Bulan Ini (Rp)</label>' +
                            '<input id="swalPayAmount" type="number" class="swal2-input" value="' + tagihanBulanIni + '" min="1" style="margin:0;width:100%;">' +
                            '</div>' +
                            unpaidHTML +
                            '<div id="payTotalSummary" class="mt-3 p-2 bg-indigo-50 rounded text-sm text-left border border-indigo-200">' +
                            '<strong>Total Pembayaran: </strong><span id="payTotalDisplay">Rp ' + Number(tagihanBulanIni).toLocaleString('id-ID') + '</span>' +
                            '</div>' +
                            '<p class="text-xs text-gray-400 mt-2">Jika lebih dari tagihan, sisa masuk ke saldo. Jika kurang, tercatat sebagai kurang bayar.</p>',
                        width: '520px',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-check mr-1"></i> Bayar',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#10b981',
                        didOpen: () => {
                            // Toggle checkbox → show/hide input & recalculate total
                            document.querySelectorAll('.unpaid-checkbox').forEach(cb => {
                                cb.addEventListener('change', function() {
                                    const idx = this.dataset.idx;
                                    const inputDiv = document.getElementById('unpaidInput_' + idx);
                                    inputDiv.style.display = this.checked ? 'block' : 'none';
                                    recalcPayTotal(tagihanBulanIni);
                                });
                            });
                            // Recalculate when any amount changes
                            document.querySelectorAll('.unpaid-amount').forEach(inp => {
                                inp.addEventListener('input', () => recalcPayTotal(tagihanBulanIni));
                            });
                            document.getElementById('swalPayAmount').addEventListener('input', () => recalcPayTotal(tagihanBulanIni));
                        },
                        preConfirm: () => {
                            const mainAmt = document.getElementById('swalPayAmount').value;
                            if (!mainAmt || mainAmt <= 0) {
                                Swal.showValidationMessage('Masukkan jumlah yang valid!');
                                return false;
                            }
                            
                            // Collect additional payments
                            let additionalPayments = [];
                            document.querySelectorAll('.unpaid-checkbox:checked').forEach(cb => {
                                const idx = cb.dataset.idx;
                                const amt = document.getElementById('unpaidAmt_' + idx).value;
                                if (amt > 0) {
                                    additionalPayments.push({
                                        invoice_id: parseInt(cb.dataset.invoiceId),
                                        amount: parseInt(amt)
                                    });
                                }
                            });

                            return { amount: mainAmt, additionalPayments: additionalPayments };
                        }
                    }).then((r) => {
                        if (r.isConfirmed) {
                            submitPayment(invoiceId, 'manual', r.value.amount, r.value.additionalPayments);
                        }
                    });
                },
                error: function() {
                    // Fallback: show without unpaid months
                    Swal.fire({
                        title: 'Input Jumlah Bayar',
                        html:
                            '<div class="text-left text-sm mb-3">' + detailHTML + '</div>' +
                            '<div class="text-left">' +
                            '<label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Bayar (Rp)</label>' +
                            '<input id="swalPayAmount" type="number" class="swal2-input" value="' + tagihanBulanIni + '" min="1" style="margin:0;width:100%;">' +
                            '</div>',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-check mr-1"></i> Bayar',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#10b981',
                        preConfirm: () => {
                            const amt = document.getElementById('swalPayAmount').value;
                            if (!amt || amt <= 0) { Swal.showValidationMessage('Masukkan jumlah yang valid!'); return false; }
                            return { amount: amt, additionalPayments: [] };
                        }
                    }).then((r) => {
                        if (r.isConfirmed) {
                            submitPayment(invoiceId, 'manual', r.value.amount, r.value.additionalPayments);
                        }
                    });
                }
            });
        }

        function recalcPayTotal(tagihanBulanIni) {
            let mainAmt = parseInt(document.getElementById('swalPayAmount').value) || 0;
            let additionalTotal = 0;
            document.querySelectorAll('.unpaid-checkbox:checked').forEach(cb => {
                const idx = cb.dataset.idx;
                additionalTotal += parseInt(document.getElementById('unpaidAmt_' + idx).value) || 0;
            });
            let total = mainAmt + additionalTotal;
            document.getElementById('payTotalDisplay').textContent = 'Rp ' + Number(total).toLocaleString('id-ID');
        }

        function submitPayment(invoiceId, method, amount, additionalPayments) {
            $.ajax({
                url: '/billing/' + invoiceId + '/pay-manual',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    method: method,
                    amount: amount,
                    additional_payments: additionalPayments || []
                }),
                success: function(res) {
                    if (res.success) {
                        Swal.fire({
                            icon: res.status === 'paid' ? 'success' : 'info',
                            title: res.status === 'paid' ? 'Lunas!' : 'Pembayaran Sebagian',
                            text: res.message,
                            timer: 2500,
                            showConfirmButton: true
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON?.message || 'Terjadi kesalahan.';
                    Swal.fire('Error', msg, 'error');
                }
            });
        }

        // Fetch and show payment history
        function showHistory(customerId, customerName) {
            Swal.fire({
                title: 'Riwayat Pembayaran',
                text: 'Memuat data...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '/billing/customer/' + customerId + '/history',
                type: 'GET',
                success: function(res) {
                    if (res.success) {
                        let html = '<div class="text-left">';
                        html += '<p class="mb-4 text-sm">Pelanggan: <strong>' + customerName + '</strong></p>';
                        
                        html += '<div class="overflow-x-auto max-h-96">';
                        html += '<table class="w-full text-sm text-left border-collapse">';
                        html += '<thead class="bg-gray-100 sticky top-0">';
                        html += '<tr><th class="p-2 border">Periode</th><th class="p-2 border">Tagihan</th><th class="p-2 border">Dibayar</th><th class="p-2 border">Kurang</th><th class="p-2 border">Status</th><th class="p-2 border text-center">Aksi</th></tr>';
                        html += '</thead><tbody>';

                        res.data.forEach(item => {
                            let statusBadge = item.status === 'paid' 
                                ? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Lunas</span>'
                                : '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Unpaid</span>';
                            
                            let actionBtn = '';
                            if (item.status === 'paid' || item.dibayar > 0) {
                                actionBtn = `<button type="button" onclick="cancelPaymentAjax(${item.id})" class="p-1 px-2 text-xs bg-orange-100 text-orange-600 hover:bg-orange-200 rounded transition-colors" title="Batalkan Bayar">
                                        <i class="fas fa-undo mr-1"></i> Batal
                                    </button>`;
                            }
                                
                            html += '<tr class="border-b hover:bg-gray-50">';
                            html += '<td class="p-2 border">' + item.periode + '</td>';
                            html += '<td class="p-2 border">Rp ' + Number(item.tagihan).toLocaleString('id-ID') + '</td>';
                            html += '<td class="p-2 border">Rp ' + Number(item.dibayar).toLocaleString('id-ID') + '</td>';
                            html += '<td class="p-2 border text-amber-600">Rp ' + Number(item.kurang).toLocaleString('id-ID') + '</td>';
                            html += '<td class="p-2 border text-center">' + statusBadge + '</td>';
                            html += '<td class="p-2 border text-center">' + actionBtn + '</td>';
                            html += '</tr>';
                        });

                        html += '</tbody></table></div>';
                        
                        if (res.total_tunggakan > 0) {
                            html += '<div class="mt-4 p-3 bg-red-50 text-red-800 rounded-md font-bold text-sm border border-red-200">';
                            html += 'Total Tunggakan: Rp ' + Number(res.total_tunggakan).toLocaleString('id-ID');
                            html += '</div>';
                        }
                        
                        html += '</div>';

                        Swal.fire({
                            title: 'Riwayat Pembayaran',
                            html: html,
                            width: '800px',
                            showCloseButton: true,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire('Gagal', 'Tidak dapat memuat data.', 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Gagal memuat data dari server.', 'error');
                }
            });
        }

        function cancelPaymentAjax(invoiceId) {
            Swal.fire({
                title: 'Batalkan Pembayaran?',
                text: "Nilai tagihan akan dikembalikan dan diakumulasikan kembali pada tagihan berikutnya.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f97316',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Batalkan!',
                cancelButtonText: 'Tutup'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                    
                    $.ajax({
                        url: '/billing/' + invoiceId + '/cancel',
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            if (res.success) {
                                Swal.fire('Berhasil!', res.message, 'success').then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Gagal!', res.message || 'Terjadi kesalahan.', 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error', 'Gagal membatalkan pembayaran.', 'error');
                        }
                    });
                }
            });
        }
    </script>
@endpush