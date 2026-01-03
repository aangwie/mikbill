@extends('layouts.app2')

@section('title', 'Pilih Paket Langganan')
@section('header', 'Paket Langganan')

@section('content')
    <div class="mx-auto max-w-7xl">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white sm:text-4xl">
                Tingkatkan Akun Anda
            </h2>
            <p class="mt-4 text-xl text-slate-500 dark:text-slate-400">
                Pilih paket yang paling sesuai dengan kebutuhan infrastruktur jaringan Anda.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($plans as $p)
                <div
                    class="relative flex flex-col p-8 bg-white dark:bg-slate-800 border-2 {{ Auth::user()->plan_id == $p->id ? 'border-primary-500 shadow-xl shadow-primary-500/10' : 'border-slate-100 dark:border-slate-700' }} rounded-3xl transition-all duration-300 hover:border-primary-500">
                    @if (Auth::user()->plan_id == $p->id)
                        <div
                            class="absolute top-0 -translate-y-1/2 left-1/2 -translate-x-1/2 px-4 py-1 bg-primary-600 text-white text-xs font-bold rounded-full uppercase tracking-widest shadow-lg">
                            Paket Saat Ini
                        </div>
                    @endif

                    <div class="mb-8">
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ $p->name }}</h3>
                        <p class="text-sm text-slate-500 mt-1">Solusi manajemen jaringan terbaik.</p>
                    </div>

                    <div class="space-y-4 mb-8">
                        <div class="flex items-center gap-3">
                            <div
                                class="h-6 w-6 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400">
                                <i class="fas fa-check text-[10px]"></i>
                            </div>
                            <span class="text-sm text-slate-600 dark:text-slate-300">Maks.
                                <strong>{{ $p->max_routers }}</strong> Mikrotik Router</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div
                                class="h-6 w-6 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400">
                                <i class="fas fa-check text-[10px]"></i>
                            </div>
                            <span class="text-sm text-slate-600 dark:text-slate-300">Maks.
                                <strong>{{ $p->max_vouchers }}</strong> Voucher Hotspot</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div
                                class="h-6 w-6 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400">
                                <i class="fas fa-check text-[10px]"></i>
                            </div>
                            <span class="text-sm text-slate-600 dark:text-slate-300">Maks.
                                <strong>{{ $p->max_customers }}</strong> Pelanggan Database</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div
                                class="h-6 w-6 rounded-full {{ $p->wa_gateway ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400' : 'bg-slate-100 dark:bg-slate-700 text-slate-400' }} flex items-center justify-center">
                                <i class="fas {{ $p->wa_gateway ? 'fa-check' : 'fa-times' }} text-[10px]"></i>
                            </div>
                            <span
                                class="text-sm {{ $p->wa_gateway ? 'text-slate-600 dark:text-slate-300 font-bold' : 'text-slate-400 line-through opacity-50' }}">WhatsApp
                                Gateway</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div
                                class="h-6 w-6 rounded-full {{ $p->customer_support ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400' : 'bg-slate-100 dark:bg-slate-700 text-slate-400' }} flex items-center justify-center">
                                <i class="fas {{ $p->customer_support ? 'fa-check' : 'fa-times' }} text-[10px]"></i>
                            </div>
                            <span
                                class="text-sm {{ $p->customer_support ? 'text-slate-600 dark:text-slate-300 font-bold' : 'text-slate-400 line-through opacity-50' }}">Customer
                                Support</span>
                        </div>
                    </div>

                    <div x-data="{ cycle: 'monthly' }" class="mt-auto">
                        <div class="flex p-1 bg-slate-100 dark:bg-slate-900/50 rounded-xl mb-6">
                            <button @click="cycle = 'monthly'" :class="cycle === 'monthly' ? 'bg-white dark:bg-slate-700 shadow-sm text-primary-600 dark:text-primary-400' :
                                                                    'text-slate-500 hover:text-slate-700'"
                                class="flex-1 py-2 text-xs font-bold rounded-lg transition-all">BULANAN</button>
                            <button @click="cycle = 'semester'" :class="cycle === 'semester' ? 'bg-white dark:bg-slate-700 shadow-sm text-primary-600 dark:text-primary-400' :
                                                                    'text-slate-500 hover:text-slate-700'"
                                class="flex-1 py-2 text-xs font-bold rounded-lg transition-all">6 BULAN</button>
                            <button @click="cycle = 'annual'" :class="cycle === 'annual' ? 'bg-white dark:bg-slate-700 shadow-sm text-primary-600 dark:text-primary-400' :
                                                                    'text-slate-500 hover:text-slate-700'"
                                class="flex-1 py-2 text-xs font-bold rounded-lg transition-all">TAHUNAN</button>
                        </div>

                        <div class="flex items-baseline justify-center gap-1 mb-6">
                            <span class="text-3xl font-bold text-slate-900 dark:text-white"
                                x-text="'Rp' + (cycle === 'monthly' ? '{{ number_format($p->price_monthly, 0, ',', '.') }}' : (cycle === 'semester' ? '{{ number_format($p->price_semester, 0, ',', '.') }}' : '{{ number_format($p->price_annual, 0, ',', '.') }}'))"></span>
                            <span class="text-slate-500 text-sm"
                                x-text="'/' + (cycle === 'monthly' ? 'bln' : (cycle === 'semester' ? '6 bln' : 'thn'))"></span>
                        </div>

                        @if(!is_null($p->stock_limit))
                            @php
                                $used_stock = $p->users()->count();
                                $remaining = max(0, $p->stock_limit - $used_stock);
                            @endphp
                            <div class="mb-6">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Kuota Terpakai</span>
                                    <span class="text-[10px] font-bold uppercase tracking-wider {{ $remaining > 5 ? 'text-primary-600' : 'text-rose-500' }}">
                                        {{ $used_stock }}/{{ $p->stock_limit }} Slot
                                    </span>
                                </div>
                                <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                                    <div class="h-full {{ $remaining > 5 ? 'bg-primary-500' : 'bg-rose-500' }} rounded-full" style="width: {{ $p->stock_limit > 0 ? ($used_stock / $p->stock_limit) * 100 : 0 }}%"></div>
                                </div>
                                @if($remaining <= 5 && $remaining > 0)
                                    <p class="text-[9px] text-rose-500 mt-1 font-medium italic">Segera amankan, tinggal {{ $remaining }} slot lagi!</p>
                                @endif
                            </div>
                        @endif

                        <form action="{{ route('plans.checkout') }}" method="POST">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $p->id }}">
                            <input type="hidden" name="cycle" :value="cycle">
                            <button type="submit" {{ Auth::user()->plan_id == $p->id || !$p->is_active || (!is_null($p->stock_limit) && $p->users()->count() >= $p->stock_limit) ? 'disabled' : '' }}
                                class="w-full py-4 px-6 rounded-2xl font-bold tracking-wide transition-all duration-300 {{ Auth::user()->plan_id == $p->id || !$p->is_active || (!is_null($p->stock_limit) && $p->users()->count() >= $p->stock_limit) ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : 'bg-primary-600 text-white shadow-lg shadow-primary-500/25 hover:bg-primary-500 hover:-translate-y-1' }}">
                                @if(Auth::user()->plan_id == $p->id)
                                    PAKET AKTIF
                                @elseif(!$p->is_active)
                                    TIDAK TERSEDIA
                                @elseif(!is_null($p->stock_limit) && $p->users()->count() >= $p->stock_limit)
                                    STOK HABIS
                                @else
                                    PILIH PAKET
                                @endif
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        @if (isset($company))
            <div
                class="mt-16 bg-white dark:bg-slate-800 rounded-3xl p-8 border border-slate-100 dark:border-slate-700 text-center">
                <p class="text-slate-600 dark:text-slate-400">
                    Butuh penawaran khusus untuk skala enterprise?
                    <a href="https://wa.me/{{ $company->phone }}" class="text-primary-600 font-bold hover:underline">Hubungi
                        Kami</a>
                </p>
            </div>
        @endif
    </div>
@endsection