@extends('layouts.app2')

@section('title', 'WhatsApp Gateway')
@section('header', 'WhatsApp Gateway')
@section('subheader', 'Konfigurasi API dan Broadcast Pesan.')

@section('content')

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

        <!-- Left Column: API Config -->
        <div class="xl:col-span-1">

            @if(Auth::user()->role === 'admin' && $globalAdsense && $globalAdsense->adsense_content)
                <!-- Global Adsense Banner (Top Blinking) -->
                <div class="mb-6 animate-blink-fast">
                    <a href="{{ $globalAdsense->adsense_url ?? '#' }}" target="_blank"
                        class="group relative flex flex-col overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-green-600 p-5 shadow-xl border border-white/20 transition-all hover:scale-[1.02] active:scale-95">
                        <div class="flex items-center gap-4 mb-3">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 text-white shadow-inner ring-1 ring-white/30 backdrop-blur-sm">
                                <i class="fab fa-whatsapp text-2xl"></i>
                            </div>
                            <div>
                                <h4 class="text-md font-black text-white leading-tight tracking-tight text-center">
                                    {{ $globalAdsense->adsense_content }}
                                </h4>
                                <p class="text-xs font-bold text-emerald-100 uppercase tracking-widest opacity-80 text-center">
                                    Premium Service</p>
                            </div>
                        </div>
                        <div
                            class="flex items-center justify-center w-full rounded-xl bg-white/20 py-2.5 text-xs font-black text-white backdrop-blur-md ring-1 ring-white/30 group-hover:bg-white/40 transition-all">
                            CONNECT NOW <i class="fas fa-bolt ml-2 animate-bounce"></i>
                        </div>

                        <!-- Glow effect -->
                        <div class="absolute -top-10 -right-10 h-32 w-32 rounded-full bg-white/20 blur-2xl"></div>
                    </a>
                </div>
            @endif

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
                                <label class="block text-sm font-bold text-slate-900 mb-1">Nomor Pengirim (Wajib)</label>
                                <input type="text" name="sender_number"
                                    class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                    placeholder="628xxx" value="{{ optional($setting)->sender_number ?? '' }}">
                            </div>

                            @if(Auth::user()->role === 'superadmin')
                                <div class="pt-4 border-t border-slate-100">
                                    <label class="block text-sm font-bold text-indigo-600 mb-1 italic">Adsense Content
                                        (Global)</label>
                                    <textarea name="adsense_content" rows="2"
                                        class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                        placeholder="Teks untuk banner iklan...">{{ optional($setting)->adsense_content ?? '' }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-indigo-600 mb-1 italic">Adsense Link URL</label>
                                    <input type="url" name="adsense_url"
                                        class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                        placeholder="https://..." value="{{ optional($setting)->adsense_url ?? '' }}">
                                </div>
                            @endif

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
                        <button @click="activeTab = 'queue'"
                            :class="activeTab === 'queue' ? 'border-indigo-500 text-indigo-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                            class="whitespace-nowrap border-b-2 py-4 px-6 text-sm font-bold flex items-center transition-colors">
                            <i class="fas fa-list-ol mr-2"></i> Antrean Jadwal
                            @if(count($scheduledMessages) > 0)
                                <span
                                    class="ml-2 inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700">
                                    {{ count($scheduledMessages) }}
                                </span>
                            @endif
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
                                @if(auth()->user()->role == 'superadmin')
                                <div>
                                    <label class="block text-sm font-bold text-slate-900 mb-1">Filter Berdasarkan Admin</label>
                                    <select id="multiAdminFilter"
                                        class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                        <option value="">Semua Admin</option>
                                        @foreach($admins as $admin)
                                            <option value="{{ $admin->id }}" {{ $selectedAdminId == $admin->id ? 'selected' : '' }}>
                                                {{ $admin->name }}{{ $admin->id == auth()->id() ? ' (Self)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
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
                            @if(auth()->user()->role == 'superadmin')
                            <div>
                                <label class="block text-sm font-bold text-slate-900 mb-1">Filter Berdasarkan Admin</label>
                                <select id="unpaidAdminFilter"
                                    class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                    <option value="">Semua Admin</option>
                                    @foreach($admins as $admin)
                                        <option value="{{ $admin->id }}" {{ $selectedAdminId == $admin->id ? 'selected' : '' }}>
                                            {{ $admin->name }}{{ $admin->id == auth()->id() ? ' (Self)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
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

                    <!-- Tab: All Broadcast (Enhanced) -->
                    <div id="broadcastTab" x-show="activeTab === 'broadcast'" style="display: none;" x-data="{
                                                            selectionMode: 'all',
                                                            whatsappAge: '12+',
                                                            scheduleMode: 'now',
                                                            selectedCustomers: [],
                                                            maxRecipients: 9999,
                                                            scheduledAt: '',
                                                            getMaxRecipients() {
                                                                if (this.whatsappAge === '1-6') return 15;
                                                                if (this.whatsappAge === '6-12') return 50;
                                                                return 9999;
                                                            },
                                                            updateLimit() {
                                                                this.maxRecipients = this.getMaxRecipients();
                                                                // Truncate selection if exceeds limit
                                                                if (this.selectedCustomers.length > this.maxRecipients) {
                                                                    this.selectedCustomers = this.selectedCustomers.slice(0, this.maxRecipients);
                                                                    $('#broadcastCustomerSelect').val(this.selectedCustomers).trigger('change');
                                                                }
                                                            }
                                                        }" x-init="updateLimit()">

                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded-r-lg">
                            <div class="flex">
                                <div class="flex-shrink-0"><i class="fas fa-info-circle text-blue-400"></i></div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">Kirim informasi / pengumuman ke pelanggan dengan opsi
                                        pilihan pelanggan dan penjadwalan.</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-5">
                            <!-- Customer Selection Mode -->
                            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                                <label class="block text-sm font-bold text-slate-900 mb-3">
                                    <i class="fas fa-users mr-2 text-indigo-500"></i>Pilih Penerima
                                </label>
                                <div class="flex gap-4 mb-4">
                                    <label class="flex items-center cursor-pointer group">
                                        <input type="radio" x-model="selectionMode" value="all"
                                            class="w-4 h-4 text-indigo-600 border-slate-300 focus:ring-indigo-500">
                                        <span class="ml-2 text-sm font-medium text-slate-700 group-hover:text-indigo-600">
                                            Semua Pelanggan
                                        </span>
                                    </label>
                                    <label class="flex items-center cursor-pointer group">
                                        <input type="radio" x-model="selectionMode" value="custom"
                                            class="w-4 h-4 text-indigo-600 border-slate-300 focus:ring-indigo-500">
                                        <span class="ml-2 text-sm font-medium text-slate-700 group-hover:text-indigo-600">
                                            Pilih Pelanggan
                                        </span>
                                    </label>
                                </div>

                                <!-- Custom Customer Selection -->
                                <div x-show="selectionMode === 'custom'" x-transition class="mt-3">
                                    <select id="broadcastCustomerSelect" multiple="multiple"
                                        class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                        @foreach($customers as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone }})</option>
                                        @endforeach
                                    </select>
                                    <div class="mt-2 flex items-center justify-between">
                                        <span class="text-xs text-slate-500">
                                            Terpilih: <span class="font-bold text-indigo-600"
                                                x-text="selectedCustomers.length"></span> /
                                            <span x-text="maxRecipients === 9999 ? 'Unlimited' : maxRecipients"></span>
                                        </span>
                                        <button type="button"
                                            @click="selectedCustomers = []; $('#broadcastCustomerSelect').val([]).trigger('change')"
                                            class="text-xs text-red-500 hover:text-red-700 font-medium">
                                            <i class="fas fa-times mr-1"></i>Reset
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- WhatsApp Age Selection -->
                            <div
                                class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-4 border border-green-200">
                                <label class="block text-sm font-bold text-slate-900 mb-3">
                                    <i class="fab fa-whatsapp mr-2 text-green-500"></i>Usia Nomor WhatsApp
                                </label>
                                <select x-model="whatsappAge" @change="updateLimit()"
                                    class="block w-full rounded-lg border-0 py-2.5 px-3 text-slate-900 shadow-sm ring-1 ring-inset ring-green-300 focus:ring-2 focus:ring-inset focus:ring-green-500 sm:text-sm font-medium bg-white">
                                    <option value="1-6">🆕 1-6 Bulan (Max 15 penerima)</option>
                                    <option value="6-12">📅 6-12 Bulan (Max 50 penerima)</option>
                                    <option value="12+">✅ 12+ Bulan (Unlimited)</option>
                                </select>
                                <p class="mt-2 text-xs text-green-700">
                                    <i class="fas fa-shield-alt mr-1"></i>
                                    Batasan untuk mencegah blokir WhatsApp pada nomor baru.
                                </p>
                            </div>

                            <!-- Schedule Options -->
                            <div
                                class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-xl p-4 border border-purple-200">
                                <label class="block text-sm font-bold text-slate-900 mb-3">
                                    <i class="fas fa-clock mr-2 text-purple-500"></i>Waktu Pengiriman
                                </label>
                                <div class="flex gap-4 mb-4">
                                    <label class="flex items-center cursor-pointer group">
                                        <input type="radio" x-model="scheduleMode" value="now"
                                            class="w-4 h-4 text-purple-600 border-slate-300 focus:ring-purple-500">
                                        <span class="ml-2 text-sm font-medium text-slate-700 group-hover:text-purple-600">
                                            <i class="fas fa-bolt text-yellow-500 mr-1"></i>Kirim Sekarang
                                        </span>
                                    </label>
                                    <label class="flex items-center cursor-pointer group">
                                        <input type="radio" x-model="scheduleMode" value="scheduled"
                                            class="w-4 h-4 text-purple-600 border-slate-300 focus:ring-purple-500">
                                        <span class="ml-2 text-sm font-medium text-slate-700 group-hover:text-purple-600">
                                            <i class="fas fa-calendar-alt text-purple-500 mr-1"></i>Jadwalkan
                                        </span>
                                    </label>
                                </div>

                                <!-- DateTime Picker -->
                                <div x-show="scheduleMode === 'scheduled'" x-transition class="mt-3">
                                    <input type="datetime-local" x-model="scheduledAt" id="scheduledAtInput"
                                        class="block w-full rounded-lg border-0 py-2.5 px-3 text-slate-900 shadow-sm ring-1 ring-inset ring-purple-300 focus:ring-2 focus:ring-inset focus:ring-purple-500 sm:text-sm font-medium bg-white">
                                    <p class="mt-2 text-xs text-purple-700">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Pesan akan dikirim otomatis pada waktu yang ditentukan.
                                    </p>
                                </div>
                            </div>

                            <!-- Message Content -->
                            <div>
                                <label class="block text-sm font-bold text-slate-900 mb-1">
                                    <i class="fas fa-edit mr-2 text-slate-500"></i>Isi Pengumuman
                                </label>
                                <div class="relative">
                                    <textarea id="msgAll" rows="5"
                                        class="block w-full rounded-lg border-0 py-2 px-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                        placeholder="Ketik pesan Anda disini...">Halo {name}, akan ada maintenance jaringan pada tanggal XX jam XX.</textarea>
                                    <div
                                        class="absolute bottom-2 right-2 text-xs text-slate-400 bg-white px-2 rounded border border-slate-100 shadow-sm">
                                        Gunakan {name} untuk nama, {tagihan} untuk tagihan
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="button" onclick="startEnhancedBroadcast()"
                                class="w-full inline-flex justify-center items-center rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-4 text-sm font-bold text-white shadow-lg hover:from-blue-700 hover:to-indigo-700 hover:shadow-xl transition-all transform hover:scale-[1.02] active:scale-100">
                                <i class="fas fa-bullhorn mr-2 text-lg"></i>
                                <span
                                    x-text="scheduleMode === 'now' ? 'Mulai Broadcast Sekarang' : 'Jadwalkan Broadcast'"></span>
                            </button>
                        </div>
                    </div>


                    <!-- Tab: Queue -->
                    <div x-show="activeTab === 'queue'" style="display: none;">
                        <div class="overflow-hidden bg-white shadow sm:rounded-lg border border-slate-200 mt-4">
                            <table class="min-w-full divide-y border-collapse divide-slate-300">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th scope="col"
                                            class="py-3.5 pl-4 pr-3 text-left text-sm font-bold text-slate-900 sm:pl-6 uppercase tracking-wider">
                                            Jadwal Kirim</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-left text-sm font-bold text-slate-900 uppercase tracking-wider">
                                            Penerima</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-left text-sm font-bold text-slate-900 uppercase tracking-wider">
                                            Isi Pesan</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-left text-sm font-bold text-slate-900 uppercase tracking-wider">
                                            Status</th>
                                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                            <span class="sr-only">Aksi</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 bg-white">
                                    @forelse($scheduledMessages as $msg)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td
                                                class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-slate-900 sm:pl-6 font-medium">
                                                @if($msg->status === 'pending' && $msg->scheduled_at)
                                                    <div x-data="{ 
                                                                    target: new Date('{{ $msg->scheduled_at->toIso8601String() }}').getTime(),
                                                                    now: new Date().getTime(),
                                                                    countdown: '',
                                                                    update() {
                                                                        let diff = this.target - this.now;
                                                                        if (diff <= 0) {
                                                                            this.countdown = 'Sesaat lagi...';
                                                                            return;
                                                                        }
                                                                        let d = Math.floor(diff / (1000 * 60 * 60 * 24));
                                                                        let h = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                                                        let m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                                                                        let s = Math.floor((diff % (1000 * 60)) / 1000);
                                                                        this.countdown = (d > 0 ? d + 'h ' : '') + h + 'j ' + m + 'm ' + s + 's';
                                                                    }
                                                                }"
                                                        x-init="update(); setInterval(() => { now = new Date().getTime(); update() }, 1000)">
                                                        <div class="font-bold text-slate-900">
                                                            {{ $msg->scheduled_at->format('d M Y H:i') }}</div>
                                                        <div class="text-[10px] text-indigo-600 font-mono" x-text="countdown"></div>
                                                    </div>
                                                @else
                                                    <div class="font-bold text-slate-900">
                                                        {{ $msg->scheduled_at ? $msg->scheduled_at->format('d M Y H:i') : 'Sekarang' }}
                                                    </div>
                                                    <div class="text-[10px] text-slate-400 capitalize">
                                                        {{ $msg->created_at->diffForHumans() }}</div>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500">
                                                <div class="flex flex-col">
                                                    <span
                                                        class="inline-flex items-center w-fit rounded-md bg-indigo-50 px-2 py-1 text-xs font-bold text-indigo-700 ring-1 ring-inset ring-indigo-700/10">
                                                        <i class="fas fa-users mr-1"></i> {{ $msg->total_count }} Nomor
                                                    </span>
                                                    <span class="text-[10px] text-slate-400 mt-1 font-medium">Age:
                                                        <span class="text-indigo-600">{{ $msg->whatsapp_age }}</span></span>
                                                </div>
                                            </td>
                                            <td class="px-3 py-4 text-sm text-slate-500">
                                                <div class="max-w-xs break-words line-clamp-2" title="{{ $msg->message }}">
                                                    {{ $msg->message }}
                                                </div>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                                @if($msg->status === 'pending')
                                                    <span
                                                        class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-bold text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                                        <span class="mr-1.5 flex h-2 w-2 items-center justify-center">
                                                            <span
                                                                class="absolute inline-flex h-2 w-2 animate-ping rounded-full bg-amber-400 opacity-75"></span>
                                                            <span
                                                                class="relative inline-flex h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                        </span>
                                                        Menunggu
                                                    </span>
                                                @elseif($msg->status === 'processing')
                                                    <span
                                                        class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-bold text-indigo-700 ring-1 ring-inset ring-indigo-700/10">
                                                        <i class="fas fa-spinner fa-spin mr-1.5"></i>
                                                        Diproses
                                                    </span>
                                                @elseif($msg->status === 'completed')
                                                    <div class="flex flex-col gap-1">
                                                        <span
                                                            class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-bold text-green-700 ring-1 ring-inset ring-green-600/20">
                                                            <i class="fas fa-check-circle mr-1.5"></i> Selesai
                                                        </span>
                                                        <div class="flex gap-2 text-[10px]">
                                                            <span class="text-green-600 font-bold"><i class="fas fa-check"></i>
                                                                {{ $msg->success_count }}</span>
                                                            <span class="text-red-500 font-bold"><i class="fas fa-times"></i>
                                                                {{ $msg->failed_count }}</span>
                                                        </div>
                                                    </div>
                                                @elseif($msg->status === 'failed')
                                                    <div class="flex flex-col gap-1">
                                                        <span
                                                            class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-bold text-red-700 ring-1 ring-inset ring-red-600/20">
                                                            <i class="fas fa-exclamation-circle mr-1.5"></i> Gagal
                                                        </span>
                                                        <span class="text-[10px] text-red-500 font-bold"><i
                                                                class="fas fa-times"></i> {{ $msg->failed_count }} Gagal</span>
                                                    </div>
                                                @endif
                                            </td>
                                            <td
                                                class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                                <button onclick="deleteScheduled({{ $msg->id }})"
                                                    class="inline-flex items-center rounded-lg bg-red-50 p-2 text-red-600 hover:bg-red-100 hover:text-red-700 transition-all border border-red-100">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-12 text-center">
                                                <div class="flex flex-col items-center justify-center">
                                                    <div
                                                        class="inline-flex items-center justify-center h-12 w-12 rounded-full bg-slate-100 text-slate-400 mb-3">
                                                        <i class="fas fa-calendar-times text-xl"></i>
                                                    </div>
                                                    <p class="text-sm font-medium text-slate-500 italic">Tidak ada antrean
                                                        jadwal pesan.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab: Quick Test -->
                    <div x-show="activeTab === 'test'" style="display: none;">
                        <div class="max-w-md mx-auto mt-6">
                            <div class="bg-slate-50 p-6 rounded-xl border border-slate-200">
                                <h4 class="text-sm font-bold text-slate-700 mb-4 uppercase tracking-wider">
                                    Test Koneksi API
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

        @keyframes blink-animation {
            0% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 0.85;
                transform: scale(0.98);
            }

            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        .animate-blink-fast {
            animation: blink-animation 1.5s ease-in-out infinite;
        }

        /* Navy color for select options */
        #multiUserSelect option,
        .select2-results__option,
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            color: #000080 !important;
            font-weight: 600;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Global broadcast variables
        var queue = [], total = 0, current = 0, successCount = 0, failCount = 0, messageToSend = "";
        var scheduledMessageId = null;

        $(document).ready(function () {
<<<<<<< HEAD
            // Initialize Select2 for Multi-Send tab
            $('#multiUserSelect').select2({
                placeholder: "Cari pelanggan...",
                allowClear: true,
                width: '100%'
            });

            // Initialize Select2 for Broadcast Customer Select
            $('#broadcastCustomerSelect').select2({
                placeholder: "Pilih pelanggan...",
                allowClear: true,
                width: '100%',
                maximumSelectionLength: 9999 // Will be dynamically updated
            });

            // Sync Select2 selections with Alpine.js
            $('#broadcastCustomerSelect').on('change', function () {
                const selectedValues = $(this).val() || [];
                const alpineComponent = document.getElementById('broadcastTab');
                if (alpineComponent) {
                    const data = Alpine.$data(alpineComponent);
                    const maxAllowed = data.maxRecipients;

                    // Enforce limit
                    if (selectedValues.length > maxAllowed) {
                        const trimmed = selectedValues.slice(0, maxAllowed);
                        $(this).val(trimmed).trigger('change.select2');
                        data.selectedCustomers = trimmed;

                        Swal.fire({
                            icon: 'warning',
                            title: 'Batas Tercapai',
                            text: `Maksimal ${maxAllowed} penerima untuk usia WhatsApp yang dipilih.`,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    } else {
                        data.selectedCustomers = selectedValues;
                    }
                }
            });

            // Set minimum datetime for schedule input
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            const minDateTime = now.toISOString().slice(0, 16);
            $('#scheduledAtInput').attr('min', minDateTime);
=======
            $('#multiUserSelect').select2({ placeholder: "Cari pelanggan...", allowClear: true, width: '100%' });

            // Update Multi-Send targets when Admin filter changes
            $('#multiAdminFilter').on('change', function() {
                const adminId = $(this).val();
                const userSelect = $('#multiUserSelect');
                
                userSelect.prop('disabled', true);
                
                $.get("{{ route('whatsapp.broadcast.targets') }}", { type: 'all', admin_id: adminId }, function(response) {
                    userSelect.empty();
                    response.forEach(function(target) {
                        const option = new Option(target.name + ' (' + target.phone + ')', target.id, false, false);
                        userSelect.append(option);
                    });
                    userSelect.trigger('change');
                    userSelect.prop('disabled', false);
                }).fail(function() {
                    alert('Gagal mengambil data pelanggan.');
                    userSelect.prop('disabled', false);
                });
            });
>>>>>>> 0beb2daa2c0d1279b6d90c25e1a6928a9cd9fe3c
        });

        // Enhanced Broadcast Function
        function startEnhancedBroadcast() {
            const alpineComponent = document.getElementById('broadcastTab');
            if (!alpineComponent) {
                alert('Error: Component not found');
                return;
            }

            const data = Alpine.$data(alpineComponent);
            const message = $('#msgAll').val().trim();

            // Validations
            if (!message) {
                Swal.fire({
                    icon: 'error',
                    title: 'Pesan Kosong',
                    text: 'Silakan masukkan isi pesan terlebih dahulu.'
                });
                return;
            }

            if (data.selectionMode === 'custom' && data.selectedCustomers.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Pilih Pelanggan',
                    text: 'Silakan pilih minimal satu pelanggan.'
                });
                return;
            }

            if (data.scheduleMode === 'scheduled' && !data.scheduledAt) {
                Swal.fire({
                    icon: 'error',
                    title: 'Waktu Belum Dipilih',
                    text: 'Silakan pilih waktu penjadwalan.'
                });
                return;
            }

            // Confirm action
            const confirmTitle = data.scheduleMode === 'now' ? 'Kirim Broadcast Sekarang?' : 'Jadwalkan Broadcast?';
            const confirmText = data.selectionMode === 'all'
                ? `Ke semua pelanggan (max ${data.maxRecipients === 9999 ? 'unlimited' : data.maxRecipients})`
                : `Ke ${data.selectedCustomers.length} pelanggan terpilih`;

            Swal.fire({
                title: confirmTitle,
                text: confirmText,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#64748b',
                confirmButtonText: data.scheduleMode === 'now' ? 'Ya, Kirim Sekarang' : 'Ya, Jadwalkan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    processBroadcastRequest(data, message);
                }
            });
        }

        function processBroadcastRequest(data, message) {
            // Show loading
            Swal.fire({
                title: 'Memproses...',
                text: 'Menyiapkan broadcast',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            // Prepare request data
            const requestData = {
                _token: $('meta[name="csrf-token"]').attr('content'),
                message: message,
                selection_mode: data.selectionMode,
                whatsapp_age: data.whatsappAge,
                schedule_mode: data.scheduleMode,
                scheduled_at: data.scheduleMode === 'scheduled' ? data.scheduledAt : null,
                customer_ids: data.selectionMode === 'custom' ? data.selectedCustomers : null
            };

            $.post("{{ route('whatsapp.broadcast.schedule') }}", requestData)
                .done(function (response) {
                    Swal.close();

                    if (response.status) {
                        if (response.mode === 'immediate') {
                            // Start immediate broadcast
                            scheduledMessageId = response.scheduled_message_id;
                            queue = response.targets;
                            total = queue.length;
                            messageToSend = message;

                            if (total === 0) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Tidak Ada Target',
                                    text: 'Tidak ada pelanggan yang ditemukan.'
                                });
                                return;
                            }

                            // Show monitor area and start processing
                            $('#monitorArea').slideDown();
                            $('#logList').html('');
                            $('#progressBar').css('width', '0%');
                            $('#statSuccess').text('0');
                            $('#statFail').text('0');
                            current = 0;
                            successCount = 0;
                            failCount = 0;
                            $('button').prop('disabled', true);
                            processQueue();
                        } else {
                            // Scheduled for later
                            Swal.fire({
                                icon: 'success',
                                title: 'Broadcast Dijadwalkan!',
                                html: `
                                                                <p>Pesan akan dikirim pada:</p>
                                                                <p class="text-lg font-bold text-indigo-600">${response.scheduled_at}</p>
                                                                <p class="text-sm text-gray-500 mt-2">Total: ${response.total} penerima</p>
                                                            `,
                                confirmButtonColor: '#4f46e5'
                            }).then(() => {
                                location.reload();
                            });
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message || 'Terjadi kesalahan.'
                        });
                    }
                })
                .fail(function (xhr) {
                    Swal.close();
                    let errorMsg = 'Terjadi kesalahan server.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg
                    });
                });
        }

        // Original Broadcast Logic for Unpaid tab
        function prepareBroadcast(type) {
            messageToSend = type === 'unpaid' ? $('#msgUnpaid').val() : $('#msgAll').val();
<<<<<<< HEAD
            if (!messageToSend.trim()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Pesan Kosong',
                    text: 'Silakan masukkan isi pesan terlebih dahulu.'
                });
                return;
            }
