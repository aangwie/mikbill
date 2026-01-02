<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;

class Expense extends Model
{
    use BelongsToTenant;
    protected $guarded = [];

    // Casting agar tanggal otomatis jadi objek Carbon
    protected $casts = [
        'transaction_date' => 'date',
    ];
}