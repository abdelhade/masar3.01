<?php

namespace App\Providers;

use App\Events\LeaveRequestApproved;
use App\Events\LeaveRequestCancelled;
use App\Events\LeaveRequestRejected;
use App\Events\LeaveRequestSubmitted;
use App\Listeners\LogUserLogin;
use App\Listeners\LogUserLogout;
use App\Listeners\UpdateLeaveBalanceOnApproved;
use App\Listeners\UpdateLeaveBalanceOnCancelled;
use App\Listeners\UpdateLeaveBalanceOnRejected;
use App\Listeners\UpdateLeaveBalanceOnSubmitted;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Login::class => [
            LogUserLogin::class,
        ],
        Logout::class => [
            LogUserLogout::class,
        ],
        LeaveRequestSubmitted::class => [
            UpdateLeaveBalanceOnSubmitted::class,
        ],
        LeaveRequestApproved::class => [
            UpdateLeaveBalanceOnApproved::class,
        ],
        LeaveRequestRejected::class => [
            UpdateLeaveBalanceOnRejected::class,
        ],
        LeaveRequestCancelled::class => [
            UpdateLeaveBalanceOnCancelled::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
