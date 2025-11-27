<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* 1. FIX 2 HALAMAN: Reset Margin Kertas PDF */
        @page { margin: 10px; size: A4; }
        
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            /* Beri jarak konten dari pinggir kertas manual lewat body */
            padding: 20px; 
            font-size: 14px; 
            background: #fff;
        }

        /* Hapus elemen yang tidak perlu saat print/pdf */
        .no-print { display: none; }

        /* Tampilan Web (Jika dibuka admin, kembalikan style normal) */
        @media screen {
            body { background: #fff; padding: 20px; }
            .invoice-box {
                max-width: 800px; margin: 0 auto; padding: 20px;
                background: white; border: 1px solid #fff;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            }
            .no-print { display: block; }
        }

        /* Tampilan saat jadi PDF (DomPDF) */
        .invoice-box { width: 90%; border: none; box-shadow: none; padding: 0; }

        /* Stempel Status */
        .stamp {
            position: absolute; top: 130px; right: 35px;
            padding: 5px 15px; border: 4px solid;
            font-size: 2.2rem; font-weight: bold;
            text-transform: uppercase; opacity: 0.3;
            transform: rotate(-15deg); z-index: -1; border-radius: 10px;
        }
        .is-paid { color: #28a745; border-color: #28a745; }
        .is-unpaid { color: #dc3545; border-color: #dc3545; }

        .header-title { font-size: 28px; color: #333; font-weight: bold; text-align: right;}
        .company-logo { max-height: 70px; max-width: 200px; }
        .signature-img { max-height: 70px; margin-top: 5px; }
        .table td, .table th { padding: 0.5rem; }
    </style>
</head>
<body>

    {{-- 2. LOGIKA PATH GAMBAR CERDAS (Localhost vs Hosting) --}}
    @php
        // Fungsi helper kecil untuk menentukan path gambar
        function getImagePath($filename) {
            // Cek apakah sedang dalam mode PDF
            // (Variabel $isPdf dikirim dari FrontendController)
            $inPdfMode = isset($GLOBALS['isPdf']) || isset($isPdf);

            if ($inPdfMode && $filename) {
                // MODE PDF: Harus Path Fisik (C:\... atau /home/...)
                
                // Cek 1: Path Hosting (public_html/uploads)
                $hostingPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $filename;
                if (file_exists($hostingPath)) return $hostingPath;

                // Cek 2: Path Laravel Default (public/uploads)
                $localPath = public_path('uploads/' . $filename);
                if (file_exists($localPath)) return $localPath;
                
                return ""; // Gagal temu
            } else {
                // MODE WEB: Gunakan URL (http://...)
                return asset('uploads/' . $filename);
            }
        }

        $logoSrc = ($company && $company->logo_path) ? getImagePath($company->logo_path) : null;
        $signSrc = ($company && $company->signature_path) ? getImagePath($company->signature_path) : null;
    @endphp

    {{-- 3. TOMBOL HANYA MUNCUL DI WEB (BUKAN PDF) --}}
    @if(!isset($isPdf))
        <div class="text-center mb-3 no-print">
            <button onclick="window.print()" class="btn btn-primary btn-lg shadow">
                <i class="fas fa-print"></i> Cetak Web
            </button>
            <a href="{{ url()->previous() }}" class="btn btn-secondary btn-lg shadow">Kembali</a>
        </div>
    @endif

    <div class="invoice-box">
        
        @if($invoice->status == 'paid')
            <div class="stamp is-paid">LUNAS</div>
        @else
            <div class="stamp is-unpaid">BELUM BAYAR</div>
        @endif

        {{-- HEADER --}}
        <div class="row mb-4 border-bottom pb-3">
            <div class="col-7">
                {{-- TAMPILKAN LOGO DENGAN SRC YANG SUDAH DIPROSES --}}
                @if($logoSrc)
                    <img src="{{ $logoSrc }}" class="company-logo mb-2">
                @else
                    <h2>{{ $company->company_name ?? 'ISP' }}</h2>
                @endif
                
                <div style="font-size: 0.9rem;">
                    {{ $company->address ?? '-' }}<br>
                    HP/WA: {{ $company->phone ?? '-' }}
                </div>
            </div>
            <div class="col-5 text-end">
                <div class="header-title">INVOICE</div>
                <div>No: <strong>INV-{{ str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}</strong></div>
                <div>Tgl: {{ date('d M Y') }}</div>
                <div>Jatuh Tempo: {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</div>
            </div>
        </div>

        {{-- INFO PELANGGAN --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="p-3 bg-light rounded border">
                    <small class="text-muted fw-bold">DITAGIHKAN KEPADA:</small><br>
                    <span class="fs-5 fw-bold">{{ $invoice->customer->name }}</span><br>
                    ID: {{ $invoice->customer->internet_number ?? '-' }} | HP: {{ $invoice->customer->phone ?? '-' }}<br>
                    <span class="small text-muted">{{ $invoice->customer->address ?? '' }}</span>
                </div>
            </div>
        </div>

        {{-- TABEL --}}
        <table class="table table-bordered mb-4">
            <thead class="table-light">
                <tr>
                    <th>Deskripsi Layanan</th>
                    <th class="text-end" width="30%">Jumlah (IDR)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>Paket Internet Bulanan</strong><br>
                        <small class="text-muted">Periode Tagihan: {{ \Carbon\Carbon::parse($invoice->due_date)->format('F Y') }}</small><br>
                        <small class="text-muted">Tagihan sudah termasuk PPN 11%</small>
                    </td>
                    <td class="text-end">
                        Rp {{ number_format($invoice->customer->monthly_price, 0, ',', '.') }}
                    </td>
                </tr>
                <tr class="table-active">
                    <td class="text-end fw-bold">TOTAL TAGIHAN</td>
                    <td class="text-end fw-bold fs-5">
                        Rp {{ number_format($invoice->customer->monthly_price, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- FOOTER --}}
        <div class="row mt-5">
            <div class="col-7">
                <h6>Info Pembayaran:</h6>
                <div class="text-muted small" style="font-size: 0.85rem;">
                    Silakan transfer ke rekening resmi kami:<br>
                    @if($company->bank_name && $company->account_number)
                        <div class="fw-bold mt-1 text-dark">
                            {{ strtoupper($company->bank_name) }}: {{ $company->account_number }}
                        </div>
                        <div>a.n {{ $company->account_holder ?? $company->owner_name }}</div>
                    @else
                        -
                    @endif
                </div>
            </div>
            <div class="col-5 text-center">
                <div class="mb-4">Hormat Kami,</div>
                
                {{-- TAMPILKAN SIGNATURE --}}
                @if($signSrc)
                    <img src="{{ $signSrc }}" class="signature-img">
                @endif
                
                <div class="fw-bold mt-2">{{ $company->owner_name ?? 'Admin' }}</div>
            </div>
        </div>

        <div class="text-center mt-4 pt-3 border-top text-muted small">
            Terima kasih atas kepercayaan Anda menggunakan layanan kami.
        </div>
    </div>

</body>
</html>