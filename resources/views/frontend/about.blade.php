@extends('layouts.frontend')

@section('title', 'Tentang Kami - BillNesia')

@section('content')
    <div class="relative py-24 sm:py-32 overflow-hidden">
        <div class="mx-auto max-w-4xl px-6 lg:px-8 relative z-10">
            <div class="text-center mb-16">
                <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-6xl mb-6">
                    Tentang Kami</h1>
                <p class="text-lg leading-8 text-slate-600 dark:text-slate-400">Kenali lebih dekat siapa kami dan apa
                    misi yang kami bawa untuk Anda.</p>
            </div>

            <div
                class="bg-white dark:bg-slate-800 rounded-3xl shadow-xl border border-slate-200 dark:border-white/10 p-8 sm:p-12 prose dark:prose-invert max-w-none">
                {!! nl2br(e($setting->about_us)) !!}
            </div>
        </div>
    </div>
@endsection