<?php

namespace App\Traits;

use App\Scopes\MobileTenantScope;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait MobileBelongsToTenant
{
    /**
     * The "booted" method of the model for Mobile context.
     */
    protected static function bootMobileBelongsToTenant(): void
    {
        static::addGlobalScope(new MobileTenantScope);

        static::creating(function ($model) {
            $user = Auth::guard('sanctum')->user() ?? Auth::user();
            if ($user) {
                if ($user->role === 'admin' || $user->role === 'superadmin') {
                    $model->admin_id = $user->id;
                } elseif ($user->role === 'operator') {
                    $model->admin_id = $user->parent_id;
                }
            }
        });
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
