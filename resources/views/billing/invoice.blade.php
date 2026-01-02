<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #INV-{{ str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}</title>
    <!-- Favicon -->
    <link rel="icon" href="{{ $global_favicon ?? asset('favicon.ico') }}">
    
    @if(!isset($isPdf))
    <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .invoice-box {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        @if(isset($isPdf))
        .invoice-box {
            margin: 0;
            box-shadow: none;
            max-width: 100%;
        }
        @endif

        table {
            width: 100%;
            line-height: inherit;
            text-align: left;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
        }

        .company-info .name {
            font-size: 28px;
            font-weight: bold;
            color: #1a1a1a;
            margin: 0;
        }

        .company-info .details {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }

        .invoice-title {
            text-align: right;
        }

        .invoice-title h2 {
            font-size: 24px;
            color: #ccc;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0;
        }

        .invoice-number {
            font-size: 18px;
            font-weight: bold;
            margin-top: 5px;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 12px;
            margin-top: 10px;
        }

        .status-paid {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .status-unpaid {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .details-table {
            margin-top: 40px;
        }

        .details-table td {
            padding-bottom: 20px;
        }

        .section-label {
            font-size: 11px;
            font-weight: bold;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .customer-name {
            font-size: 16px;
            font-weight: bold;
        }

        .customer-details {
            font-size: 13px;
            color: #555;
        }

        .items-table {
            margin-top: 30px;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
        }

        .items-table th {
            background: #fbfbfb;
            padding: 12px;
            font-size: 11px;
            color: #888;
            text-transform: uppercase;
        }

        .items-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #f9f9f9;
        }

        .item-desc {
            font-size: 14px;
            font-weight: bold;
        }

        .item-subtext {
            font-size: 11px;
            color: #999;
        }

        .total-row td {
            padding: 20px 12px;
            text-align: right;
        }

        .total-label {
            font-size: 14px;
            font-weight: bold;
            color: #888;
        }

        .total-amount {
            font-size: 22px;
            font-weight: bold;
            color: #4f46e5;
        }

        .footer {
            margin-top: 40px;
            border-top: 1px solid #eee;
            padding-top: 20px;
            font-size: 12px;
            color: #999;
        }

        .btn-print {
            background-color: #4f46e5;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            border: none;
            cursor: pointer;
        }

        @media print {
            .no-print {
                display: none;
            }
            .invoice-box {
                margin: 0;
                box-shadow: none;
            }
        }
    </style>
</head>

<body>

    <div class="invoice-box">
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td>
                    <table style="width: auto;">
                        <tr>
                            @if(isset($logoBase64))
                                <td><img src="{{ $logoBase64 }}" style="height: 45px; width: auto; margin-right: 15px; border-radius: 6px;"></td>
                            @elseif(!empty($company->logo_path))
                                <td><img src="{{ asset('uploads/' . $company->logo_path) }}" style="height: 45px; width: auto; margin-right: 15px; border-radius: 6px;"></td>
                            @else
                                <td>
                                    <div style="height: 40px; width: 40px; background-color: #4f46e5; color: white; border-radius: 8px; text-align: center; line-height: 40px; font-weight: bold; font-size: 20px; margin-right: 10px;">
                                        {{ substr($company->company_name ?? 'M', 0, 1) }}
                                    </div>
                                </td>
                            @endif
                            <td class="company-info">
                                <p class="name">{{ $company->company_name ?? 'MIKBILL' }}</p>
                            </td>
                        </tr>
                    </table>
                    <div class="company-info">
                        <p class="details">
                            {{ $company->address ?? 'Alamat Perusahaan belum diatur' }}<br>
                            {{ $company->phone ?? '' }} | {{ $company->email ?? '' }}
                        </p>
                    </div>
                </td>
                <td class="invoice-title">
                    <h2>Invoice</h2>
                    <p class="invoice-number">#INV-{{ str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}</p>
                    <div class="status-badge {{ $invoice->status == 'paid' ? 'status-paid' : 'status-unpaid' }}">
                        {{ $invoice->status == 'paid' ? 'LUNAS' : 'BELUM BAYAR' }}
                    </div>
                </td>
            </tr>
        </table>

        <!-- Details -->
        <table class="details-table">
            <tr>
                <td style="width: 60%;">
                    <p class="section-label">Tagihan Kepada:</p>
                    <p class="customer-name">{{ $invoice->customer->name }}</p>
                    <p class="customer-details">
                        {{ $invoice->customer->address ?? 'Alamat tidak tersedia' }}<br>
                        <strong>ID:</strong> {{ $invoice->customer->internet_number }}
                    </p>
                </td>
                <td style="text-align: right;">
                    <p class="section-label">Tanggal Invoice:</p>
                    <p style="font-size: 14px; font-weight: bold; margin-bottom: 10px;">{{ $invoice->created_at->format('d/m/Y') }}</p>
                    
                    <p class="section-label">Jatuh Tempo:</p>
                    <p style="font-size: 14px; font-weight: bold; {{ $invoice->status != 'paid' && now() > $invoice->due_date ? 'color: #b91c1c;' : '' }}">
                        {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}
                    </p>
                </td>
            </tr>
        </table>

        <!-- Items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Deskripsi</th>
                    <th>Periode</th>
                    <th style="text-align: right;">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <p class="item-desc">Paket Internet Bulanan</p>
                        <p class="item-subtext">{{ $invoice->customer->profile ?? 'Default Profile' }}</p>
                    </td>
                    <td style="font-size: 14px;">
                        {{ \Carbon\Carbon::parse($invoice->due_date)->isoFormat('MMMM Y') }}
                    </td>
                    <td style="text-align: right; font-weight: bold; font-size: 14px;">
                        @php
                            $displayPrice = $invoice->price > 0 ? $invoice->price : ($invoice->customer->monthly_price ?? 0);
                        @endphp
                        Rp {{ number_format($displayPrice, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Summary -->
        <table class="total-row">
            <tr>
                <td style="width: 70%;" class="total-label">Total Tagihan</td>
                <td class="total-amount">Rp {{ number_format($displayPrice, 0, ',', '.') }}</td>
            </tr>
        </table>

        <!-- Footer -->
        <table class="footer">
            <tr>
                <td style="width: 70%;">
                    <p>Terima kasih atas kepercayaan Anda.</p>
                    <p>Harap melakukan pembayaran sebelum tanggal jatuh tempo.</p>
                </td>
                @if(!isset($isPdf))
                <td style="text-align: right;" class="no-print">
                    <button onclick="window.print()" class="btn-print">
                        <i class="fas fa-print"></i> Cetak Invoice
                    </button>
                </td>
                @endif
            </tr>
        </table>
    </div>

</body>

</html>