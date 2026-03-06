<?php

namespace App\Models;

use App\Traits\MobileBelongsToTenant;

class MobileCompany extends Company
{
    use MobileBelongsToTenant;

    protected $table = 'companies';
}
