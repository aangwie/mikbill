@extends('layouts.app2')

@section('title', 'Laporan Keuangan')
@section('header', 'Laporan Keuangan')
@section('subheader', 'Rekapitulasi pembayaran dan status tagihan pelanggan.')

@section('content')

    <!-- Filter Bar -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <div class="bg-indigo-50 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 p-2 rounded-lg">
                <i class="fas fa-chart-line"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 dark:text-white">Periode Laporan</h3>
        </div>
        <form action="{{ route('report.index') }}" method="GET" class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
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
                <i class="fas fa-filter mr-2"></i> Filter
            </button>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-8">
        <!-- Total Tagihan (Omset) -->
        <div
            class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 p-6 shadow-md text-white">
            <dt class="truncate text-sm font-medium text-blue-100 uppercase tracking-wider">Total Tagihan (Omset)</dt>
            <dd class="mt-2 text-3xl font-bold tracking-tight">
                Rp {{ number_format($totalTagihan, 0, ',', '.') }}
            </dd>
            <p class="mt-1 text-xs text-blue-100 opacity-80">{{ count($invoices) }} Pelanggan</p>
            <div class="absolute right-4 top-4 text-white opacity-20">
                <i class="fas fa-file-invoice-dollar fa-3x transform -rotate-12"></i>
            </div>
        </div>

        <!-- Uang Masuk (Lunas) -->
        <div
            class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 p-6 shadow-md text-white">
            <dt class="truncate text-sm font-medium text-emerald-100 uppercase tracking-wider">Uang Masuk (Lunas)</dt>
            <dd class="mt-2 text-3xl font-bold tracking-tight">
                Rp {{ number_format($totalLunas, 0, ',', '.') }}
            </dd>
            <p class="mt-1 text-xs text-emerald-100 opacity-80">{{ $jumlahLunas }} Pelanggan</p>
            <div class="absolute right-4 top-4 text-white opacity-20">
                <i class="fas fa-check-circle fa-3x"></i>
            </div>
        </div>

        <!-- Piutang (Belum Lunas) -->
        <div
            class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-rose-500 to-red-600 p-6 shadow-md text-white">
            <dt class="truncate text-sm font-medium text-rose-100 uppercase tracking-wider">Piutang (Belum Bayar)</dt>
            <dd class="mt-2 text-3xl font-bold tracking-tight">
                Rp {{ number_format($totalBelumLunas, 0, ',', '.') }}
            </dd>
            <p class="mt-1 text-xs text-rose-100 opacity-80">{{ $jumlahBelumLunas }} Pelanggan</p>
            <div class="absolute right-4 top-4 text-white opacity-20">
                <i class="fas fa-times-circle fa-3x"></i>
            </div>
        </div>
    </div>

    <!-- Transaction Table -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm ring-1 ring-slate-900/5 dark:ring-slate-700/50 overflow-hidden">
        <div class="border-b border-slate-200 dark:border-slate-700 px-4 py-5 sm:px-6 bg-slate-50/50 dark:bg-slate-900/50">
            <h3 class="text-base font-semibold leading-6 text-slate-900 dark:text-white">Detail Transaksi Pembayaran</h3>
        </div>
        <div class="overflow-x-auto p-4">
            <table id="tableReport" class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th
                            class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider py-3 px-4 bg-slate-50 dark:bg-slate-700/50 rounded-l-lg">
                            Pelanggan</th>
                        <th class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider py-3 px-4 bg-slate-50 dark:bg-slate-700/50">Akun
                            PPPoE</th>
                        <th class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider py-3 px-4 bg-slate-50 dark:bg-slate-700/50">
                            Jatuh Tempo</th>
                        <th class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider py-3 px-4 bg-slate-50 dark:bg-slate-700/50">
                            Nominal</th>
                        <th class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider py-3 px-4 bg-slate-50 dark:bg-slate-700/50">
                            Status</th>
                        <th
                            class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider py-3 px-4 bg-slate-50 dark:bg-slate-700/50 rounded-r-lg text-center">
                            Invoice</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach($invoices as $inv)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            <td class="px-4 py-3 align-middle font-medium text-slate-900 dark:text-white">
                                {{ $inv->customer->name ?? 'User Deleted' }}</td>
                            <td class="px-4 py-3 align-middle text-sm text-slate-500 dark:text-slate-400 font-mono">
                                {{ $inv->customer->pppoe_username ?? '-' }}</td>
                            <td class="px-4 py-3 align-middle text-sm text-slate-600 dark:text-slate-300">
                                {{ \Carbon\Carbon::parse($inv->due_date)->format('d M Y') }}</td>
                            <td class="px-4 py-3 align-middle font-bold text-slate-700 dark:text-slate-200">Rp
                                {{ number_format($inv->price, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 align-middle">
                                @if($inv->status == 'paid')
                                    <span
                                        class="inline-flex items-center rounded-full bg-green-50 dark:bg-green-900/30 px-2 py-1 text-xs font-medium text-green-700 dark:text-green-400 ring-1 ring-inset ring-green-600/20 dark:ring-green-500/30">
                                        <i class="fas fa-check mr-1"></i> Lunas
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center rounded-full bg-red-50 dark:bg-red-900/30 px-2 py-1 text-xs font-medium text-red-700 dark:text-red-400 ring-1 ring-inset ring-red-600/10 dark:ring-red-500/30">
                                        <i class="fas fa-clock mr-1"></i> Belum Bayar
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-middle text-center">
                                <a href="{{ route('billing.print', $inv->id) }}" target="_blank"
                                    class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 text-sm font-medium hover:underline">
                                    <i class="fas fa-print mr-1"></i> Cetak
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
            $('#tableReport').DataTable({
                responsive: true,
                order: [[2, "desc"]]
            });
        });
    </script>
@endpush