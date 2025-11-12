<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LoginSession;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserMonitoringController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('can:view Users');
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
     */
    public function activityLog(Request $request)
    {
        $query = LoginSession::with('user')
            ->whereNotNull('logout_at')
            ->orderBy('logout_at', 'desc');

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('login_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('login_at', '<=', $request->date_to);
        }

        $activities = $query->paginate(20);
        $users = User::orderBy('name')->get();

        return view('users.monitoring.activity-log', compact('activities', 'users'));
    }

}
