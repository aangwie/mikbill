<?php

namespace App\Imports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CustomersImport implements ToModel, WithHeadingRow
{
    /**
    * Mapping baris excel ke Database
    */
    public function model(array $row)
    {
        // Pastikan kolom mandatory ada isinya
        if(!isset($row['username_pppoe']) || !isset($row['nama_pelanggan'])) {
            return null;
        }

        // Cek duplicate agar tidak error (Update or Create)
        return Customer::updateOrCreate(
            ['pppoe_username' => $row['username_pppoe']], // Kunci pencarian (unik)
            [
                'internet_number' => $row['nomor_internet'] ?? rand(10000000, 99999999),
                'name'            => $row['nama_pelanggan'],
                'phone'           => $row['no_hp'],
                'pppoe_password'  => $row['password_pppoe'],
                'profile'         => $row['profile_mikrotik'] ?? 'default',
                'monthly_price'   => $row['harga_paket'] ?? 0,
                'address'         => $row['alamat'],
                'latitude'        => $row['latitude'],
                'longitude'       => $row['longitude'],
                'is_active'       => true,
            ]
        );
    }
}