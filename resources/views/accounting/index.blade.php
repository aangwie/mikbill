@extends('layouts.app2')

@section('title', 'Manajemen Keuangan')
@section('header', 'Akuntansi & Keuangan')
@section('subheader', 'Kelola pendapatan, pengeluaran, dan laporan laba rugi.')

@section('content')

    <div x-data="{ showPrintModal: false }">

        <!-- Filter Bar -->
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <div class="bg-indigo-50 text-indigo-600 p-2 rounded-lg">
                    <i class="fas fa-wallet"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Periode Keuangan</h3>
            </div>
            <form action="{{ route('accounting.index') }}" method="GET"
                class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <select name="month"
                    class="block w-full sm:w-40 rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                            {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                        </option>
                    @endfor
                </select>
                <select name="year"
                    class="block w-full sm:w-32 rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
                    @for ($y = date('Y'); $y >= 2023; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <button type="submit"
                    class="inline-flex justify-center items-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 transition-all">
                    <i class="fas fa-filter mr-2"></i> Lihat
                </button>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-8">
            <!-- Revenue -->
            <div class="relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5">
                <dt class="truncate text-sm font-medium text-slate-500 uppercase tracking-wider">Total Pendapatan (Omset)
                </dt>
                <dd class="mt-2 text-3xl font-bold tracking-tight text-emerald-600">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </dd>
                <div class="mt-2 flex items-center text-sm text-slate-500">
                    <i class="fas fa-arrow-up text-emerald-500 mr-2"></i> Dari Tagihan Lunas
                </div>
                <div class="absolute top-0 right-0 p-4 opacity-10">
                    <i class="fas fa-wallet text-emerald-600 fa-4x transform rotate-12"></i>
                </div>
            </div>

            <!-- Expense -->
            <div class="relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5">
                <dt class="truncate text-sm font-medium text-slate-500 uppercase tracking-wider">Total Pengeluaran</dt>
                <dd class="mt-2 text-3xl font-bold tracking-tight text-rose-600">
                    Rp {{ number_format($totalExpense, 0, ',', '.') }}
                </dd>
                <div class="mt-2 flex items-center text-sm text-slate-500">
                    <i class="fas fa-arrow-down text-rose-500 mr-2"></i> Operasional & Lainnya
                </div>
                <div class="absolute top-0 right-0 p-4 opacity-10">
                    <i class="fas fa-money-bill-wave text-rose-600 fa-4x transform -rotate-12"></i>
                </div>
            </div>

            <!-- Profit -->
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br {{ $netProfit >= 0 ? 'from-blue-600 to-indigo-700' : 'from-amber-500 to-orange-600' }} p-6 shadow-md text-white">
                <dt class="truncate text-sm font-medium text-white/80 uppercase tracking-wider">Laba Bersih (Profit)</dt>
                <dd class="mt-2 text-3xl font-bold tracking-tight">
                    Rp {{ number_format($netProfit, 0, ',', '.') }}
                </dd>
                <div class="mt-2 flex items-center text-sm text-white/90">
                    @if($netProfit >= 0)
                        <i class="fas fa-smile mr-2"></i> Untung
                    @else
                        <i class="fas fa-frown mr-2"></i> Rugi
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Left: Expense List -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-900/5 overflow-hidden">
                    <div
                        class="border-b border-slate-200 px-4 py-5 sm:px-6 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <h3 class="text-base font-semibold leading-6 text-slate-900">Rincian Pengeluaran Bulan Ini</h3>
                        <button @click="showPrintModal = true"
                            class="inline-flex items-center rounded-md bg-slate-800 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-slate-700">
                            <i class="fas fa-print mr-2"></i> Cetak Laporan
                        </button>
                    </div>
                    <div class="overflow-x-auto p-4">
                        <table id="tableExpenses" class="w-full text-left border-collapse">
                            <thead>
                                <tr>
                                    <th
                                        class="text-xs font-semibold text-slate-500 uppercase tracking-wider py-3 px-4 bg-slate-50 rounded-l-lg">
                                        Tanggal</th>
                                    <th
                                        class="text-xs font-semibold text-slate-500 uppercase tracking-wider py-3 px-4 bg-slate-50">
                                        Keterangan</th>
                                    <th
                                        class="text-xs font-semibold text-slate-500 uppercase tracking-wider py-3 px-4 bg-slate-50 text-right">
                                        Jumlah</th>
                                    <th
                                        class="text-xs font-semibold text-slate-500 uppercase tracking-wider py-3 px-4 bg-slate-50 rounded-r-lg text-right">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($expenses as $exp)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-4 py-3 align-middle text-sm text-slate-600 whitespace-nowrap">
                                            {{ $exp->transaction_date->format('d/m/Y') }}
                                        </td>
                                        <td class="px-4 py-3 align-middle text-sm text-slate-900">{{ $exp->description }}</td>
                                        <td
                                            class="px-4 py-3 align-middle text-sm font-bold text-rose-600 text-right whitespace-nowrap">
                                            Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 align-middle text-right">
                                            <form action="{{ route('accounting.destroy', $exp->id) }}" method="POST"
                                                onsubmit="return confirm('Hapus pengeluaran ini?');">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 p-1.5 rounded-md transition-colors"><i
                                                        class="fas fa-trash-alt"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right: Add Expense Form -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-900/5 overflow-hidden sticky top-24">
                    <div class="bg-rose-600 px-6 py-4 border-b border-rose-500">
                        <h3 class="text-base font-bold text-white flex items-center">
                            <i class="fas fa-plus-circle mr-2"></i> Catat Pengeluaran
                        </h3>
                    </div>
                    <div class="p-6">
                        @if(session('success'))
                            <div class="mb-4 rounded-md bg-green-50 p-4 border border-green-200">
                                <div class="flex">
                                    <div class="flex-shrink-0"><i class="fas fa-check-circle text-green-400"></i></div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <form action="{{ route('accounting.store') }}" method="POST">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-900 mb-1">Tanggal</label>
                                    <input type="date" name="transaction_date"
                                        class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-rose-600 sm:text-sm sm:leading-6"
                                        value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-900 mb-1">Keterangan</label>
                                    <textarea name="description" rows="3"
                                        class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-rose-600 sm:text-sm sm:leading-6"
                                        placeholder="Beli peralatan, bayar listrik..." required></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-900 mb-1">Nominal (Rp)</label>
                                    <div class="relative rounded-md shadow-sm">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                            <span class="text-slate-500 sm:text-sm">Rp</span>
                                        </div>
                                        <input type="number" name="amount"
                                            class="block w-full rounded-md border-0 py-1.5 pl-10 text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-rose-600 sm:text-sm sm:leading-6"
                                            placeholder="0" required>
                                    </div>
                                </div>
                                <div class="pt-2">
                                    <button type="submit"
                                        class="w-full justify-center rounded-lg bg-rose-600 px-3 py-2 text-sm font-bold text-white shadow-sm hover:bg-rose-500 transition-all">
                                        Simpan Pengeluaran
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Print Modal (Alpine) -->
        <div x-show="showPrintModal" class="relative z-500" style="display:none;">
            <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm" x-transition.opacity></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:w-full sm:max-w-lg"
                        @click.away="showPrintModal = false" x-transition.scale>
                        <form action="{{ route('accounting.print') }}" method="GET" target="_blank">
                            <input type="hidden" name="month" value="{{ $month }}">
                            <input type="hidden" name="year" value="{{ $year }}">

                            <div class="bg-slate-800 px-4 py-3 sm:px-6 flex justify-between items-center">
                                <h3 class="text-base font-bold leading-6 text-white"><i class="fas fa-print mr-2"></i> Pilih
                                    Jenis Laporan</h3>
                                <button type="button" @click="showPrintModal = false"
                                    class="text-slate-400 hover:text-white"><i class="fas fa-times"></i></button>
                            </div>

                            <div class="px-4 py-5 sm:p-6">
                                <div class="space-y-4">
                                    <label
                                        class="flex items-start cursor-pointer hover:bg-slate-50 p-3 rounded-lg border border-transparent hover:border-slate-200 transition-all">
                                        <input type="radio" name="report_type" value="1"
                                            class="mt-1 h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-600"
                                            checked>
                                        <div class="ml-3">
                                            <span class="block text-sm font-medium text-slate-900">Rincian Lengkap</span>
                                            <span class="block text-sm text-slate-500">Cetak semua detail transaksi
                                                pemasukan dan pengeluaran.</span>
                                        </div>
                                    </label>
                                    <label
                                        class="flex items-start cursor-pointer hover:bg-slate-50 p-3 rounded-lg border border-transparent hover:border-slate-200 transition-all">
                                        <input type="radio" name="report_type" value="2"
                                            class="mt-1 h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-600">
                                        <div class="ml-3">
                                            <span class="block text-sm font-medium text-slate-900">Rekap Omset & Rincian
                                                Biaya</span>
                                            <span class="block text-sm text-slate-500">Ringkasan total pemasukan, tapi
                                                rincian pengeluaran ditampilkan.</span>
                                        </div>
                                    </label>
                                    <label
                                        class="flex items-start cursor-pointer hover:bg-slate-50 p-3 rounded-lg border border-transparent hover:border-slate-200 transition-all">
                                        <input type="radio" name="report_type" value="3"
                                            class="mt-1 h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-600">
                                        <div class="ml-3">
                                            <span class="block text-sm font-medium text-slate-900">Rincian Omset & Rekap
                                                Biaya</span>
                                            <span class="block text-sm text-slate-500">Detail pemasukan ditampilkan,
                                                pengeluaran hanya totalnya saja.</span>
                                        </div>
                                    </label>
                                    <label
                                        class="flex items-start cursor-pointer hover:bg-slate-50 p-3 rounded-lg border border-transparent hover:border-slate-200 transition-all">
                                        <input type="radio" name="report_type" value="4"
                                            class="mt-1 h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-600">
                                        <div class="ml-3">
                                            <span class="block text-sm font-medium text-slate-900">Rekapitulasi Total</span>
                                            <span class="block text-sm text-slate-500">Hanya menampilkan total akhir
                                                pemasukan, pengeluaran, dan laba bersih.</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <button type="submit" @click="showPrintModal = false"
                                    class="inline-flex w-full justify-center rounded-lg bg-slate-800 px-3 py-2 text-sm font-bold text-white shadow-sm hover:bg-slate-700 sm:ml-3 sm:w-auto">
                                    <i class="fas fa-print mr-2"></i> Cetak PDF
                                </button>
                                <button type="button" @click="showPrintModal = false"
                                    class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto">Batal</button>
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
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.tailwindcss.js"></script>
    <script>
        $(document).ready(function () {
            $('#tableExpenses').DataTable({ responsive: true, order: [[0, "desc"]] });
        });
    </script>
@endpush