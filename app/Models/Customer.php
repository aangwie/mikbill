<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\BelongsToTenant;

class Customer extends Model
{
    use BelongsToTenant;

    protected $guarded = [];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function olt(): BelongsTo
    {
        return $this->belongsTo(Olt::class);
    }
}