<?php

// namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use App\Models\User;


if (! function_exists('userBranches')) {
    function userBranches()
    {
        if (Auth::check()) {
            /** @var User $user */
            $user = Auth::user();
            return $user->branches()
                ->where('branches.is_active', 1)
                ->select('branches.id', 'branches.name')
                ->get();
        }
        return collect();
    }
}
