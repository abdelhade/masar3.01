<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use App\Models\User;


class BranchScope implements Scope
{
        protected static $cachedBranchIds = [];

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check()) {
            /** @var User $user */
            $user = Auth::user();
            $activeBranches = $user->branches()
                ->where('is_active', 1)
                ->pluck('branches.id');
            $userId = Auth::id();

            if (!isset(static::$cachedBranchIds[$userId])) {
                static::$cachedBranchIds[$userId] = Auth::user()
                    ->branches()
                    ->where('is_active', 1)
                    ->pluck('branches.id')
                    ->toArray();
            }

            $builder->whereIn($model->getTable() . '.branch_id', static::$cachedBranchIds[$userId]);
        }
    }
}
