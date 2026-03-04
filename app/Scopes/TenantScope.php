<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user() ?? (Auth::guard('sanctum')->check() ? Auth::guard('sanctum')->user() : null);
        \Illuminate\Support\Facades\Log::info('TenantScope Triggered. User ID: ' . ($user ? $user->id : 'NULL'));

        if ($user) {
            if ($user->isSuperAdmin() || $user->isAdmin()) {
                // Both Superadmin and Admin only see their own data globally by default.
                $builder->where('admin_id', $user->id);
            } elseif ($user->isOperator()) {
                // Operator sees data belonging to their Admin (parent)
                $builder->where('admin_id', $user->parent_id);
            }
        }
    }
}
