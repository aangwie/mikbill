<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan - {{ $month }}/{{ $year }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            @page { margin: 1cm; size: A4; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans text-gray-800 text-sm">

    <div class="max-w-[21cm] mx-auto bg-white p-10 min-h-screen shadow-lg print:shadow-none print:w-full print:max-w-none print:m-0 print:p-0">
        
        <div class="no-print flex gap-2 mb-6">
            <button onclick="window.print()" class="bg-indigo-600 text-white px-4 py-2 rounded shadow hover:bg-indigo-700 font-bold">Cetak / Simpan PDF</button>
            <button onclick="window.close()" class="bg-gray-500 text-white px-4 py-2 rounded shadow hover:bg-gray-600">Tutup</button>
        </div>

        @php
            switch ($reportType) {
                case 1: $judulLaporan = "LAPORAN RINCIAN PENDAPATAN & PENGELUARAN"; break;
                case 2: $judulLaporan = "LAPORAN REKAP PENDAPATAN & RINCIAN PENGELUARAN"; break;
                case 3: $judulLaporan = "LAPORAN RINCIAN PENDAPATAN & REKAP PENGELUARAN"; break;
                case 4: $judulLaporan = "LAPORAN REKAPITULASI KEUANGAN"; break;
                default: $judulLaporan = "LAPORAN LABA RUGI"; break;
            }
        @endphp

        <!-- Header -->
        <div class="flex justify-between items-start border-b-2 border-gray-800 pb-6 mb-8">
            <div class="w-2/3">
                @if($company && $company->logo_path)
                    <img src="{{ asset('uploads/' . $company->logo_path) }}" class="h-16 mb-2 object-contain">
                @else
                    <h1 class="text-2xl font-bold text-gray-900 uppercase tracking-wide">{{ $company->company_name ?? 'MIKBILL ISP' }}</h1>
                @endif
                <p class="text-gray-600 leading-snug">{{ $company->address ?? '' }}</p>
                <p class="text-gray-600">Telp: {{ $company->phone ?? '' }}</p>
            </div>
            <div class="w-1/3 text-right">
                <h2 class="text-lg font-bold text-gray-900">{{ $judulLaporan }}</h2>
                <p class="text-gray-600 mt-1">Periode: <span class="font-semibold text-gray-900">{{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}</span></p>
                <p class="text-xs text-gray-500 mt-2">Dicetak: {{ date('d/m/Y H:i') }}</p>
            </div>
        </div>

        <!-- Section A: Revenue -->
        <div class="mb-8">
            <h3 class="text-base font-bold text-emerald-700 border-b border-emerald-200 pb-1 mb-3 uppercase">A. Pemasukan (Omset)</h3>
            
            @if(in_array($reportType, [1, 3]))
                <table class="w-full border-collapse border border-gray-300">
                    <thead class="bg-emerald-50">
                        <tr>
                            <th class="border border-gray-300 p-2 text-left w-12">No</th>
                            <th class="border border-gray-300 p-2 text-left">Tanggal Bayar</th>
                            <th class="border border-gray-300 p-2 text-left">Pelanggan</th>
                            <th class="border border-gray-300 p-2 text-left">Paket / ID</th>
                            <th class="border border-gray-300 p-2 text-right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $index => $inv)
                        <tr class="odd:bg-white even:bg-gray-50">
                            <td class="border border-gray-300 p-2 text-center">{{ $index + 1 }}</td>
                            <td class="border border-gray-300 p-2">{{ $inv->updated_at->format('d/m/Y') }}</td>
                            <td class="border border-gray-300 p-2 font-medium">{{ $inv->customer->name }}</td>
                            <td class="border border-gray-300 p-2 text-gray-600">{{ $inv->customer->internet_number }}</td>
                            <td class="border border-gray-300 p-2 text-right font-medium">Rp {{ number_format($inv->customer->monthly_price, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr class="bg-emerald-100 font-bold">
                            <td colspan="4" class="border border-gray-300 p-2 text-right uppercase">Total Pemasukan</td>
                            <td class="border border-gray-300 p-2 text-right">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            @else
                <div class="bg-emerald-50 border border-emerald-200 rounded p-4 text-right">
                    <span class="font-bold text-emerald-800">TOTAL PEMASUKAN ({{ count($invoices) }} Transaksi) :</span> 
                    <span class="text-xl font-bold text-emerald-600 ml-2">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
                </div>
            @endif
        </div>

        <!-- Section B: Expenses -->
        <div class="mb-8">
            <h3 class="text-base font-bold text-rose-700 border-b border-rose-200 pb-1 mb-3 uppercase">B. Pengeluaran (Biaya)</h3>

            @if(in_array($reportType, [1, 2]))
                <table class="w-full border-collapse border border-gray-300">
                    <thead class="bg-rose-50">
                        <tr>
                            <th class="border border-gray-300 p-2 text-left w-12">No</th>
                            <th class="border border-gray-300 p-2 text-left">Tanggal</th>
                            <th class="border border-gray-300 p-2 text-left">Keterangan Pengeluaran</th>
                            <th class="border border-gray-300 p-2 text-right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $index => $exp)
                        <tr class="odd:bg-white even:bg-gray-50">
                            <td class="border border-gray-300 p-2 text-center">{{ $index + 1 }}</td>
                            <td class="border border-gray-300 p-2">{{ $exp->transaction_date->format('d/m/Y') }}</td>
                            <td class="border border-gray-300 p-2">{{ $exp->description }}</td>
                            <td class="border border-gray-300 p-2 text-right font-medium">Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="border border-gray-300 p-4 text-center text-gray-500 italic">Tidak ada data pengeluaran.</td></tr>
                        @endforelse
                        <tr class="bg-rose-100 font-bold">
                            <td colspan="3" class="border border-gray-300 p-2 text-right uppercase">Total Pengeluaran</td>
                            <td class="border border-gray-300 p-2 text-right">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            @else
                <div class="bg-rose-50 border border-rose-200 rounded p-4 text-right">
                    <span class="font-bold text-rose-800">TOTAL PENGELUARAN ({{ count($expenses) }} Transaksi) :</span> 
                    <span class="text-xl font-bold text-rose-600 ml-2">Rp {{ number_format($totalExpense, 0, ',', '.') }}</span>
                </div>
            @endif
        </div>

        <!-- Section C: Summary -->
        <div class="flex justify-end mb-12">
            <div class="w-1/2">
                <table class="w-full border border-gray-300 shadow-sm">
                    <tr>
                        <td class="p-3 bg-gray-50 font-bold text-gray-700">Total Pemasukan</td>
                        <td class="p-3 text-right font-bold text-emerald-600">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="p-3 bg-gray-50 font-bold text-gray-700">Total Pengeluaran</td>
                        <td class="p-3 text-right font-bold text-rose-600">( Rp {{ number_format($totalExpense, 0, ',', '.') }} )</td>
                    </tr>
                    <tr class="border-t-2 border-gray-800">
                        <td class="p-4 bg-gray-100 font-bold text-lg text-gray-900 uppercase">Laba Bersih</td>
                        <td class="p-4 bg-gray-100 text-right font-bold text-lg {{ $netProfit >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                            Rp {{ number_format($netProfit, 0, ',', '.') }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Signature -->
        <div class="flex justify-end">
            <div class="text-center w-64">
                <p class="mb-8">Mengetahui,</p>
                @if($company && $company->signature_path)
                    <div class="h-20 flex items-center justify-center mb-2">
                         <img src="{{ asset('uploads/' . $company->signature_path) }}" class="max-h-full object-contain">
                    </div>
                @else
                    <div class="h-20 mb-2"></div>
                @endif
                <p class="font-bold border-t border-gray-400 pt-2">{{ $company->owner_name ?? 'Administrator' }}</p>
                <p class="text-xs text-gray-500">Direktur / Owner</p>
            </div>
        </div>

    </div>

</body>
</html>