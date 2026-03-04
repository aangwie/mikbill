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
            $tableName = $model->getTable();

            if ($user->role === 'superadmin' || $user->role === 'admin') {
                // By default, both see only their own records
                $builder->where($tableName . '.admin_id', $user->id);
            } elseif ($user->role === 'operator') {
                // Operator sees data from their parent admin
                $builder->where($tableName . '.admin_id', $user->parent_id);
            }
        }
    }
}
