@extends('layouts.app2')

@section('title', 'Paket Plan')
@section('header', 'Pilih Paket Layanan')
@section('subheader', 'Upgrade akun Anda untuk mendapatkan kapasitas lebih besar dan fitur premium.')

@section('content')
    <div class="mt-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($plans as $p)
                <div
                    class="relative flex flex-col p-8 bg-white dark:bg-slate-800 rounded-3xl shadow-xl border-2 {{ auth()->user()->plan_id == $p->id ? 'border-primary-500 ring-4 ring-primary-500/10' : 'border-slate-100 dark:border-slate-700' }} transition-all hover:scale-[1.02]">
                    @if(auth()->user()->plan_id == $p->id)
                        <div
                            class="absolute -top-4 left-1/2 -translate-x-1/2 bg-primary-600 text-white px-4 py-1 rounded-full text-xs font-bold tracking-widest uppercase">
                            PAKET ANDA</div>
                    @endif

                    <div class="mb-6">
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
                                <strong>{{ $p->max_customers }}</strong> Pelanggan Database</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div
                                class="h-6 w-6 rounded-full {{ $p->wa_gateway ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400' : 'bg-slate-100 dark:bg-slate-700 text-slate-400' }} flex items-center justify-center">
                                <i class="fas {{ $p->wa_gateway ? 'fa-check' : 'fa-times' }} text-[10px]"></i>
                            </div>
                            <span
                                class="text-sm {{ $p->wa_gateway ? 'text-slate-600 dark:text-slate-300 font-bold text-green-600' : 'text-slate-400' }}">WhatsApp
                                Gateway (Blast & Notif)</span>
                        </div>
                    </div>

                    <div x-data="{ cycle: 'monthly' }" class="mt-auto">
                        <div class="flex bg-slate-100 dark:bg-slate-900 p-1 rounded-xl mb-6">
                            <button @click="cycle = 'monthly'"
                                :class="cycle === 'monthly' ? 'bg-white dark:bg-slate-700 shadow-sm text-slate-900 dark:text-white' : 'text-slate-500'"
                                class="flex-1 py-1.5 text-[10px] font-bold uppercase rounded-lg transition-all">Bulanan</button>
                            <button @click="cycle = 'semester'"
                                :class="cycle === 'semester' ? 'bg-white dark:bg-slate-700 shadow-sm text-slate-900 dark:text-white' : 'text-slate-500'"
                                class="flex-1 py-1.5 text-[10px] font-bold uppercase rounded-lg transition-all">Semester</button>
                            <button @click="cycle = 'annual'"
                                :class="cycle === 'annual' ? 'bg-white dark:bg-slate-700 shadow-sm text-slate-900 dark:text-white' : 'text-slate-500'"
                                class="flex-1 py-1.5 text-[10px] font-bold uppercase rounded-lg transition-all">Tahunan</button>
                        </div>

                        <div class="flex items-baseline gap-1 mb-6">
                            <span class="text-3xl font-extrabold text-slate-900 dark:text-white"
                                x-text="'Rp' + (cycle === 'monthly' ? '{{ number_format($p->price_monthly, 0, ',', '.') }}' : (cycle === 'semester' ? '{{ number_format($p->price_semester, 0, ',', '.') }}' : '{{ number_format($p->price_annual, 0, ',', '.') }}'))">
                            </span>
                            <span class="text-slate-500 text-sm"
                                x-text="'/' + (cycle === 'monthly' ? 'bln' : (cycle === 'semester' ? '6 bln' : 'thn'))"></span>
                        </div>

                        @if(auth()->user()->plan_id == $p->id)
                            <button disabled
                                class="block w-full text-center py-4 rounded-2xl bg-slate-100 dark:bg-slate-700 text-slate-400 cursor-not-allowed text-sm font-bold">
                                Paket Aktif
                            </button>
                        @else
                            <form action="{{ route('plans.checkout') }}" method="POST">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $p->id }}">
                                <input type="hidden" name="cycle" :value="cycle">
                                <button type="submit"
                                    class="block w-full text-center py-4 rounded-2xl bg-primary-600 hover:bg-primary-500 text-white shadow-lg shadow-primary-500/25 text-sm font-bold transition-all">
                                    Pilih Paket
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection