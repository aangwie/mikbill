@extends('layouts.app2')

@section('title', 'Billing & Tagihan')
@section('header', 'Billing & Kasir')
@section('subheader', 'Kelola tagihan pelanggan, pembayaran, dan invoice.')

@section('content')

    <div x-data="{ 
                    showCreateModal: false, 
                    showGenerateModal: false 
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
        <div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-3">
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
        </div>

        <!-- Toolbar -->
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex gap-2">
                <button @click="showCreateModal = true"
                    class="inline-flex items-center rounded-lg bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 transition-all">
                    <i class="fas fa-plus mr-2"></i> Buat Manual
                </button>
                @if(auth()->user()->role == 'admin')
                    <button @click="showGenerateModal = true"
                        class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 transition-all">
                        <i class="fas fa-magic mr-2"></i> Generate Massal
                    </button>
                @endif
            </div>
        </div>

        <!-- Table Card -->
        <div
            class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm ring-1 ring-slate-900/5 dark:ring-slate-700/50 overflow-hidden">
            <div class="overflow-x-auto p-4">
                <table id="tableBilling" class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th
                                class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider py-3 px-4 bg-slate-50 dark:bg-slate-700/50 rounded-l-lg">
                                No. Invoice</th>
                            <th
                                class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider py-3 px-4 bg-slate-50 dark:bg-slate-700/50">
                                Pelanggan</th>
                            <th
                                class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider py-3 px-4 bg-slate-50 dark:bg-slate-700/50 hidden sm:table-cell">
                                Bulan/Tahun</th>
                            <th
                                class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider py-3 px-4 bg-slate-50 dark:bg-slate-700/50">
                                Total</th>
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
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors group">
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
                                    @php
                                        $displayPrice = $inv->price > 0 ? $inv->price : ($inv->customer->monthly_price ?? 0);
                                    @endphp
                                    Rp {{ number_format($displayPrice, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 align-middle">
                                    @if($inv->status == 'paid')
                                        <span
                                            class="inline-flex items-center rounded-full bg-green-50 dark:bg-green-900/30 px-2 py-1 text-xs font-medium text-green-700 dark:text-green-400 ring-1 ring-inset ring-green-600/20 dark:ring-green-500/30">Lunas</span>
                                    @else
                                        <span
                                            class="inline-flex items-center rounded-full bg-red-50 dark:bg-red-900/30 px-2 py-1 text-xs font-medium text-red-700 dark:text-red-400 ring-1 ring-inset ring-red-600/10 dark:ring-red-500/30">Belum
                                            Bayar</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 align-middle text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('billing.print', $inv->id) }}" target="_blank"
                                            class="p-1.5 text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-md transition-colors"
                                            title="Print Invoice">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        @if($inv->status == 'unpaid')
                                            <form action="{{ route('billing.pay', $inv->id) }}" method="POST" class="d-inline"
                                                onsubmit="return confirm('Tandai invoice ini sebagai LUNAS?');">
                                                @csrf
                                                <button
                                                    class="p-1.5 text-green-600 hover:text-green-700 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-md transition-colors"
                                                    title="Bayar Manual">
                                                    <i class="fas fa-check-double"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('billing.cancel', $inv->id) }}" method="POST" class="d-inline"
                                                onsubmit="return confirm('Batalkan pembayaran ini? Status akan kembali menjadi UNPAID.');">
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
        <div x-show="showCreateModal" class="relative z-500" style="display:none;">
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
                                            <select name="month"
                                                class="mt-1 block w-full rounded-md border-0 py-1.5 text-slate-900 dark:text-white dark:bg-slate-700 ring-1 ring-inset ring-slate-300 dark:ring-slate-600 sm:text-sm">
                                                @for($i = 1; $i <= 12; $i++)
                                                    <option value="{{ $i }}" {{ date('n') == $i ? 'selected' : '' }}>{{ $i }}
                                                </option> @endfor
                                            </select>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-slate-900 dark:text-slate-300">Tahun</label>
                                            <select name="year"
                                                class="mt-1 block w-full rounded-md border-0 py-1.5 text-slate-900 dark:text-white dark:bg-slate-700 ring-1 ring-inset ring-slate-300 dark:ring-slate-600 sm:text-sm">
                                                <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                                                <option value="{{ date('Y') + 1 }}">{{ date('Y') + 1 }}</option>
                                            </select>
                                        </div>
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
                                untuk pelanggan aktif **Anda**.
                            </p>

                            <!-- Initial Form -->
                            <div id="genInitial" class="space-y-4">
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
                                            <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                                            <option value="{{ date('Y') + 1 }}">{{ date('Y') + 1 }}</option>
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
            $('.select2-modal').select2({ width: '100%' });
        });

        async function startGenerate() {
            const month = $('#genMonth').val();
            const year = $('#genYear').val();
            const dueDate = $('#genDueDate').val();
            const log = $('#genLog');

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
                const listResp = await fetch(`{{ route('billing.list') }}?month=${month}&year=${year}`);
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
    </script>
@endpush