<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class EmployeeAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        
        // التحقق من تسجيل دخول الموظف
        $isLoggedIn = Session::get('employee_logged_in', false);
        $employeeId = Session::get('employee_id');
        

        if (!$isLoggedIn || !$employeeId) {
            
            // إذا كان طلب API، إرجاع JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مسجل دخول. يرجى تسجيل الدخول أولاً',
                    'redirect' => '/mobile/employee-login'
                ], 401);
            }

            // إذا كان طلب عادي، إعادة توجيه لصفحة تسجيل الدخول
            return redirect('/mobile/employee-login');
        }


        // إضافة بيانات الموظف للطلب
        $request->merge([
            'current_employee_id' => $employeeId,
            'current_employee_name' => Session::get('employee_name'),
            'current_employee_finger_print_id' => Session::get('employee_finger_print_id'),
            'current_employee_finger_print_name' => Session::get('employee_finger_print_name')
        ]);

        return $next($request);
    }
}
