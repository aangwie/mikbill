@extends('layouts.app2')

@section('title', 'WhatsApp Gateway')
@section('header', 'WhatsApp Gateway')
@section('subheader', 'Konfigurasi API dan Broadcast Pesan.')

@section('content')

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

        <!-- Left Column: API Config -->
        <div class="xl:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 sticky top-24 overflow-hidden">
                <div class="bg-indigo-600 px-6 py-4 border-b border-indigo-500">
                    <h3 class="text-base font-bold text-white flex items-center">
                        <i class="fas fa-cog mr-2"></i> Konfigurasi API
                    </h3>
                </div>
                <div class="p-6">
                    <form action="{{ route('whatsapp.update') }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-900 mb-1">Target URL / API Host</label>
                                <input type="url" name="target_url"
                                    class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                    placeholder="https://api.gateway.com/send"
                                    value="{{ optional($setting)->target_url ?? '' }}" required>
                                <p class="mt-1 text-xs text-slate-500">Endpoint untuk POST request.</p>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-900 mb-1">API Key (Provider)</label>
                                <input type="text" name="api_key"
                                    class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                    placeholder="Provider API Key" value="{{ optional($setting)->api_key ?? '' }}" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-900 mb-1">Nomor Pengirim (Opsional)</label>
                                <input type="text" name="sender_number"
                                    class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                    placeholder="628xxx" value="{{ optional($setting)->sender_number ?? '' }}">
                            </div>
                            <div class="pt-4">
                                <button type="submit"
                                    class="w-full justify-center rounded-lg bg-indigo-600 px-3 py-2 text-sm font-bold text-white shadow-sm hover:bg-indigo-500 transition-all">
                                    <i class="fas fa-save mr-2"></i> Simpan Konfigurasi
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column: Message Center (Alpine Tabs) -->
        <div class="xl:col-span-2" x-data="{ activeTab: 'multi' }">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden min-h-[500px] flex flex-col">

                <!-- Tabs Header -->
                <div class="bg-slate-50 border-b border-slate-200">
                    <nav class="flex overflow-x-auto" aria-label="Tabs">
                        <button @click="activeTab = 'multi'"
                            :class="activeTab === 'multi' ? 'border-indigo-500 text-indigo-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                            class="whitespace-nowrap border-b-2 py-4 px-6 text-sm font-bold flex items-center transition-colors">
                            <i class="fas fa-users mr-2"></i> Multi-Send
                        </button>
                        <button @click="activeTab = 'unpaid'"
                            :class="activeTab === 'unpaid' ? 'border-indigo-500 text-indigo-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                            class="whitespace-nowrap border-b-2 py-4 px-6 text-sm font-bold flex items-center transition-colors">
                            <i class="fas fa-file-invoice-dollar mr-2"></i> Tagihan (Unpaid)
                        </button>
                        <button @click="activeTab = 'broadcast'"
                            :class="activeTab === 'broadcast' ? 'border-indigo-500 text-indigo-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                            class="whitespace-nowrap border-b-2 py-4 px-6 text-sm font-bold flex items-center transition-colors">
                            <i class="fas fa-bullhorn mr-2"></i> Broadcast All
                        </button>
                        <button @click="activeTab = 'test'"
                            :class="activeTab === 'test' ? 'border-indigo-500 text-indigo-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                            class="whitespace-nowrap border-b-2 py-4 px-6 text-sm font-bold flex items-center transition-colors">
                            <i class="fas fa-paper-plane mr-2"></i> Quick Test
                        </button>
                        <button @click="activeTab = 'api'"
                            :class="activeTab === 'api' ? 'border-indigo-500 text-indigo-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                            class="whitespace-nowrap border-b-2 py-4 px-6 text-sm font-bold flex items-center transition-colors">
                            <i class="fas fa-code mr-2"></i> API Key
                        </button>
                    </nav>
                </div>

                <!-- Tab Contents -->
                <div class="p-6 flex-1">

                    <!-- Tab: Multi Send -->
                    <div x-show="activeTab === 'multi'" style="display: none;">
                        <form action="{{ route('whatsapp.send.customer') }}" method="POST">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-bold text-slate-900 mb-1">Pilih Penerima</label>
                                    <select name="customer_ids[]" id="multiUserSelect"
                                        class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 select2-tailwind"
                                        multiple="multiple" required>
                                        @foreach($customers as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-900 mb-1">Pesan</label>
                                    <div class="relative">
                                        <textarea name="message" rows="5"
                                            class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                            required placeholder="Halo {name}, ..."></textarea>
                                        <div
                                            class="absolute bottom-2 right-2 text-xs text-slate-400 bg-white px-2 rounded border border-slate-100 shadow-sm">
                                            Gunakan {name} untuk nama pelanggan</div>
                                    </div>
                                </div>
                                <div class="flex justify-end pt-2">
                                    <button type="submit"
                                        class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-indigo-500">
                                        <i class="fas fa-paper-plane mr-2"></i> Kirim Pesan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Tab: Unpaid Reminder -->
                    <div x-show="activeTab === 'unpaid'" style="display: none;">
                        <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-6 rounded-r-lg">
                            <div class="flex">
                                <div class="flex-shrink-0"><i class="fas fa-exclamation-triangle text-amber-400"></i></div>
                                <div class="ml-3">
                                    <p class="text-sm text-amber-700">Kirim pengingat otomatis ke semua pelanggan yang
                                        status tagihannya <b>BELUM LUNAS</b> (Unpaid).</p>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-900 mb-1">Isi Pesan Template</label>
                                <textarea id="msgUnpaid" rows="6"
                                    class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">Halo {name}, tagihan internet Anda sebesar Rp {tagihan} belum terbayar. Mohon segera lunasi.</textarea>
                            </div>
                            <button onclick="prepareBroadcast('unpaid')"
                                class="w-full inline-flex justify-center items-center rounded-lg bg-amber-500 px-4 py-3 text-sm font-bold text-white shadow-sm hover:bg-amber-600 hover:shadow-md transition-all">
                                <i class="fab fa-whatsapp mr-2 text-lg"></i> Mulai Broadcast Reminder
                            </button>
                        </div>
                    </div>

                    <!-- Tab: All Broadcast -->
                    <div x-show="activeTab === 'broadcast'" style="display: none;">
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded-r-lg">
                            <div class="flex">
                                <div class="flex-shrink-0"><i class="fas fa-info-circle text-blue-400"></i></div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">Kirim informasi / pengumuman ke <b>SEMUA PELANGGAN
                                            AKTIF</b>.</p>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-900 mb-1">Isi Pengumuman</label>
                                <textarea id="msgAll" rows="6"
                                    class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">Halo {name}, akan ada maintenance jaringan pada tanggal XX jam XX.</textarea>
                            </div>
                            <button onclick="prepareBroadcast('all')"
                                class="w-full inline-flex justify-center items-center rounded-lg bg-blue-600 px-4 py-3 text-sm font-bold text-white shadow-sm hover:bg-blue-700 hover:shadow-md transition-all">
                                <i class="fas fa-bullhorn mr-2 text-lg"></i> Mulai Broadcast Pengumuman
                            </button>
                        </div>
                    </div>

                    <!-- Tab: Quick Test -->
                    <div x-show="activeTab === 'test'" style="display: none;">
                        <div class="max-w-md mx-auto mt-6">
                            <div class="bg-slate-50 p-6 rounded-xl border border-slate-200">
                                <h4 class="text-sm font-bold text-slate-700 mb-4 uppercase tracking-wider">Test Koneksi API
                                </h4>
                                <form action="{{ route('whatsapp.test') }}" method="POST">
                                    @csrf
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 mb-1">Nomor Tujuan</label>
                                            <input type="text" name="target"
                                                class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                                placeholder="081234xxx" required>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 mb-1">Pesan Test</label>
                                            <textarea name="message" rows="3"
                                                class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                                required>Ini adalah pesan test dari MikBill.</textarea>
                                        </div>
                                        <button type="submit"
                                            class="w-full inline-flex justify-center items-center rounded-lg bg-slate-800 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-slate-700 transition-all">
                                            <i class="fas fa-rocket mr-2"></i> Kirim Test
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: API Key (For Developers) -->
                    <div x-show="activeTab === 'api'" style="display: none;">
                        <div class="bg-slate-900 text-slate-300 p-6 rounded-xl font-mono text-sm relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-4 opacity-10"><i class="fas fa-code fa-5x"></i></div>
                            <p class="mb-4 text-slate-400">Gunakan API Key ini untuk mengintegrasikan pengiriman WhatsApp
                                dari aplikasi pihak ketiga.</p>

                            <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Your API
                                Key</label>
                            <div class="flex gap-2 mb-6">
                                <input type="text" id="apiKeyField"
                                    class="block w-full bg-slate-800 border-0 rounded-md text-slate-200 py-2 px-3 text-sm font-mono"
                                    readonly value="{{ auth()->user()->api_token ?? 'Belum ada API Key' }}">
                                <button onclick="copyApiKey()"
                                    class="bg-slate-700 hover:bg-slate-600 text-white px-3 py-2 rounded-md"><i
                                        class="fas fa-copy"></i></button>
                            </div>

                            <div class="flex gap-4">
                                <form action="{{ route('whatsapp.apikey') }}" method="POST" class="w-1/2"
                                    onsubmit="return confirm('Generate key baru? Key lama akan hangus.');">
                                    @csrf
                                    <button type="submit"
                                        class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2 rounded-lg text-sm">Generate
                                        New Key</button>
                                </form>
                                <a href="{{ asset('docs/api.html') }}" target="_blank"
                                    class="w-1/2 block text-center bg-slate-700 hover:bg-slate-600 text-white font-bold py-2 rounded-lg text-sm">Dokumentasi
                                    API</a>
                            </div>
                        </div>
                    </div>

                    <!-- Monitor Area (Broadcast Progress) -->
                    <div id="monitorArea" class="mt-8 border-t border-slate-200 pt-6" style="display: none;">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Proses Broadcast</h4>
                            <div class="text-xs font-mono">
                                <span class="text-green-600 font-bold px-2 py-1 bg-green-50 rounded">OK: <span
                                        id="statSuccess">0</span></span>
                                <span class="text-red-600 font-bold px-2 py-1 bg-red-50 rounded ml-2">Fail: <span
                                        id="statFail">0</span></span>
                            </div>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2.5 mb-4 overflow-hidden">
                            <div id="progressBar" class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300"
                                style="width: 0%"></div>
                        </div>
                        <div id="logList"
                            class="bg-slate-900 rounded-lg p-4 h-48 overflow-y-auto font-mono text-xs text-slate-300 space-y-1">
                            <!-- Logs here -->
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Select2 Custom Tailwind-ish */
        .select2-container .select2-selection--multiple {
            min-height: 38px;
            border-color: #d1d5db;
            border-radius: 0.375rem;
        }

        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #4f46e5;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
        }
    </style>
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#multiUserSelect').select2({ placeholder: "Cari pelanggan...", allowClear: true, width: '100%' });
        });

        // Broadcast Logic
        var queue = [], total = 0, current = 0, successCount = 0, failCount = 0, messageToSend = "";

        function prepareBroadcast(type) {
            messageToSend = type === 'unpaid' ? $('#msgUnpaid').val() : $('#msgAll').val();
            if (!messageToSend.trim()) { alert("Pesan tidak boleh kosong!"); return; }
            if (!confirm("Mulai broadcast " + type.toUpperCase() + "?")) return;

            $('#monitorArea').slideDown();
            $('#logList').html('<div class="text-center text-slate-500 italic">Mengambil data target...</div>');
            $('#progressBar').css('width', '0%');
            $('#statSuccess').text('0'); $('#statFail').text('0');

            $.get("{{ route('whatsapp.broadcast.targets') }}", { type: type }, function (response) {
                queue = response; total = queue.length;
                if (total === 0) { $('#logList').html('<div class="text-amber-400 text-center">Tidak ada target ditemukan.</div>'); return; }
                $('#logList').html('');
                current = 0; successCount = 0; failCount = 0;
                $('button').prop('disabled', true);
                processQueue();
            }).fail(function () {
                $('#logList').html('<div class="text-red-400 text-center">Gagal mengambil data target.</div>');
            });
        }

        function processQueue() {
            if (current >= total) {
                $('button').prop('disabled', false);
                $('#logList').prepend('<div class="text-center text-green-400 font-bold border-t border-slate-700 pt-2 mt-2">--- SELESAI ---</div>');
                alert("Broadcast Selesai! Sukses: " + successCount + ", Gagal: " + failCount);
                return;
            }
            let target = queue[current];
            let percent = Math.round(((current + 1) / total) * 100);
            $('#progressBar').css('width', percent + '%');

            $.post("{{ route('whatsapp.broadcast.process') }}", {
                _token: $('meta[name="csrf-token"]').attr('content'),
                id: target.id, message: messageToSend
            }).done(function (res) {
                if (res.status) { successCount++; $('#statSuccess').text(successCount); appendLog(target.name, true, 'Terkirim'); }
                else { failCount++; $('#statFail').text(failCount); appendLog(target.name, false, res.message || 'Error'); }
            }).fail(function () {
                failCount++; $('#statFail').text(failCount); appendLog(target.name, false, 'Server Error');
            }).always(function () { current++; processQueue(); });
        }

        function appendLog(name, status, msg) {
            let color = status ? 'text-green-400' : 'text-red-400';
            let icon = status ? 'check' : 'times';
            let html = `<div class="flex justify-between hover:bg-slate-800 p-1 rounded"><span><i class="fas fa-${icon} ${color} w-5"></i> ${name}</span><span class="${color}">${msg}</span></div>`;
            $('#logList').prepend(html);
        }

        function copyApiKey() {
            var el = document.getElementById("apiKeyField");
            el.select(); el.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(el.value);
            alert("API Key tersalin!");
        }
    </script>
@endpush