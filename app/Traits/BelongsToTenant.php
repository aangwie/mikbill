<?php

namespace App\Traits;

use App\Scopes\TenantScope;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToTenant
{
    /**
     * The "booted" method of the model.
     */
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if (Auth::check()) {
                $user = Auth::user();
                if ($user->isAdmin() || $user->isSuperAdmin()) {
                    $model->admin_id = $user->id;
                } elseif ($user->isOperator()) {
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
