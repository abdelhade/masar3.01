<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\LoginSession;

class LogUserLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $sessionId = session()->getId();

        // منع التكرار لو نفس session_id موجود بالفعل
        if (!LoginSession::where('session_id', $sessionId)->exists()) {
            LoginSession::create([
                'user_id'    => $event->user->getAuthIdentifier(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'device'     => request()->header('User-Agent'),
                'login_at'   => now(),
                'session_id' => $sessionId,
            ]);
        }
    }
}
