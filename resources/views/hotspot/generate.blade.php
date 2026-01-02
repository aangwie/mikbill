@extends('layouts.app2')

@section('title', 'Generate Hotspot Accounts')
@section('header', 'Generate Hotspot Accounts')
@section('subheader', 'Generate akun hotspot secara massal dengan masa aktif yang dimulai setelah login pertama.')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Form Section -->
        <div class="lg:col-span-1">
            <div
                class="bg-white dark:bg-slate-800 shadow-sm rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden sticky top-20">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white">Form Generate</h3>
                </div>
                <div class="p-6">
                @if(!$isConnected)
                    <div class="mb-4 p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-700 text-sm flex items-start gap-3">
                        <i class="fas fa-exclamation-triangle mt-0.5"></i>
                        <div>
                            <p class="font-bold">Router Terputus!</p>
                            <p class="text-xs">Sistem tidak dapat terhubung ke Mikrotik.</p>
                        </div>
                    </div>
                @elseif(!$isHotspotReady)
                    <div class="mb-4 p-4 bg-amber-50 border-l-4 border-amber-500 text-amber-700 text-sm flex items-start gap-3">
                        <i class="fas fa-info-circle mt-0.5"></i>
                        <div>
                            <p class="font-bold">Hotspot Belum Siap!</p>
                            <p class="text-xs">Server Hotspot tidak terdeteksi di Mikrotik. Silahkan buat server hotspot terlebih dahulu.</p>
                        </div>
                    </div>
                @endif

                @if(session('success'))
                    <div class="mb-4 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 text-sm">
                        {{ session('success') }}
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="mb-4 p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-700 text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('hotspot.generate.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <fieldset @disabled(!$isConnected || !$isHotspotReady) class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Prefix
                                    Username</label>
                                <input type="text" name="prefix" value="{{ old('prefix', 'MB-') }}"
                                    class="w-full rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white text-sm focus:ring-[#352f99] focus:border-[#352f99] disabled:bg-slate-50 disabled:text-slate-400"
                                    placeholder="Contoh: VIP-">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Jumlah
                                    Voucher</label>
                                <input type="number" name="quantity" value="{{ old('quantity', 10) }}" min="1" max="100"
                                    required
                                    class="w-full rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white text-sm focus:ring-[#352f99] focus:border-[#352f99] disabled:bg-slate-50 disabled:text-slate-400">
                                <p class="mt-1 text-xs text-slate-500">Maksimal 100 voucher sekali generate.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Profil
                                    Hotspot</label>
                                <select name="profile" required
                                    class="w-full rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white text-sm focus:ring-[#352f99] focus:border-[#352f99] disabled:bg-slate-50 disabled:text-slate-400">
                                    @forelse($profiles as $profile)
                                        <option value="{{ $profile['name'] }}">{{ $profile['name'] }}</option>
                                    @empty
                                        <option value="">Tidak ada profil</option>
                                    @endforelse
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Masa Aktif
                                    (Period)</label>
                                <select name="period" required
                                    class="w-full rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white text-sm focus:ring-[#352f99] focus:border-[#352f99] disabled:bg-slate-50 disabled:text-slate-400">
                                    <option value="1d">1 Hari</option>
                                    <option value="1w">1 Minggu</option>
                                    <option value="1m">1 Bulan</option>
                                </select>
                                <p class="mt-1 text-xs text-slate-500">Countdown dimulai saat login pertama kali.</p>
                            </div>

                            <div class="pt-4">
                                <button type="submit"
                                    class="w-full py-2.5 px-4 bg-[#352f99] hover:bg-indigo-700 text-white font-bold rounded-lg transition duration-200 flex items-center justify-center gap-2 shadow-lg shadow-indigo-200 dark:shadow-none disabled:bg-slate-400 disabled:shadow-none disabled:cursor-not-allowed">
                                    <i class="fas fa-magic"></i> Generate Sekarang
                                </button>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div class="lg:col-span-3">
            <div
                class="bg-white dark:bg-slate-800 shadow-sm rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div
                    class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white">Daftar Akun Hotspot</h3>
                </div>
                <div class="p-6 overflow-x-auto">
                    <table id="tableManagedUsers" class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 text-xs uppercase tracking-wider">
                                <th class="px-6 py-4 font-bold border-b border-slate-200 dark:border-slate-700">Username
                                </th>
                                <th class="px-6 py-4 font-bold border-b border-slate-200 dark:border-slate-700">Password
                                </th>
                                <th class="px-6 py-4 font-bold border-b border-slate-200 dark:border-slate-700">Profile</th>
                                <th class="px-6 py-4 font-bold border-b border-slate-200 dark:border-slate-700">Uptime</th>
                                <th class="px-6 py-4 font-bold border-b border-slate-200 dark:border-slate-700">Sisa Waktu
                                </th>
                                <th class="px-6 py-4 font-bold border-b border-slate-200 dark:border-slate-700 text-center">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach($managedUsers as $user)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                    <td class="px-6 py-4 text-sm font-bold text-indigo-600 dark:text-indigo-400 font-mono">
                                        {{ $user['name'] }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 font-mono">
                                        {{ $user['password'] ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                                        <span
                                            class="px-2 py-1 bg-slate-100 dark:bg-slate-700 rounded text-xs">{{ $user['profile'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400 font-mono text-xs">
                                        {{ $user['uptime'] ?? '0s' }}</td>
                                    <td class="px-6 py-4 text-sm font-medium">
                                        @if($user['remaining_time'] == 'un-activate')
                                            <span
                                                class="px-2 py-0.5 bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded text-[10px] font-bold uppercase">un-activate</span>
                                        @elseif($user['remaining_time'] == 'Expired')
                                            <span
                                                class="px-2 py-0.5 bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 rounded text-[10px] font-bold uppercase">{{ $user['remaining_time'] }}</span>
                                        @else
                                            <span
                                                class="text-emerald-600 dark:text-emerald-400 text-xs font-bold">{{ $user['remaining_time'] }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <form action="{{ route('hotspot.destroy', $user['name']) }}" method="POST"
                                            onsubmit="return confirm('Hapus akun ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-500 hover:text-rose-700 transition">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
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
        }
    </style>
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.tailwindcss.js"></script>
    <script>
        $(document).ready(function () {
            $('#tableManagedUsers').DataTable({
                responsive: true,
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    paginate: {
                        previous: "<",
                        next: ">"
                    }
                },
                order: [[4, 'asc']] // Order by remaining time
            });
        });
    </script>
@endpush