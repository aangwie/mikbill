@extends('layouts.frontend')

@section('title', 'Harga Paket Langganan')

@section('content')
    <div class="bg-white dark:bg-slate-900 py-24 sm:py-32">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-4xl text-center">
                <h2 class="text-base font-semibold leading-7 text-primary-600 dark:text-primary-400">Harga</h2>
                <p class="mt-2 text-4xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-5xl">Pilih Paket
                    yang Sesuai dengan Kebutuhan Anda</p>
                <p class="mt-6 text-lg leading-8 text-slate-600 dark:text-slate-400">Solusi terbaik untuk manajemen Mikrotik
                    Router Anda dengan harga yang terjangkau.</p>
            </div>

            <div x-data="{ cycle: 'monthly' }" class="mt-16 flex flex-col items-center">
                <!-- Cycle Toggle -->
                <div class="flex items-center gap-x-4">
                    <span :class="cycle === 'monthly' ? 'text-slate-900 dark:text-white' : 'text-slate-500'"
                        class="text-sm font-semibold transition-colors">Bulanan</span>
                    <button
                        @click="cycle = cycle === 'monthly' ? 'semester' : (cycle === 'semester' ? 'annual' : 'monthly')"
                        class="relative flex h-6 w-11 flex-none cursor-pointer rounded-full bg-slate-200 dark:bg-slate-700 p-1 transition-colors duration-200 ease-in-out focus:outline-none ring-1 ring-inset ring-slate-900/5 dark:ring-white/10">
                        <span
                            :class="cycle === 'monthly' ? 'translate-x-0' : (cycle === 'semester' ? 'translate-x-[1.25rem]' : 'translate-x-[1.25rem]')"
                            class="h-4 w-4 transform rounded-full bg-white shadow-sm ring-1 ring-slate-900/5 transition duration-200 ease-in-out"></span>
                    </button>
                    <div class="flex flex-col">
                        <span :class="cycle === 'semester' ? 'text-slate-900 dark:text-white' : 'text-slate-500'"
                            class="text-sm font-semibold transition-colors">6 Bulan</span>
                        <span :class="cycle === 'annual' ? 'text-slate-900 dark:text-white' : 'text-slate-500'"
                            class="text-sm font-semibold transition-colors">12 Bulan <span
                                class="text-green-500 text-[10px] ml-1">Hemat!</span></span>
                    </div>
                </div>

                <!-- Pricing Cards -->
                <div class="mt-10 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($plans as $p)
                        <div
                            class="flex flex-col justify-between rounded-3xl bg-white dark:bg-slate-800 p-8 ring-1 ring-slate-200 dark:ring-slate-700 xl:p-10 hover:ring-primary-500 dark:hover:ring-primary-500 transition-all duration-300">
                            <div>
                                <h3 class="text-xl font-bold leading-7 text-slate-900 dark:text-white">{{ $p->name }}</h3>
                                <div class="mt-4 flex items-baseline gap-x-2">
                                    <span class="text-4xl font-bold tracking-tight text-slate-900 dark:text-white"
                                        x-text="'Rp' + (cycle === 'monthly' ? '{{ number_format($p->price_monthly, 0, ',', '.') }}' : (cycle === 'semester' ? '{{ number_format($p->price_semester, 0, ',', '.') }}' : '{{ number_format($p->price_annual, 0, ',', '.') }}'))"></span>
                                    <span class="text-sm font-semibold leading-6 text-slate-500"
                                        x-text="'/' + (cycle === 'monthly' ? 'bln' : (cycle === 'semester' ? '6 bln' : 'thn'))"></span>
                                </div>
                                <ul role="list" class="mt-8 space-y-3 text-sm leading-6 text-slate-600 dark:text-slate-400">
                                    <li class="flex gap-x-3">
                                        <i class="fas fa-check text-primary-500 mt-1"></i>
                                        <span>Maks. <strong>{{ $p->max_routers }}</strong> Mikrotik Router</span>
                                    </li>
                                    <li class="flex gap-x-3">
                                        <i class="fas fa-check text-primary-500 mt-1"></i>
                                        <span>Maks. <strong>{{ $p->max_vouchers }}</strong> Voucher Hotspot</span>
                                    </li>
                                    <li class="flex gap-x-3">
                                        <i class="fas fa-check text-primary-500 mt-1"></i>
                                        <span>Maks. <strong>{{ $p->max_customers }}</strong> Pelanggan Database</span>
                                    </li>
                                    <li class="flex gap-x-3 {{ $p->wa_gateway ? '' : 'opacity-50 line-through' }}">
                                        <i
                                            class="fas {{ $p->wa_gateway ? 'fa-check text-primary-500' : 'fa-times text-slate-400' }} mt-1"></i>
                                        <span>WhatsApp Gateway (Blast & Notif)</span>
                                    </li>
                                    <li class="flex gap-x-3 {{ $p->customer_support ? '' : 'opacity-50 line-through' }}">
                                        <i
                                            class="fas {{ $p->customer_support ? 'fa-check text-primary-500' : 'fa-times text-slate-400' }} mt-1"></i>
                                        <span>Layanan Customer Support</span>
                                    </li>
                                </ul>
                            </div>
                            <a href="{{ route('register', ['plan' => $p->id]) }}"
                                class="mt-8 block rounded-xl bg-primary-600 px-3 py-2 text-center text-sm font-bold leading-6 text-white shadow-sm hover:bg-primary-500 transition-all">Mulai
                                Sekarang</a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
bitumen