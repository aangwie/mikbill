<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomerTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    /**
     * Header Kolom (Wajib sama dengan format Import)
     */
    public function headings(): array
    {
        return [
            'nomor_internet',
            'nama_pelanggan',
            'no_hp',
            'username_pppoe',
            'password_pppoe',
            'profile_mikrotik',
            'harga_paket',
            'alamat',
            'latitude',
            'longitude',
        ];
    }

    /**
     * Data Contoh (Dummy Row) agar user paham format isinya
     */
    public function array(): array
    {
        return [
            [
                '88291022',           // nomor_internet
                'Contoh Pelanggan',   // nama_pelanggan
                '628123456789',       // no_hp
                'user_contoh',        // username_pppoe
                'pass123',            // password_pppoe
                'default',            // profile_mikrotik
                '150000',             // harga_paket
                'Jl. Mawar No. 10',   // alamat
                '-7.123456',          // latitude
                '110.123456',         // longitude
            ],
        ];
    }

    /**
     * Styling: Bold pada Header agar jelas
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}