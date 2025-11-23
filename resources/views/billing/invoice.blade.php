<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->id }}</title>
    <link rel="icon" href="{{ $global_favicon ?? asset('favicon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #eee;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }

        .invoice-box {
            max-width: 800px;
            margin: 30px auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            background: white;
            position: relative;
            /* Untuk stempel */
            overflow: hidden;
        }

        /* Stempel Status */
        .stamp {
            position: absolute;
            top: 150px;
            right: 50px;
            padding: 10px 20px;
            border: 5px solid;
            font-size: 3rem;
            font-weight: bold;
            text-transform: uppercase;
            opacity: 0.3;
            transform: rotate(-15deg);
            z-index: 0;
            border-radius: 10px;
        }

        .is-paid {
            color: #28a745;
            border-color: #28a745;
        }

        .is-unpaid {
            color: #dc3545;
            border-color: #dc3545;
        }

        /* Header Invoice */
        .header-title {
            font-size: 40px;
            color: #333;
            font-weight: bold;
            text-align: right;
        }

        .company-logo {
            max-height: 80px;
            max-width: 200px;
        }

        /* Tanda Tangan */
        .signature-img {
            max-height: 80px;
            margin-top: 10px;
        }

        /* Print Settings */
        @media print {
            body {
                background: white;
                -webkit-print-color-adjust: exact;
            }

            .invoice-box {
                box-shadow: none;
                border: none;
                margin: 0;
                width: 100%;
                max-width: 100%;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="text-center mb-3 no-print">
        <button onclick="window.print()" class="btn btn-primary btn-lg shadow">
            <i class="fas fa-print"></i> Cetak / Simpan PDF
        </button>
        <a href="{{ route('billing.index') }}" class="btn btn-secondary btn-lg shadow">Kembali</a>
    </div>

    <div class="invoice-box">

        {{-- LOGIKA STEMPEL STATUS --}}
        @if($invoice->status == 'paid')
        <div class="stamp is-paid">LUNAS</div>
        @else
        <div class="stamp is-unpaid">BELUM BAYAR</div>
        @endif

        {{-- HEADER KOP SURAT --}}
        <div class="row mb-5 border-bottom pb-3">
            <div class="col-8">
                {{-- Logo Perusahaan --}}
                @if($company && $company->logo_path)
                <img src="{{ asset('storage/' . $company->logo_path) }}" class="company-logo mb-2">
                @else
                <h2>{{ $company->company_name ?? 'NAMA PERUSAHAAN' }}</h2>
                @endif

                <div>{{ $company->address ?? 'Alamat Perusahaan' }}</div>
                <div>HP/WA: {{ $company->phone ?? '-' }}</div>
            </div>
            <div class="col-4 text-end">
                <div class="header-title">INVOICE</div>
                <div>No: <strong>INV-{{ str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}</strong></div>
                <div>Tanggal: {{ date('d M Y') }}</div>
                <div>Jatuh Tempo: {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</div>
            </div>
        </div>

        {{-- INFO PELANGGAN --}}
        <div class="row mb-4">
            <div class="col-6">
                <h6 class="fw-bold text-secondary">DITAGIHKAN KEPADA:</h6>
                <h5 class="fw-bold">{{ $invoice->customer->name }}</h5>
                <div>ID Pelanggan: {{ $invoice->customer->internet_number ?? '-' }}</div>
                <div>HP: {{ $invoice->customer->phone ?? '-' }}</div>
                <div><i>{{ $invoice->customer->address ?? '' }}</i></div>
            </div>
        </div>

        {{-- TABEL ITEM --}}
        <table class="table table-bordered mb-4">
            <thead class="table-light">
                <tr>
                    <th>Deskripsi Layanan</th>
                    <th class="text-end" width="25%">Jumlah (IDR)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>Paket Internet Bulanan</strong><br>
                        <small class="text-muted">Periode: {{ \Carbon\Carbon::parse($invoice->due_date)->format('F Y') }}</small><br>
                        <small class="text-muted">Harga diatas sudah termasuk PPN 11%</small>
                    </td>
                    <td class="text-end">
                        Rp {{ number_format($invoice->customer->monthly_price, 0, ',', '.') }}
                    </td>
                </tr>
                {{-- Baris Total --}}
                <tr class="table-active">
                    <td class="text-end fw-bold">TOTAL TAGIHAN</td>
                    <td class="text-end fw-bold fs-5">
                        Rp {{ number_format($invoice->customer->monthly_price, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- FOOTER & TTD --}}
        {{-- FOOTER & TTD --}}
        <div class="row mt-5">
            <div class="col-6">
                <h6>Metode Pembayaran:</h6>
                <div class="text-muted small">
                    Silakan transfer ke rekening resmi kami:<br>

                    {{-- TAMPILKAN BANK DINAMIS --}}
                    @if($company->bank_name && $company->account_number)
                    <div class="fw-bold mt-1 text-dark" style="font-size: 1.1em;">
                        {{ strtoupper($company->bank_name) }}: {{ $company->account_number }}
                    </div>
                    <div>a.n {{ $company->account_holder ?? $company->owner_name }}</div>
                    @else
                    {{-- Fallback jika belum diisi --}}
                    <div class="text-danger">Rekening belum diatur di menu Perusahaan.</div>
                    @endif

                    <br>
                    Atau bayar tunai di outlet pembayaran yang bekerjasama dengan kami.
                </div>
            </div>

            <div class="col-6 text-center">
                <div class="mb-5">Hormat Kami,</div>

                @if($company && $company->signature_path)
                <img src="{{ asset('storage/' . $company->signature_path) }}" class="signature-img">
                @endif

                <div class="fw-bold mt-2">{{ $company->owner_name ?? 'Administrator' }}</div>
                <div class="small text-muted">{{ $company->company_name ?? 'ISP Provider' }}</div>
            </div>
        </div>

        <div class="text-center mt-5 pt-3 border-top text-muted small">
            Terima kasih atas kepercayaan Anda menggunakan layanan kami.
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>

</html>