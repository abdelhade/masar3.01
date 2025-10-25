<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MobileAttendanceController extends Controller
{
    /**
     * تسجيل بصمة جديدة من الموبايل
     */
    public function recordAttendance(Request $request)
    {
        try {
            // الحصول على بيانات الموظف من الجلسة
            $employeeId = $request->get('current_employee_id');
            $fingerPrintId = $request->get('current_employee_finger_print_id');
            $fingerPrintName = $request->get('current_employee_finger_print_name');

            if (!$employeeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مسجل دخول. يرجى تسجيل الدخول أولاً'
                ], 401);
            }

            // التحقق من صحة البيانات
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:check_in,check_out',
                'location' => 'nullable|string',
                'notes' => 'nullable|string|max:500',
                'project_code' => 'nullable|string|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'بيانات غير صحيحة',
                    'errors' => $validator->errors()
                ], 422);
            }

            // التحقق من وجود الموظف
            $employee = Employee::find($employeeId);
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'الموظف غير موجود'
                ], 404);
            }

            // استخدام وقت السيرفر بدلاً من وقت الجهاز
            $serverTime = Carbon::now(config('app.timezone'));
            $serverDate = $serverTime->format('Y-m-d');
            $serverTimeFormatted = $serverTime->format('H:i');

            // تحويل location من JSON string إلى array
            $locationData = null;
            if ($request->location) {
                $decoded = json_decode($request->location, true);
                if ($decoded !== null) {
                    // التحقق من صحة بيانات الموقع
                    if (isset($decoded['latitude']) && isset($decoded['longitude'])) {
                        // تقريب الإحداثيات لتقليل التباين
                        $decoded['latitude'] = round($decoded['latitude'], 6);
                        $decoded['longitude'] = round($decoded['longitude'], 6);
                        
                        // إضافة معلومات إضافية
                        $decoded['captured_at'] = now()->toISOString();
                        $decoded['source'] = 'mobile_gps';
                        
                        $locationData = $decoded;
                    } else {
                        $locationData = $request->location;
                    }
                } else {
                    // إذا فشل التحويل، احفظ كـ string
                    $locationData = $request->location;
                }
            }

            // إنشاء سجل الحضور
            $attendanceData = [
                'employee_id' => $employeeId,
                'employee_attendance_finger_print_id' => $fingerPrintId,
                'employee_attendance_finger_print_name' => $fingerPrintName,
                'type' => $request->type,
                'date' => $serverDate, // استخدام تاريخ السيرفر
                'time' => $serverTimeFormatted, // استخدام وقت السيرفر
                'location' => $locationData,
                'status' => 'pending', // افتراضياً قيد المراجعة
                'notes' => $request->notes,
                'project_code' => $request->project_code, // إضافة كود المشروع
                'user_id' => null // لا يوجد user للموظفين
            ];

            // التحقق من اتصال قاعدة البيانات
            try {
                DB::connection()->getPdo();
            } catch (\Exception $dbException) {
                throw $dbException;
            }

            $attendance = Attendance::create($attendanceData);

            // إرجاع النتيجة
            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل البصمة بنجاح',
                'data' => [
                    'id' => $attendance->id,
                    'type' => $attendance->type,
                    'date' => $attendance->date,
                    'time' => $attendance->time,
                    'status' => $attendance->status,
                    'location' => $attendance->location,
                    'project_code' => $attendance->project_code
                ]
            ], 201);

        } catch (\Exception $e) {
            // تحديد نوع الخطأ وإعطاء رسالة مناسبة
            $errorMessage = 'حدث خطأ في تسجيل البصمة';
            if (strpos($e->getMessage(), 'Data too long') !== false) {
                $errorMessage = 'البيانات المرسلة كبيرة جداً. يرجى المحاولة مرة أخرى';
            } elseif (strpos($e->getMessage(), 'SQLSTATE') !== false) {
                $errorMessage = 'خطأ في قاعدة البيانات. يرجى المحاولة مرة أخرى';
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 500);
        }
    }

    /**
     * الحصول على آخر بصمة للموظف
     */
    public function getLastAttendance(Request $request)
    {
        try {
            $employeeId = $request->get('current_employee_id');
            
            if (!$employeeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مسجل دخول. يرجى تسجيل الدخول أولاً'
                ], 401);
            }
            
            $lastAttendance = Attendance::where('employee_id', $employeeId)
                ->latest()
                ->first();

            if (!$lastAttendance) {
                return response()->json([
                    'success' => true,
                    'message' => 'لا توجد بصمات سابقة',
                    'data' => null
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $lastAttendance->id,
                    'type' => $lastAttendance->type,
                    'date' => $lastAttendance->date,
                    'time' => $lastAttendance->time,
                    'status' => $lastAttendance->status,
                    'location' => $lastAttendance->location,
                    'notes' => $lastAttendance->notes,
                    'project_code' => $lastAttendance->project_code
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب آخر بصمة'
            ], 500);
        }
    }

    /**
     * الحصول على تاريخ البصمات للموظف
     */
    public function getAttendanceHistory(Request $request)
    {
        try {
            $employeeId = $request->get('current_employee_id');
            
            if (!$employeeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مسجل دخول. يرجى تسجيل الدخول أولاً'
                ], 401);
            }
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            $limit = $request->get('limit', 50);

            $query = Attendance::where('employee_id', $employeeId);

            // فلترة حسب التاريخ
            if ($dateFrom && $dateTo) {
                $query->whereBetween('date', [$dateFrom, $dateTo]);
            } elseif ($dateFrom) {
                $query->where('date', '>=', $dateFrom);
            } elseif ($dateTo) {
                $query->where('date', '<=', $dateTo);
            }

            $attendances = $query->orderBy('date', 'desc')
                ->orderBy('time', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $attendances->map(function ($attendance) {
                    return [
                        'id' => $attendance->id,
                        'type' => $attendance->type,
                        'date' => $attendance->date,
                        'time' => $attendance->time,
                        'status' => $attendance->status,
                        'location' => $attendance->location,
                        'notes' => $attendance->notes,
                        'project_code' => $attendance->project_code
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب تاريخ البصمات'
            ], 500);
        }
    }

    /**
     * الحصول على إحصائيات البصمات للموظف
     */
    public function getAttendanceStats(Request $request)
    {
        try {
            $employeeId = $request->get('current_employee_id');
            
            if (!$employeeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مسجل دخول. يرجى تسجيل الدخول أولاً'
                ], 401);
            }
            $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

            $stats = [
                'total_attendances' => Attendance::where('employee_id', $employeeId)
                    ->whereBetween('date', [$dateFrom, $dateTo])
                    ->count(),
                'check_ins' => Attendance::where('employee_id', $employeeId)
                    ->where('type', 'check_in')
                    ->whereBetween('date', [$dateFrom, $dateTo])
                    ->count(),
                'check_outs' => Attendance::where('employee_id', $employeeId)
                    ->where('type', 'check_out')
                    ->whereBetween('date', [$dateFrom, $dateTo])
                    ->count(),
                'pending' => Attendance::where('employee_id', $employeeId)
                    ->where('status', 'pending')
                    ->whereBetween('date', [$dateFrom, $dateTo])
                    ->count(),
                'approved' => Attendance::where('employee_id', $employeeId)
                    ->where('status', 'approved')
                    ->whereBetween('date', [$dateFrom, $dateTo])
                    ->count(),
                'rejected' => Attendance::where('employee_id', $employeeId)
                    ->where('status', 'rejected')
                    ->whereBetween('date', [$dateFrom, $dateTo])
                    ->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب إحصائيات البصمات'
            ], 500);
        }
    }

    /**
     * التحقق من إمكانية تسجيل البصمة
     */
    public function canRecordAttendance(Request $request)
    {
        try {
            $employeeId = $request->get('current_employee_id');
            
            if (!$employeeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مسجل دخول. يرجى تسجيل الدخول أولاً'
                ], 401);
            }
            $type = $request->get('type', 'check_in');
            $today = Carbon::now(config('app.timezone'))->format('Y-m-d');

            // التحقق من آخر بصمة اليوم
            $lastAttendance = Attendance::where('employee_id', $employeeId)
                ->where('date', $today)
                ->latest()
                ->first();

            $canRecord = true;
            $message = 'يمكن تسجيل البصمة';
            $lastType = null;

            if ($lastAttendance) {
                $lastType = $lastAttendance->type;
                
                // إذا كان آخر نوع هو نفس النوع المطلوب
                if ($lastType === $type) {
                    $canRecord = false;
                    $message = $type === 'check_in' 
                        ? 'تم تسجيل دخول اليوم بالفعل' 
                        : 'تم تسجيل خروج اليوم بالفعل';
                }
            } else {
                // إذا لم توجد بصمات اليوم وكان النوع خروج
                if ($type === 'check_out') {
                    $canRecord = false;
                    $message = 'يجب تسجيل دخول أولاً';
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'can_record' => $canRecord,
                    'message' => $message,
                    'last_type' => $lastType,
                    'last_attendance' => $lastAttendance ? [
                        'id' => $lastAttendance->id,
                        'type' => $lastAttendance->type,
                        'time' => $lastAttendance->time,
                        'status' => $lastAttendance->status
                    ] : null
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في التحقق من إمكانية تسجيل البصمة'
            ], 500);
        }
    }
}
