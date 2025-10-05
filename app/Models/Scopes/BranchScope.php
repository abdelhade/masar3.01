<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Modules\Branches\Models\Branch;


class BranchScope implements Scope
{
        protected static $cachedBranchIds = [];

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check()) {
            $userId = Auth::id();

            if (!isset(static::$cachedBranchIds[$userId])) {
                /** @var User $user */
                $user = Auth::user();
                static::$cachedBranchIds[$userId] = $user->branches()
                    ->where('is_active', 1)
                    ->pluck('branches.id')
                    ->toArray();
            }

            $builder->whereIn($model->getTable() . '.branch_id', static::$cachedBranchIds[$userId]);
        }
    }
}
