<?php

use Illuminate\Support\Facades\Auth;


if (! function_exists('userBranches')) {
    function userBranches()
    {
        if (Auth::check()) {
            return Auth::user()
                ->branches()
                ->where('branches.is_active', 1)
                ->select('branches.id', 'branches.name')
                ->get();
        }
        return collect();
    }
}
