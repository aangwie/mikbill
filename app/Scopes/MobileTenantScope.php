<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class MobileTenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     * This scope is specifically for Mobile API requests.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Mobile uses Sanctum, but we check both just in case
        $user = Auth::guard('sanctum')->user() ?? Auth::user();

        if ($user) {
            $tableName = $model->getTable();

            // Mobile-specific logic:
            // Admin/Superadmin sees their own data.
            // Operator sees their parent admin's data.
            if ($user->role === 'superadmin' || $user->role === 'admin') {
                $builder->where($tableName . '.admin_id', $user->id);
            } elseif ($user->role === 'operator') {
                $builder->where($tableName . '.admin_id', $user->parent_id);
            }
        }
    }
}
