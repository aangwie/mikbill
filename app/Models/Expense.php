<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $guarded = [];
    
    // Casting agar tanggal otomatis jadi objek Carbon
    protected $casts = [
        'transaction_date' => 'date',
    ];
}