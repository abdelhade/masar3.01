<?php

namespace App\Http\Controllers;

use App\Models\LoginSession;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use RealRashid\SweetAlert\Facades\Alert;

class UserMonitoringController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('can:view login-history')->only(['loginHistory']);
        $this->middleware('can:view activity-logs')->only(['activityLog']);
        $this->middleware('can:view active-sessions')->only(['activeSessions', 'terminateSession']);
        $this->middleware('can:delete Users')->only(['destroy']);
    }

    /**
     * سجل تسجيل الدخول
     */
    public function loginHistory()
    {
        $loginSessions = LoginSession::with('user')
            ->orderBy('login_at', 'desc')
            ->paginate(20);

        return view('users.monitoring.login-history', compact('loginSessions'));
    }

    /**
     * الجلسات النشطة
     */
    public function activeSessions()
    {
        $activeSessions = LoginSession::with('user')
            ->whereNull('logout_at')
            ->orderBy('login_at', 'desc')
            ->get();

        return view('users.monitoring.active-sessions', compact('activeSessions'));
    }

    /**
     * إنهاء جلسة معينة
     */
    public function terminateSession(Request $request, $sessionId)
    {
        $this->authorize('edit Users');

        try {
            $session = LoginSession::findOrFail($sessionId);

            $session->update([
                'logout_at' => now(),
                'session_duration' => now()->diffInMinutes($session->login_at),
            ]);

            Alert::toast('تم إنهاء الجلسة بنجاح', 'success');

            return redirect()->back();
        } catch (\Exception $e) {
            Alert::toast('حدث خطأ أثناء إنهاء الجلسة', 'error');

            return redirect()->back();
        }
    }

    /**
     * سجل النشاطات
     * Redirect to the new ActivityLog module
     */
    public function activityLog(Request $request)
    {
        return redirect()->route('activitylog.index', $request->all());
    }
}
