@extends('layouts.frontend')

@section('title', 'Syarat & Ketentuan - BillNesia')

@section('content')
    <div class="relative py-24 sm:py-32 overflow-hidden">
        <div class="mx-auto max-w-4xl px-6 lg:px-8 relative z-10">
            <div class="text-center mb-16">
                <div
                    class="inline-block px-4 py-1.5 mb-6 text-xs font-bold uppercase tracking-widest text-primary-600 bg-primary-100 dark:bg-primary-900/30 rounded-full">
                    Kebijakan Layanan</div>
                <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-6xl mb-6">
                    Syarat & Ketentuan</h1>
                <p class="text-lg leading-8 text-slate-600 dark:text-slate-400">Harap baca dengan teliti syarat
                    penggunaan layanan kami demi kenyamanan bersama.</p>
            </div>

            <div
                class="bg-white dark:bg-slate-800 rounded-3xl shadow-xl border border-slate-200 dark:border-white/10 p-8 sm:p-12 prose dark:prose-invert max-w-none">
                {!! nl2br(e($setting->terms_conditions)) !!}
            </div>
        </div>
    </div>
@endsection