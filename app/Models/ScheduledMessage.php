<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class ScheduledMessage extends Model
{
    use BelongsToTenant;

    protected $guarded = [];

    protected $casts = [
        'customer_ids' => 'array',
        'scheduled_at' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function isScheduled(): bool
    {
        return $this->scheduled_at !== null;
    }

    public function isDue(): bool
    {
        return $this->scheduled_at !== null && $this->scheduled_at->lte(now());
    }

    public static function getMaxRecipients(string $whatsappAge): int
    {
        return match ($whatsappAge) {
            '1-6' => 15,
            '6-12' => 50,
            default => 9999, // 12+ = unlimited
        };
    }
}
