<?php

namespace App\Models;

use App\Traits\MobileBelongsToTenant;

class MobileCustomer extends Customer
{
    use MobileBelongsToTenant;

    protected $table = 'customers';
}