=======
            let adminId = type === 'unpaid' ? $('#unpaidAdminFilter').val() : '';
            
            if (!messageToSend.trim()) { alert("Pesan tidak boleh kosong!"); return; }
            if (!confirm("Mulai broadcast " + type.toUpperCase() + "?")) return;
>>>>>>> 0beb2daa2c0d1279b6d90c25e1a6928a9cd9fe3c

            Swal.fire({
                title: 'Mulai Broadcast?',
                text: `Broadcast ${type.toUpperCase()} ke semua target`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Mulai',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    executeBroadcast(type);
                }
            });
        }

        function executeBroadcast(type) {
            $('#monitorArea').slideDown();
            $('#logList').html('<div class="text-center text-slate-500 italic">Mengambil data target...</div>');
            $('#progressBar').css('width', '0%');
            $('#statSuccess').text('0');
            $('#statFail').text('0');

<<<<<<< HEAD
            $.get("{{ route('whatsapp.broadcast.targets') }}", { type: type }, function (response) {
                queue = response.targets || response;
                total = queue.length;
                if (total === 0) {
                    $('#logList').html('<div class="text-amber-400 text-center">Tidak ada target ditemukan.</div>');
                    return;
                }
=======
            $.get("{{ route('whatsapp.broadcast.targets') }}", { type: type, admin_id: adminId }, function (response) {
                queue = response; total = queue.length;
                if (total === 0) { $('#logList').html('<div class="text-amber-400 text-center">Tidak ada target ditemukan.</div>'); return; }
>>>>>>> 0beb2daa2c0d1279b6d90c25e1a6928a9cd9fe3c
                $('#logList').html('');
                current = 0;
                successCount = 0;
                failCount = 0;
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

                // Update progress if we have a scheduled message ID
                if (scheduledMessageId) {
                    $.post("{{ route('whatsapp.broadcast.progress') }}", {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        scheduled_message_id: scheduledMessageId,
                        success_count: successCount,
                        failed_count: failCount,
                        status: 'completed'
                    });
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Broadcast Selesai!',
                    html: `<p>Sukses: <span class="text-green-600 font-bold">${successCount}</span></p>
                                                       <p>Gagal: <span class="text-red-600 font-bold">${failCount}</span></p>`,
                    confirmButtonColor: '#4f46e5'
                });

                scheduledMessageId = null;
                return;
            }

            let target = queue[current];
            let percent = Math.round(((current + 1) / total) * 100);
            $('#progressBar').css('width', percent + '%');

            $.post("{{ route('whatsapp.broadcast.process') }}", {
                _token: $('meta[name="csrf-token"]').attr('content'),
                id: target.id,
                message: messageToSend
            }).done(function (res) {
                if (res.status) {
                    successCount++;
                    $('#statSuccess').text(successCount);
                    appendLog(target.name, true, 'Terkirim');
                } else {
                    failCount++;
                    $('#statFail').text(failCount);
                    appendLog(target.name, false, res.message || 'Error');
                }
            }).fail(function () {
                failCount++;
                $('#statFail').text(failCount);
                appendLog(target.name, false, 'Server Error');
            }).always(function () {
                current++;
                processQueue();
            });
        }

        function appendLog(name, status, msg) {
            let color = status ? 'text-green-400' : 'text-red-400';
            let icon = status ? 'check' : 'times';
            let html = `<div class="flex justify-between hover:bg-slate-800 p-1 rounded"><span><i class="fas fa-${icon} ${color} w-5"></i> ${name}</span><span class="${color}">${msg}</span></div>`;
            $('#logList').prepend(html);
        }

        function deleteScheduled(id) {
            Swal.fire({
                title: 'Hapus Jadwal?',
                text: "Antrean pesan ini akan dibatalkan.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('/whatsapp/broadcast/schedule') }}/" + id,
                        type: 'DELETE',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            if (response.status) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Gagal', response.message, 'error');
                            }
                        },
                        error: function () {
                            Swal.fire('Error', 'Gagal menghapus jadwal pesan.', 'error');
                        }
                    });
                }
            });
        }
    </script>
@endpush