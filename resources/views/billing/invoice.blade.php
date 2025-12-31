<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #INV-{{ str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}</title>
     <!-- Favicon -->
    <link rel="icon" href="{{ $global_favicon ?? asset('favicon.ico') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            @page {
                margin: 0;
                size: auto;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body class="bg-gray-100 font-sans text-gray-800">

    <div
        class="max-w-3xl mx-auto my-10 bg-white shadow-lg p-10 print:shadow-none print:m-0 print:w-full print:max-w-full">

        <!-- Header -->
        <div class="flex justify-between items-start border-b border-gray-200 pb-8 mb-8">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    @if(!empty($company->logo_path))
                        <img src="{{ asset('uploads/' . $company->logo_path) }}" alt="Logo" class="h-10 w-auto rounded-lg">
                    @else
                        <div class="h-10 w-10 bg-indigo-600 rounded-lg flex items-center justify-center text-white font-bold text-xl">
                            {{ substr($company->company_name ?? 'M', 0, 1) }}
                        </div>
                    @endif
                    <h1 class="text-3xl font-bold text-gray-900 tracking-tight">{{ $company->company_name ?? 'MIKBILL' }}</h1>
                </div>
                <p class="text-sm text-gray-500">
                    {{ $company->address ?? 'Alamat Perusahaan belum diatur' }}<br>
                    {{ $company->phone ?? '' }}<br>
                    {{ $company->email ?? '' }}
                </p>
            </div>
            <div class="text-right">
                <h2 class="text-2xl font-bold text-gray-400 uppercase tracking-widest mb-1">Invoice</h2>
                <p class="text-lg font-semibold text-gray-900">#INV-{{ str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}</p>
                <div class="mt-4">
                    @if($invoice->status == 'paid')
                        <span
                            class="px-4 py-2 bg-green-100 text-green-700 font-bold rounded-lg border border-green-200">LUNAS</span>
                    @else
                        <span class="px-4 py-2 bg-red-100 text-red-700 font-bold rounded-lg border border-red-200">BELUM
                            BAYAR</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Bill To -->
        <div class="flex justify-between mb-8">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Tagihan Kepada:</p>
                <p class="text-lg font-bold text-gray-900">{{ $invoice->customer->name }}</p>
                <p class="text-sm text-gray-600">{{ $invoice->customer->address ?? 'Alamat tidak tersedia' }}</p>
                <p class="text-sm text-gray-600 mt-1"><span class="font-semibold">ID:</span>
                    {{ $invoice->customer->internet_number }}</p>
            </div>
            <div class="text-right">
                <div class="mb-2">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Tanggal Invoice:</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $invoice->created_at->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Jatuh Tempo:</p>
                    <p class="text-sm font-semibold text-gray-900">
                        {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Table -->
        <table class="w-full mb-8">
            <thead>
                <tr class="bg-gray-50 border-y border-gray-200">
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Deskripsi</th>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Periode
                    </th>
                    <th class="py-3 px-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Jumlah
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="py-4 px-4 border-b border-gray-100">
                        <p class="text-sm font-bold text-gray-900">Paket Internet Bulanan</p>
                        <p class="text-xs text-gray-500">{{ $invoice->customer->profile ?? 'Default Profile' }}</p>
                    </td>
                    <td class="py-4 px-4 border-b border-gray-100 text-sm text-gray-600">
                        {{ \Carbon\Carbon::parse($invoice->due_date)->isoFormat('MMMM Y') }}
                    </td>
                    <td class="py-4 px-4 border-b border-gray-100 text-right font-bold text-gray-900">
                        @php
                            $displayPrice = $invoice->price > 0 ? $invoice->price : ($invoice->customer->monthly_price ?? 0);
                        @endphp
                        Rp {{ number_format($displayPrice, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="py-4 text-right text-sm font-semibold text-gray-500">Total Tagihan</td>
                    <td class="py-4 text-right text-xl font-bold text-indigo-600">Rp
                        {{ number_format($displayPrice, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Footer -->
        <div class="border-t border-gray-200 pt-8 flex justify-between items-center">
            <div class="text-sm text-gray-500">
                <p>Terima kasih atas kepercayaan Anda.</p>
                <p>Harap melakukan pembayaran sebelum tanggal jatuh tempo.</p>
            </div>
            <div class="print:hidden">
                <button onclick="window.print()"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700 transition font-medium">
                    <i class="fas fa-print mr-2"></i> Cetak
                </button>
            </div>
        </div>
    </div>

</body>

</html>