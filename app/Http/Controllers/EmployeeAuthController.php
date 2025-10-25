<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

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
            ], [
                'finger_print_id.required' => 'رقم البصمة مطلوب',
                'finger_print_id.integer' => 'رقم البصمة يجب أن يكون رقماً صحيحاً',
                'finger_print_name.required' => 'اسم البصمة مطلوب',
                'finger_print_name.string' => 'اسم البصمة يجب أن يكون نصاً',
                'finger_print_name.max' => 'اسم البصمة طويل جداً',
                'password.required' => 'كلمة المرور مطلوبة',
                'password.string' => 'كلمة المرور يجب أن تكون نصاً'
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorMessages = [];
                
                if ($errors->has('finger_print_id')) {
                    $errorMessages[] = $errors->first('finger_print_id');
                }
                if ($errors->has('finger_print_name')) {
                    $errorMessages[] = $errors->first('finger_print_name');
                }
                if ($errors->has('password')) {
                    $errorMessages[] = $errors->first('password');
                }
                
                return response()->json([
                    'success' => false,
                    'message' => implode(' - ', $errorMessages),
                    'errors' => $validator->errors()
                ], 422);
            }

            // البحث عن الموظف
            $employee = Employee::where('finger_print_id', $request->finger_print_id)
                                ->where('finger_print_name', $request->finger_print_name)
                                ->with(['department', 'job', 'shift'])
                                ->first();

            if (!$employee) {
                // التحقق من وجود رقم البصمة فقط
                $employeeByFingerId = Employee::where('finger_print_id', $request->finger_print_id)->first();
                
                if ($employeeByFingerId) {
                    // رقم البصمة صحيح لكن اسم البصمة خطأ
                    return response()->json([
                        'success' => false,
                        'message' => 'اسم البصمة غير صحيح. يرجى التحقق من اسم البصمة المدخل',
                        'error_type' => 'finger_print_name'
                    ], 401);
                } else {
                    // رقم البصمة خطأ
                    return response()->json([
                        'success' => false,
                        'message' => 'رقم البصمة غير صحيح. يرجى التحقق من رقم البصمة المدخل',
                        'error_type' => 'finger_print_id'
                    ], 401);
                }
            }

            // التحقق من حالة الموظف
            if ($employee->status !== 'مفعل') {
                $statusMessage = $employee->status === 'معطل' 
                    ? 'حساب الموظف معطل. يرجى التواصل مع الإدارة لتفعيل الحساب'
                    : 'حساب الموظف غير مفعل. يرجى التواصل مع الإدارة';
                    
                return response()->json([
                    'success' => false,
                    'message' => $statusMessage,
                    'error_type' => 'account_disabled',
                    'employee_status' => $employee->status
                ], 403);
            }

            // التحقق من كلمة المرور
            if (!$employee->password) {
                return response()->json([
                    'success' => false,
                    'message' => 'لم يتم تعيين كلمة مرور لهذا الموظف. يرجى التواصل مع الإدارة',
                    'error_type' => 'no_password'
                ], 401);
            }
            
            if (!Hash::check($request->password, $employee->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'كلمة المرور غير صحيحة. يرجى التحقق من كلمة المرور المدخلة',
                    'error_type' => 'wrong_password'
                ], 401);
            }

            // تسجيل دخول الموظف في الجلسة
            Session::put('employee_id', $employee->id);
            Session::put('employee_name', $employee->name);
            Session::put('employee_finger_print_id', $employee->finger_print_id);
            Session::put('employee_finger_print_name', $employee->finger_print_name);
            Session::put('employee_logged_in', true);

            // إرجاع بيانات الموظف
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

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل الخروج بنجاح'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تسجيل الخروج'
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
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في التحقق من تسجيل الدخول'
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
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب بيانات الموظف'
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
                return response()->json(['ok' => false, 'message' => 'Invalid payload'], 400);
            }

            return response()->json(['ok' => true]);

        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'message' => 'Internal error'], 500);
        }
    }
}
