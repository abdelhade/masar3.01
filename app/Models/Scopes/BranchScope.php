<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class BranchScope implements Scope
{
    protected static $cachedBranchIds = [];

    /**
     * Apply the scope to a given Eloquent query builder.
     * Optimized with improved caching and eager loading
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check()) {
            $userId = Auth::id();
            $cacheKey = "user_branches_{$userId}";

            // Check static cache first (fastest)
            if (! isset(static::$cachedBranchIds[$userId])) {
                // Check application cache (persists across requests)
                $branchIds = Cache::remember($cacheKey, 3600, function () {
                    /** @var User $user */
                    $user = Auth::user();

                    // Use eager loading to avoid N+1 queries
                    return $user->branches()
                        ->where('is_active', 1)
                        ->pluck('branches.id')
                        ->toArray();
                });

                static::$cachedBranchIds[$userId] = $branchIds;
            }

            $branchIds = static::$cachedBranchIds[$userId];

            // Only apply scope if user has branches
            if (! empty($branchIds)) {
                $builder->whereIn($model->getTable().'.branch_id', $branchIds);
            } else {
                // If no branches, return empty result set
                $builder->whereRaw('1 = 0');
            }
        }
    }

    /**
     * Clear cache for a specific user
     */
    public static function clearCache(?int $userId = null): void
    {
        if ($userId) {
            unset(static::$cachedBranchIds[$userId]);
            Cache::forget("user_branches_{$userId}");
        } else {
            static::$cachedBranchIds = [];
        }
    }
}
