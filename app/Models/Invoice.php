<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;

class Invoice extends Model
{
    use BelongsToTenant;
    protected $guarded = [];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}