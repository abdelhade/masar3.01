<?php
namespace Modules\Progress\Http\Controllers\Auth;
use Modules\Progress\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected function redirectTo()
    {
    $user = Auth::user();
        // إذا كان المستخدم موظف وله مشروع محدد
        if ($user && $user->employee && $user->employee->project_id) {
            // توجيه الموظف إلى فورم التقدم اليومي لمشروعه فقط
            return route('daily-progress.create', ['project_id' => $user->employee->project_id]);
        }
        // أي مستخدم آخر (أدمن أو غيره)
        return '/';
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }
}
