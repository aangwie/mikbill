<?php

namespace App\Models;

use App\Traits\MobileBelongsToTenant;

class MobileInvoice extends Invoice
{
    use MobileBelongsToTenant;

    protected $table = 'invoices';

    public function customer()
    {
        return $this->belongsTo(MobileCustomer::class, 'customer_id');
    }
}
