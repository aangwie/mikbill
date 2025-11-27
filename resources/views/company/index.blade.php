<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Perusahaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    {{-- Favicon --}}
    <link rel="icon" href="{{ $global_favicon ?? asset('favicon.ico') }}">

    <style>
        .img-preview {
            max-width: 150px;
            border: 1px dashed #ccc;
            padding: 5px;
            border-radius: 5px;
            margin-top: 10px;
        }
    </style>
</head>
<body class="bg-light">

    @include('layouts.navbar_partial')

    <div class="container pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="fas fa-building text-primary"></i> Profil Perusahaan</h3>
        </div>

        @if(session('success')) 
            <div class="alert alert-success border-0 shadow-sm"><i class="fas fa-check-circle"></i> {{ session('success') }}</div> 
        @endif

        {{-- FORM UTAMA --}}
        <div class="card shadow border-0">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary">Data Identitas Provider & Pembayaran</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('company.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-7 border-end">
                            <h6 class="text-primary fw-bold mb-3">Identitas Umum</h6>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Perusahaan / ISP</label>
                                <input type="text" name="company_name" class="form-control" value="{{ $company->company_name }}" placeholder="Contoh: NetWiz Internet" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nama Pemilik / Direktur</label>
                                    <input type="text" name="owner_name" class="form-control" value="{{ $company->owner_name }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nomor WhatsApp / Hotline</label>
                                    <input type="text" name="phone" class="form-control" value="{{ $company->phone }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Alamat Lengkap</label>
                                <textarea name="address" class="form-control" rows="3">{{ $company->address }}</textarea>
                            </div>

                            <hr> 

                            {{-- DATA REKENING BANK --}}
                            <h6 class="text-primary fw-bold mb-3"><i class="fas fa-money-check-alt"></i> Info Pembayaran (Untuk Invoice)</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Nama Bank</label>
                                    <input type="text" name="bank_name" class="form-control" value="{{ $company->bank_name }}" placeholder="Cth: BCA">
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Nomor Rekening</label>
                                    <input type="text" name="account_number" class="form-control" value="{{ $company->account_number }}" placeholder="Cth: 1234567890">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Atas Nama (Pemilik Rekening)</label>
                                <input type="text" name="account_holder" class="form-control" value="{{ $company->account_holder }}" placeholder="Cth: PT. NetWiz Indonesia">
                            </div>
                        </div>

                        <div class="col-md-5">
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">Logo Perusahaan</label>
                                <input type="file" name="logo" class="form-control" accept="image/*">
                                <div class="text-muted small">Format: JPG/PNG. Otomatis jadi Favicon.</div>
                                
                                @if($company->logo_path)
                                    <div class="mt-2">
                                        <small class="d-block text-secondary">Logo Saat Ini:</small>
                                        {{-- PERUBAHAN DISINI: uploads/ --}}
                                        <img src="{{ asset('uploads/' . $company->logo_path) }}" class="img-preview" alt="Logo">
                                    </div>
                                @endif
                            </div>

                            <hr>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Tanda Tangan / Stempel (Digital)</label>
                                <input type="file" name="signature" class="form-control" accept="image/*">
                                <div class="text-muted small">Akan muncul di bagian bawah Invoice.</div>
                                
                                @if($company->signature_path)
                                    <div class="mt-2">
                                        <small class="d-block text-secondary">TTD Saat Ini:</small>
                                        {{-- PERUBAHAN DISINI: uploads/ --}}
                                        <img src="{{ asset('uploads/' . $company->signature_path) }}" class="img-preview" alt="Signature">
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>

                    <div class="card-footer bg-white text-end pt-3">
                        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i> Simpan Perubahan</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>