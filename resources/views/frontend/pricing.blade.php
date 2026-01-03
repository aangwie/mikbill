@extends('layouts.frontend')

@section('title', 'Harga Paket Langganan - BillNesia')

@section('content')
    <div class="bg-white dark:bg-slate-900 py-24 sm:py-32 transition-colors duration-300 px-6">
        <div class="mx-auto max-w-7xl">
            <div class="mx-auto max-w-4xl text-center">
                <p class="mt-2 text-4xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-5xl">
                    Pilih Paket yang Sesuai dengan Kebutuhan Anda</p>
                <p class="mt-6 text-lg leading-8 text-slate-600 dark:text-slate-400">Solusi terbaik untuk
                    manajemen Mikrotik Router Anda dengan harga yang terjangkau.</p>
            </div>

            <div x-data="{ cycle: 'monthly' }" class="mt-16 flex flex-col items-center">
                <!-- Cycle Selection (Radio Group) -->
                <div
                    class="inline-flex p-1 bg-slate-100 dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-inner">
                    <button @click="cycle = 'monthly'"
                        :class="cycle === 'monthly' ? 'bg-white dark:bg-[#352f99] shadow-md text-[#352f99] dark:text-white scale-105' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'"
                        class="px-8 py-2.5 rounded-xl text-sm font-bold transition-all duration-300 transform">
                        Bulanan
                    </button>
                    <button @click="cycle = 'semester'"
                        :class="cycle === 'semester' ? 'bg-white dark:bg-[#352f99] shadow-md text-[#352f99] dark:text-white scale-105' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'"
                        class="px-8 py-2.5 rounded-xl text-sm font-bold transition-all duration-300 transform">
                        6 Bulan
                    </button>
                    <button @click="cycle = 'annual'"
                        :class="cycle === 'annual' ? 'bg-white dark:bg-[#352f99] shadow-md text-[#352f99] dark:text-white scale-105' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'"
                        class="px-8 py-2.5 rounded-xl text-sm font-bold transition-all duration-300 transform relative">
                        12 Bulan
                        <span
                            class="absolute -top-1.5 -right-1.5 bg-green-500 text-white text-[9px] font-black px-2 py-0.5 rounded-full shadow-sm animate-pulse">HEMAT!</span>
                    </button>
                </div>

                <!-- Pricing Cards -->
                <div class="mt-10 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 w-full max-w-7xl">
                    @foreach($plans as $p)
                        <div
                            class="flex flex-col justify-between rounded-3xl bg-white dark:bg-slate-800 p-8 ring-1 ring-slate-200 dark:ring-slate-700 xl:p-10 hover:ring-[#352f99] dark:hover:ring-[#352f99] transition-all duration-300 shadow-sm hover:shadow-xl">
                            <div>
                                <h3 class="text-xl font-bold leading-7 text-slate-900 dark:text-white">
                                    {{ $p->name }}
                                </h3>
                                <div
                                    class="mt-4 flex items-baseline gap-x-2 border-b border-slate-100 dark:border-slate-700 pb-6">
                                    <span class="text-4xl font-bold tracking-tight text-slate-900 dark:text-white"
                                        x-text="'Rp' + (cycle === 'monthly' ? '{{ number_format($p->price_monthly, 0, ',', '.') }}' : (cycle === 'semester' ? '{{ number_format($p->price_semester, 0, ',', '.') }}' : '{{ number_format($p->price_annual, 0, ',', '.') }}'))"></span>
                                    <span class="text-sm font-semibold leading-6 text-slate-500"
                                        x-text="'/' + (cycle === 'monthly' ? 'bln' : (cycle === 'semester' ? '6 bln' : 'thn'))"></span>
                                </div>
                                <ul role="list" class="mt-8 space-y-3 text-sm leading-6 text-slate-600 dark:text-slate-400">
                                    <li class="flex gap-x-3 items-center">
                                        <i class="fas fa-check text-emerald-500"></i>
                                        <span>Maks. <strong>{{ $p->max_routers }}</strong> Mikrotik Router</span>
                                    </li>
                                    <li class="flex gap-x-3 items-center">
                                        <i class="fas fa-check text-emerald-500"></i>
                                        <span>Maks. <strong>{{ $p->max_vouchers }}</strong> Voucher Hotspot</span>
                                    </li>
                                    <li class="flex gap-x-3 items-center">
                                        <i class="fas fa-check text-emerald-500"></i>
                                        <span>Maks. <strong>{{ $p->max_customers }}</strong> Pelanggan
                                            Database</span>
                                    </li>
                                    <li class="flex gap-x-3 items-center {{ $p->wa_gateway ? '' : 'opacity-50 line-through' }}">
                                        <i
                                            class="fas {{ $p->wa_gateway ? 'fa-check text-emerald-500' : 'fa-times text-slate-400' }}"></i>
                                        <span>WhatsApp Gateway</span>
                                    </li>
                                    <li
                                        class="flex gap-x-3 items-center {{ $p->customer_support ? '' : 'opacity-50 line-through' }}">
                                        <i
                                            class="fas {{ $p->customer_support ? 'fa-check text-emerald-500' : 'fa-times text-slate-400' }}"></i>
                                        <span>Layanan Customer Support</span>
                                    </li>
                                </ul>
                            </div>

                            <div class="mt-8">
                                @if(!is_null($p->stock_limit))
                                    @php
                                        $used_stock = $p->users()->count();
                                        $remaining = max(0, $p->stock_limit - $used_stock);
                                    @endphp
                                    <div class="mb-4 flex items-center gap-2">
                                        <div class="h-2 flex-1 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                                            <div class="h-full bg-indigo-500 rounded-full"
                                                style="width: {{ $p->stock_limit > 0 ? ($used_stock / $p->stock_limit) * 100 : 0 }}%">
                                            </div>
                                        </div>
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                            @if($remaining > 0)
                                                {{ $remaining }} Slot Tersisa
                                            @else
                                                STOK HABIS
                                            @endif
                                        </span>
                                    </div>
                                @endif

                                @if(!is_null($p->stock_limit) && $p->users()->count() >= $p->stock_limit)
                                    <button disabled
                                        class="w-full block rounded-xl bg-slate-100 dark:bg-slate-800 px-3 py-4 text-center text-sm font-bold leading-6 text-slate-400 cursor-not-allowed border border-slate-200 dark:border-slate-700">
                                        Stok Habis
                                    </button>
                                @else
                                    <a href="{{ route('register', ['plan' => $p->id]) }}"
                                        class="block rounded-xl bg-[#352f99] px-3 py-4 text-center text-sm font-bold leading-6 text-white shadow-lg shadow-indigo-500/25 hover:bg-indigo-700 hover:-translate-y-1 transition-all">Mulai
                                        Sekarang</a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection