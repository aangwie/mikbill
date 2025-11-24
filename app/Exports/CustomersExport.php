<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomersExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * Ambil semua data
    */
    public function collection()
    {
        return Customer::all();
    }

    /**
     * Judul Kolom di Excel
     */
    public function headings(): array
    {
        return [
            'Nomor Internet',
            'Nama Pelanggan',
            'No. HP',
            'Username PPPoE',
            'Password PPPoE',
            'Profile Mikrotik',
            'Harga Paket',
            'Alamat',
            'Latitude',
            'Longitude',
        ];
    }

    /**
     * Mapping data agar sesuai urutan header
     */
    public function map($customer): array
    {
        return [
            $customer->internet_number,
            $customer->name,
            $customer->phone,
            $customer->pppoe_username,
            $customer->pppoe_password,
            $customer->profile,
            $customer->monthly_price,
            $customer->address,
            $customer->latitude,
            $customer->longitude,
        ];
    }
}