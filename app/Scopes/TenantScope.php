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
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->isSuperAdmin()) {
                // Superadmin sees everything.
                // Optional: Check if we want to filter by specific admin if viewing their "dashboard" context?
                // For now, global view.
                return;
            }

            if ($user->isAdmin()) {
                $builder->where('admin_id', $user->id);
            } elseif ($user->isOperator()) {
                // Operator sees data belonging to their Admin (parent)
                $builder->where('admin_id', $user->parent_id);
            }
        }
    }
}
