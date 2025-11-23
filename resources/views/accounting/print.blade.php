<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan - {{ $month }}/{{ $year }}</title>
    <link rel="icon" href="{{ $global_favicon ?? asset('favicon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: sans-serif; }
        .header { border-bottom: 2px solid #000; padding-bottom: 20px; margin-bottom: 20px; }
        .logo { max-height: 60px; }
        .total-row { font-weight: bold; background-color: #f8f9fa; }
        .rekap-box { border: 1px solid #ccc; padding: 10px; background: #eee; text-align: right; font-weight: bold; }
        @media print {
            .no-print { display: none; }
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body class="p-4">

    <div class="no-print mb-3">
        <button onclick="window.print()" class="btn btn-primary">Cetak Laporan</button>
        <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
    </div>

    {{-- LOGIKA JUDUL DINAMIS --}}
    @php
        switch ($reportType) {
            case 1:
                $judulLaporan = "LAPORAN RINCIAN PENDAPATAN & PENGELUARAN";
                break;
            case 2:
                $judulLaporan = "LAPORAN REKAP PENDAPATAN & RINCIAN PENGELUARAN";
                break;
            case 3:
                $judulLaporan = "LAPORAN RINCIAN PENDAPATAN & REKAP PENGELUARAN";
                break;
            case 4:
                $judulLaporan = "LAPORAN REKAPITULASI KEUANGAN";
                break;
            default:
                $judulLaporan = "LAPORAN LABA RUGI";
                break;
        }
    @endphp

    {{-- KOP SURAT --}}
    <div class="header row">
        <div class="col-8">
            @if($company && $company->logo_path)
                <img src="{{ asset('storage/' . $company->logo_path) }}" class="logo mb-2">
            @else
                <h2>{{ $company->company_name ?? 'MIKBILL ISP' }}</h2>
            @endif
            <div>{{ $company->address ?? '' }}</div>
            <div>Telp: {{ $company->phone ?? '' }}</div>
        </div>
        <div class="col-4 text-end">
            {{-- JUDUL TAMPIL DISINI --}}
            <h5 class="fw-bold mt-2 text-uppercase">{{ $judulLaporan }}</h5>
            
            <div>Periode: {{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}</div>
            <div class="small text-muted">Dicetak pada: {{ date('d/m/Y H:i') }}</div>
        </div>
    </div>

    {{-- ================= BAGIAN A: PEMASUKAN ================= --}}
    <h5 class="fw-bold text-success mb-2">A. PEMASUKAN (OMSET)</h5>
    
    @if(in_array($reportType, [1, 3]))
        <table class="table table-bordered table-sm mb-4">
            <thead>
                <tr class="table-success">
                    <th style="width: 5%">No</th>
                    <th>Tanggal Bayar</th>
                    <th>Pelanggan</th>
                    <th>Paket Internet</th>
                    <th class="text-end">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $index => $inv)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $inv->updated_at->format('d/m/Y') }}</td>
                    <td>{{ $inv->customer->name }}</td>
                    <td>{{ $inv->customer->internet_number }}</td>
                    <td class="text-end">Rp {{ number_format($inv->customer->monthly_price, 0, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="4" class="text-end">TOTAL PEMASUKAN</td>
                    <td class="text-end">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    @else
        <div class="rekap-box mb-4 text-success">
            TOTAL PEMASUKAN ({{ count($invoices) }} Transaksi) : Rp {{ number_format($totalRevenue, 0, ',', '.') }}
        </div>
    @endif


    {{-- ================= BAGIAN B: PENGELUARAN ================= --}}
    <h5 class="fw-bold text-danger mb-2">B. PENGELUARAN (BIAYA)</h5>

    @if(in_array($reportType, [1, 2]))
        <table class="table table-bordered table-sm mb-4">
            <thead>
                <tr class="table-danger">
                    <th style="width: 5%">No</th>
                    <th>Tanggal</th>
                    <th>Keterangan Pengeluaran</th>
                    <th class="text-end">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $index => $exp)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $exp->transaction_date->format('d/m/Y') }}</td>
                    <td>{{ $exp->description }}</td>
                    <td class="text-end">Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center">Tidak ada pengeluaran.</td></tr>
                @endforelse
                <tr class="total-row">
                    <td colspan="3" class="text-end">TOTAL PENGELUARAN</td>
                    <td class="text-end">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    @else
        <div class="rekap-box mb-4 text-danger">
            TOTAL PENGELUARAN ({{ count($expenses) }} Transaksi) : Rp {{ number_format($totalExpense, 0, ',', '.') }}
        </div>
    @endif


    {{-- ================= BAGIAN C: RINGKASAN AKHIR ================= --}}
    <div class="row justify-content-end">
        <div class="col-md-5">
            <table class="table table-bordered">
                <tr>
                    <td class="fw-bold">Total Pemasukan</td>
                    <td class="text-end fw-bold text-success">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="fw-bold">Total Pengeluaran</td>
                    <td class="text-end fw-bold text-danger">( Rp {{ number_format($totalExpense, 0, ',', '.') }} )</td>
                </tr>
                <tr class="table-active" style="border-top: 2px solid #000;">
                    <td class="fw-bold fs-5">LABA BERSIH</td>
                    <td class="text-end fw-bold fs-5 {{ $netProfit >= 0 ? 'text-primary' : 'text-danger' }}">
                        Rp {{ number_format($netProfit, 0, ',', '.') }}
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- TANDA TANGAN --}}
    <div class="row mt-5">
        <div class="col-8"></div>
        <div class="col-4 text-center">
            <div class="mb-5">Mengetahui,</div>
            @if($company && $company->signature_path)
                <img src="{{ asset('storage/' . $company->signature_path) }}" style="max-height: 80px;">
            @endif
            <div class="fw-bold mt-2">{{ $company->owner_name ?? 'Admin' }}</div>
        </div>
    </div>

</body>
</html>