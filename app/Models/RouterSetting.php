<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;

class RouterSetting extends Model
{
    use BelongsToTenant;

    // Sesuaikan dengan nama tabel asli kamu
    protected $table = 'router_settings';
    protected $guarded = ['id'];

    // Relasi: Router ini punya banyak OLT
    public function olts()
    {
        return $this->hasMany(Olt::class, 'router_id');
    }
}
