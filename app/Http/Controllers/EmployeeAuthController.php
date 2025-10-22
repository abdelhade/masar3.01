<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class EmployeeAuthController extends Controller
{
    /**
     * تسجيل دخول الموظف باستخدام بيانات البصمة
     */
    public function login(Request $request)
    {
        
        try {
            // التحقق من صحة البيانات
            $validator = Validator::make($request->all(), [
                'finger_print_id' => 'required|integer',
                'finger_print_name' => 'required|string|max:255',
                'password' => 'required|string'
            ]);

            if ($validator->fails()) {
                 Log::warning('[Mobile Employee Login] Validation failed', [
                    'errors' => $validator->errors()->toArray(),
                    'ip' => $request->ip(),
                    'ua' => $request->userAgent(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'بيانات غير صحيحة',
                    'errors' => $validator->errors()
                ], 422);
            }

            // البحث عن الموظف
            
            $employee = Employee::where('finger_print_id', $request->finger_print_id)
                                ->where('finger_print_name', $request->finger_print_name)
                                ->with(['department', 'job', 'shift'])
                                ->first();

            if (!$employee) {
                Log::warning('[Mobile Employee Login] Employee not found', [
                    'finger_print_id' => $request->finger_print_id,
                    'finger_print_name' => $request->finger_print_name,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'رقم البصمة أو اسم البصمة غير صحيح'
                ], 401);
            }
            

            // التحقق من حالة الموظف
            if ($employee->status !== 'مفعل') {
                Log::notice('[Mobile Employee Login] Inactive employee tried to login', [
                    'employee_id' => $employee->id,
                    'status' => $employee->status,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'حساب الموظف معطل. يرجى التواصل مع الإدارة'
                ], 403);
            }

            // التحقق من كلمة المرور
            if (!$employee->password || !Hash::check($request->password, $employee->password)) {
                Log::warning('[Mobile Employee Login] Wrong password', [
                    'employee_id' => $employee->id,
                    'finger_print_id' => $employee->finger_print_id,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'كلمة المرور غير صحيحة'
                ], 401);
            }

            // تسجيل دخول الموظف في الجلسة
            
            Session::put('employee_id', $employee->id);
            Session::put('employee_name', $employee->name);
            Session::put('employee_finger_print_id', $employee->finger_print_id);
            Session::put('employee_finger_print_name', $employee->finger_print_name);
            Session::put('employee_logged_in', true);

            // إرجاع بيانات الموظف
            
            Log::info('[Mobile Employee Login] Login successful', [
                'employee_id' => $employee->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل الدخول بنجاح',
                'data' => [
                    'employee' => [
                        'id' => $employee->id,
                        'name' => $employee->name,
                        'position' => $employee->position,
                        'status' => $employee->status,
                        'finger_print_id' => $employee->finger_print_id,
                        'finger_print_name' => $employee->finger_print_name,
                        'department' => $employee->department,
                        'job' => $employee->job,
                        'shift' => $employee->shift
                    ],
                    'session' => [
                        'employee_id' => $employee->id,
                        'logged_in' => true
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('[Mobile Employee Login] Exception during login', [
                'message' => $e->getMessage(),
                'trace' => collect($e->getTrace())->take(5)->toArray(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تسجيل الدخول'
            ], 500);
        }
    }

    /**
     * تسجيل خروج الموظف
     */
    public function logout(Request $request)
    {
        try {
            // مسح بيانات الجلسة
            Session::forget([
                'employee_id',
                'employee_name',
                'employee_finger_print_id',
                'employee_finger_print_name',
                'employee_logged_in'
            ]);

            Log::info('[Mobile Employee Login] Logout successful', [
                'employee_id' => $request->session()->get('employee_id'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل الخروج بنجاح'
            ]);

        } catch (\Exception $e) {
            Log::error('[Mobile Employee Login] Exception during logout', [
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تسجيل الخروج',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * التحقق من حالة تسجيل الدخول
     */
    public function checkAuth(Request $request)
    {
        try {
            $employeeId = Session::get('employee_id');
            $isLoggedIn = Session::get('employee_logged_in', false);

            if (!$isLoggedIn || !$employeeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مسجل دخول',
                    'data' => [
                        'logged_in' => false
                    ]
                ]);
            }

            // جلب بيانات الموظف
            $employee = Employee::where('id', $employeeId)
                                ->with(['department', 'job', 'shift'])
                                ->first();

            if (!$employee) {
                // مسح الجلسة إذا لم يعد الموظف موجود
                Session::forget([
                    'employee_id',
                    'employee_name',
                    'employee_finger_print_id',
                    'employee_finger_print_name',
                    'employee_logged_in'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'الموظف غير موجود',
                    'data' => [
                        'logged_in' => false
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'مسجل دخول',
                'data' => [
                    'logged_in' => true,
                    'employee' => [
                        'id' => $employee->id,
                        'name' => $employee->name,
                        'position' => $employee->position,
                        'status' => $employee->status,
                        'finger_print_id' => $employee->finger_print_id,
                        'finger_print_name' => $employee->finger_print_name,
                        'department' => $employee->department,
                        'job' => $employee->job,
                        'shift' => $employee->shift
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('[Mobile Employee Login] Exception during auth check', [
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في التحقق من تسجيل الدخول',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * الحصول على بيانات الموظف الحالي
     */
    public function getCurrentEmployee(Request $request)
    {
        try {
            $employeeId = Session::get('employee_id');
            $isLoggedIn = Session::get('employee_logged_in', false);

            if (!$isLoggedIn || !$employeeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مسجل دخول'
                ], 401);
            }

            $employee = Employee::where('id', $employeeId)
                                ->with(['department', 'job', 'shift'])
                                ->first();

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'الموظف غير موجود'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'position' => $employee->position,
                    'status' => $employee->status,
                    'finger_print_id' => $employee->finger_print_id,
                    'finger_print_name' => $employee->finger_print_name,
                    'department' => $employee->department,
                    'job' => $employee->job,
                    'shift' => $employee->shift,
                    'phone' => $employee->phone,
                    'email' => $employee->email,
                    'date_of_hire' => $employee->date_of_hire,
                    'salary' => $employee->salary,
                    'salary_type' => $employee->salary_type
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('[Mobile Employee Login] Exception while fetching current employee', [
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب بيانات الموظف',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * استقبال أخطاء العميل (الموبايل) وكتابتها في Laravel log
     */
    public function logClientError(Request $request)
    {
        try {
            $payload = $request->all();
            
            // التحقق من صحة البيانات
            $validator = Validator::make($payload, [
                'message' => 'required|string|max:1000',
                'context' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                Log::warning('[Mobile Employee Login] Invalid client error payload', [
                    'errors' => $validator->errors()->toArray(),
                    'payload' => $payload,
                    'ip' => $request->ip(),
                ]);
                return response()->json(['ok' => false, 'message' => 'Invalid payload'], 400);
            }

            // تسجيل الخطأ
            Log::error('[Mobile Employee Login] Client error', [
                'message' => $payload['message'],
                'context' => $payload['context'] ?? [],
                'ip' => $request->ip(),
                'ua' => $request->userAgent(),
                'timestamp' => now()->toISOString(),
            ]);

            return response()->json(['ok' => true]);
            
        } catch (\Exception $e) {
            Log::error('[Mobile Employee Login] Exception in logClientError', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['ok' => false, 'message' => 'Internal error'], 500);
        }
    }
}
