@extends('layouts.app2')

@section('title', 'Riwayat Saldo - ' . $customer->name)
@section('header', 'Riwayat Saldo')
@section('subheader', $customer->name . ' (' . $customer->internet_number . ')')

@section('content')

    {{-- Summary Card --}}
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('customers.index') }}"
                class="inline-flex items-center rounded-lg bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 transition-all">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
            <div class="flex items-center gap-3 bg-white dark:bg-slate-800 rounded-xl px-5 py-3 shadow-sm ring-1 ring-slate-900/5 dark:ring-slate-700/50">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/40">
                    <i class="fas fa-wallet text-emerald-600 dark:text-emerald-400"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total Saldo</p>
                    <p id="totalSaldo" class="text-xl font-bold text-emerald-600 dark:text-emerald-400">
                        Rp {{ number_format($totalBalance, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
        <button type="button" onclick="topupSaldoHistory()"
            class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 transition-all">
            <i class="fas fa-plus mr-2"></i> Tambah Saldo
        </button>
    </div>

    {{-- Table Card --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm ring-1 ring-slate-900/5 dark:ring-slate-700/50 overflow-hidden">
        <div class="overflow-x-auto p-4">
            <table id="tableBalance" class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider py-3 px-4 bg-slate-50 dark:bg-slate-700/50 rounded-l-lg">
                            No</th>
                        <th class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider py-3 px-4 bg-slate-50 dark:bg-slate-700/50">
                            Tanggal</th>
                        <th class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider py-3 px-4 bg-slate-50 dark:bg-slate-700/50">
                            Jumlah</th>
                        <th class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider py-3 px-4 bg-slate-50 dark:bg-slate-700/50">
                            Keterangan</th>
                        <th class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider py-3 px-4 bg-slate-50 dark:bg-slate-700/50 rounded-r-lg text-right">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody id="balanceBody" class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($balances as $i => $b)
                        <tr id="row-{{ $b->id }}" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            <td class="px-4 py-3 align-middle text-sm text-slate-500 dark:text-slate-400">
                                {{ $i + 1 }}
                            </td>
                            <td class="px-4 py-3 align-middle">
                                <div class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                    {{ $b->created_at->format('d M Y') }}
                                </div>
                                <div class="text-xs text-slate-400">
                                    {{ $b->created_at->format('H:i') }} WIB
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle">
                                <span class="inline-flex items-center rounded-md px-2.5 py-1 text-sm font-bold {{ $b->amount >= 0 ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300' }}">
                                    {{ $b->amount >= 0 ? '+' : '' }}Rp {{ number_format($b->amount, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 align-middle text-sm text-slate-600 dark:text-slate-300">
                                {{ $b->description ?? '-' }}
                            </td>
                            <td class="px-4 py-3 align-middle text-right">
                                <div class="flex justify-end gap-1">
                                    <button type="button"
                                        onclick="editSaldo('{{ $b->id }}', '{{ $b->amount }}', '{{ addslashes($b->description) }}')"
                                        class="p-1.5 text-amber-500 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-md transition-colors"
                                        title="Edit">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <button type="button"
                                        onclick="hapusSaldo('{{ $b->id }}')"
                                        class="p-1.5 text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors"
                                        title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyRow">
                            <td colspan="5" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="fas fa-wallet text-4xl text-slate-300 dark:text-slate-600"></i>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Belum ada riwayat saldo.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.tailwindcss.css">
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

    <script>
        const customerId = '{{ $customer->id }}';
        const customerName = '{{ addslashes($customer->name) }}';
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        $(document).ready(function() {
            @if($balances->count() > 0)
                $('#tableBalance').DataTable({
                    responsive: true,
                    order: [[1, 'desc']]
                });
            @endif
        });

        // Tambah Saldo dari halaman history
        function topupSaldoHistory() {
            Swal.fire({
                title: 'Tambah Saldo',
                html:
                    '<p class="text-sm text-gray-500 mb-4">Pelanggan: <strong>' + customerName + '</strong></p>' +
                    '<div class="text-left mb-3">' +
                    '<label class="block text-sm font-medium text-gray-700 mb-1">Jumlah (Rp)</label>' +
                    '<input id="swalAmount" type="number" class="swal2-input" placeholder="Contoh: 100000" min="1" style="margin:0; width:100%;">' +
                    '</div>' +
                    '<div class="text-left">' +
                    '<label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>' +
                    '<input id="swalDesc" type="text" class="swal2-input" placeholder="Contoh: Topup bulan Mei" style="margin:0; width:100%;">' +
                    '</div>',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-wallet mr-2"></i> Topup Sekarang',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#10b981',
                focusConfirm: false,
                preConfirm: () => {
                    const amount = document.getElementById('swalAmount').value;
                    const desc = document.getElementById('swalDesc').value;
                    if (!amount || amount <= 0) {
                        Swal.showValidationMessage('Masukkan jumlah yang valid!');
                        return false;
                    }
                    return { amount: amount, description: desc };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/customers/' + customerId + '/balance',
                        type: 'POST',
                        data: {
                            _token: csrfToken,
                            amount: result.value.amount,
                            description: result.value.description
                        },
                        success: function(res) {
                            if (res.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: res.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            }
                        },
                        error: function(xhr) {
                            let msg = xhr.responseJSON?.message || 'Terjadi kesalahan.';
                            Swal.fire('Error', msg, 'error');
                        }
                    });
                }
            });
        }

        // Edit Saldo
        function editSaldo(id, amount, description) {
            Swal.fire({
                title: 'Edit Saldo',
                html:
                    '<div class="text-left mb-3">' +
                    '<label class="block text-sm font-medium text-gray-700 mb-1">Jumlah (Rp)</label>' +
                    '<input id="swalEditAmount" type="number" class="swal2-input" value="' + amount + '" min="1" style="margin:0; width:100%;">' +
                    '</div>' +
                    '<div class="text-left">' +
                    '<label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>' +
                    '<input id="swalEditDesc" type="text" class="swal2-input" value="' + description + '" style="margin:0; width:100%;">' +
                    '</div>',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-save mr-2"></i> Simpan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#f59e0b',
                focusConfirm: false,
                preConfirm: () => {
                    const amount = document.getElementById('swalEditAmount').value;
                    const desc = document.getElementById('swalEditDesc').value;
                    if (!amount || amount <= 0) {
                        Swal.showValidationMessage('Masukkan jumlah yang valid!');
                        return false;
                    }
                    return { amount: amount, description: desc };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/customer-balance/' + id,
                        type: 'PUT',
                        data: {
                            _token: csrfToken,
                            amount: result.value.amount,
                            description: result.value.description
                        },
                        success: function(res) {
                            if (res.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: res.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            }
                        },
                        error: function(xhr) {
                            let msg = xhr.responseJSON?.message || 'Terjadi kesalahan.';
                            Swal.fire('Error', msg, 'error');
                        }
                    });
                }
            });
        }

        // Hapus Saldo
        function hapusSaldo(id) {
            Swal.fire({
                title: 'Hapus Entri Saldo?',
                text: 'Data ini akan dihapus secara permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: '<i class="fas fa-trash-alt mr-2"></i> Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/customer-balance/' + id,
                        type: 'DELETE',
                        data: { _token: csrfToken },
                        success: function(res) {
                            if (res.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Dihapus!',
                                    text: res.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            }
                        },
                        error: function(xhr) {
                            let msg = xhr.responseJSON?.message || 'Terjadi kesalahan.';
                            Swal.fire('Error', msg, 'error');
                        }
                    });
                }
            });
        }
    </script>
@endpush
