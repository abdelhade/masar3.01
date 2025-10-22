<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
            // تسجيل بداية العملية
            Log::info('MobileAttendance: بدء تسجيل البصمة', [
                'request_data' => $request->all(),
                'headers' => $request->headers->all(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // الحصول على بيانات الموظف من الجلسة
            $employeeId = $request->get('current_employee_id');
            $fingerPrintId = $request->get('current_employee_finger_print_id');
            $fingerPrintName = $request->get('current_employee_finger_print_name');

            Log::info('MobileAttendance: بيانات الموظف المستخرجة', [
                'employee_id' => $employeeId,
                'finger_print_id' => $fingerPrintId,
                'finger_print_name' => $fingerPrintName
            ]);

            if (!$employeeId) {
                Log::warning('MobileAttendance: محاولة تسجيل بصمة بدون employee_id');
                return response()->json([
                    'success' => false,
                    'message' => 'غير مسجل دخول. يرجى تسجيل الدخول أولاً'
                ], 401);
            }

            // التحقق من صحة البيانات
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:check_in,check_out',
                'location' => 'nullable|string',
                'notes' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                Log::warning('MobileAttendance: فشل في التحقق من صحة البيانات', [
                    'errors' => $validator->errors()->toArray(),
                    'input_data' => $request->all()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'بيانات غير صحيحة',
                    'errors' => $validator->errors()
                ], 422);
            }

            Log::info('MobileAttendance: نجح التحقق من صحة البيانات');

            // التحقق من وجود الموظف
            $employee = Employee::find($employeeId);
            if (!$employee) {
                Log::error('MobileAttendance: الموظف غير موجود', [
                    'employee_id' => $employeeId
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'الموظف غير موجود'
                ], 404);
            }

            Log::info('MobileAttendance: تم العثور على الموظف', [
                'employee_id' => $employee->id,
                'employee_name' => $employee->name ?? 'غير محدد'
            ]);

            // استخدام وقت السيرفر بدلاً من وقت الجهاز
            $serverTime = Carbon::now(config('app.timezone'));
            $serverDate = $serverTime->format('Y-m-d');
            $serverTimeFormatted = $serverTime->format('H:i');

            Log::info('MobileAttendance: بيانات الوقت', [
                'server_time' => $serverTime->toDateTimeString(),
                'server_date' => $serverDate,
                'server_time_formatted' => $serverTimeFormatted,
                'timezone' => config('app.timezone')
            ]);

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

            Log::info('MobileAttendance: بيانات الموقع', [
                'original_location' => $request->location,
                'processed_location' => $locationData
            ]);

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
                'user_id' => null // لا يوجد user للموظفين
            ];

            Log::info('MobileAttendance: محاولة إنشاء سجل الحضور', [
                'attendance_data' => $attendanceData,
                'location_data_size' => $locationData ? strlen(json_encode($locationData)) : 0
            ]);

            // التحقق من اتصال قاعدة البيانات
            try {
                DB::connection()->getPdo();
                Log::info('MobileAttendance: اتصال قاعدة البيانات متاح');
                
                // التحقق من وجود جدول Attendance
                $tableExists = Schema::hasTable('attendances');
                Log::info('MobileAttendance: حالة جدول attendances', [
                    'table_exists' => $tableExists
                ]);
                
                if ($tableExists) {
                    // التحقق من بنية الجدول
                    $columns = Schema::getColumnListing('attendances');
                    Log::info('MobileAttendance: أعمدة جدول attendances', [
                        'columns' => $columns
                    ]);
                }
                
            } catch (\Exception $dbException) {
                Log::error('MobileAttendance: مشكلة في اتصال قاعدة البيانات', [
                    'error' => $dbException->getMessage()
                ]);
                throw $dbException;
            }

            $attendance = Attendance::create($attendanceData);

            Log::info('MobileAttendance: تم إنشاء سجل الحضور بنجاح', [
                'attendance_id' => $attendance->id,
                'attendance_type' => $attendance->type,
                'attendance_date' => $attendance->date,
                'attendance_time' => $attendance->time
            ]);

            // إرجاع النتيجة
            $response = [
                'success' => true,
                'message' => 'تم تسجيل البصمة بنجاح',
                'data' => [
                    'id' => $attendance->id,
                    'type' => $attendance->type,
                    'date' => $attendance->date,
                    'time' => $attendance->time,
                    'status' => $attendance->status,
                    'location' => $attendance->location
                ]
            ];

            Log::info('MobileAttendance: تم إرجاع النتيجة بنجاح', [
                'response' => $response
            ]);

            return response()->json($response, 201);

        } catch (\Exception $e) {
            Log::error('MobileAttendance: حدث خطأ في تسجيل البصمة', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            // تحديد نوع الخطأ وإعطاء رسالة مناسبة
            $errorMessage = 'حدث خطأ في تسجيل البصمة';
            if (strpos($e->getMessage(), 'Data too long') !== false) {
                $errorMessage = 'البيانات المرسلة كبيرة جداً. يرجى المحاولة مرة أخرى';
            } elseif (strpos($e->getMessage(), 'SQLSTATE') !== false) {
                $errorMessage = 'خطأ في قاعدة البيانات. يرجى المحاولة مرة أخرى';
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'error' => config('app.debug') ? $e->getMessage() : 'خطأ داخلي في الخادم'
            ], 500);
        }
    }

    /**
     * الحصول على آخر بصمة للموظف
     */
    public function getLastAttendance(Request $request)
    {
        try {
            Log::info('MobileAttendance: طلب آخر بصمة', [
                'request_data' => $request->all()
            ]);

            $employeeId = $request->get('current_employee_id');
            
            if (!$employeeId) {
                Log::warning('MobileAttendance: محاولة جلب آخر بصمة بدون employee_id');
                return response()->json([
                    'success' => false,
                    'message' => 'غير مسجل دخول. يرجى تسجيل الدخول أولاً'
                ], 401);
            }
            
            $lastAttendance = Attendance::where('employee_id', $employeeId)
                ->latest()
                ->first();

            if (!$lastAttendance) {
                Log::info('MobileAttendance: لا توجد بصمات سابقة للموظف', [
                    'employee_id' => $employeeId
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'لا توجد بصمات سابقة',
                    'data' => null
                ]);
            }

            Log::info('MobileAttendance: تم العثور على آخر بصمة', [
                'employee_id' => $employeeId,
                'last_attendance_id' => $lastAttendance->id,
                'last_attendance_type' => $lastAttendance->type,
                'last_attendance_date' => $lastAttendance->date,
                'last_attendance_time' => $lastAttendance->time
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $lastAttendance->id,
                    'type' => $lastAttendance->type,
                    'date' => $lastAttendance->date,
                    'time' => $lastAttendance->time,
                    'status' => $lastAttendance->status,
                    'location' => $lastAttendance->location,
                    'notes' => $lastAttendance->notes
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('MobileAttendance: حدث خطأ في جلب آخر بصمة', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب آخر بصمة',
                'error' => $e->getMessage()
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
                        'notes' => $attendance->notes
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب تاريخ البصمات',
                'error' => $e->getMessage()
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
                'message' => 'حدث خطأ في جلب إحصائيات البصمات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * التحقق من إمكانية تسجيل البصمة
     */
    public function canRecordAttendance(Request $request)
    {
        try {
            Log::info('MobileAttendance: التحقق من إمكانية تسجيل البصمة', [
                'request_data' => $request->all()
            ]);

            $employeeId = $request->get('current_employee_id');
            
            if (!$employeeId) {
                Log::warning('MobileAttendance: محاولة التحقق من إمكانية تسجيل البصمة بدون employee_id');
                return response()->json([
                    'success' => false,
                    'message' => 'غير مسجل دخول. يرجى تسجيل الدخول أولاً'
                ], 401);
            }
            $type = $request->get('type', 'check_in');
            $today = Carbon::now(config('app.timezone'))->format('Y-m-d');

            Log::info('MobileAttendance: بيانات التحقق', [
                'employee_id' => $employeeId,
                'type' => $type,
                'today' => $today
            ]);

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
                
                Log::info('MobileAttendance: تم العثور على آخر بصمة اليوم', [
                    'last_attendance_id' => $lastAttendance->id,
                    'last_type' => $lastType,
                    'last_time' => $lastAttendance->time
                ]);
                
                // إذا كان آخر نوع هو نفس النوع المطلوب
                if ($lastType === $type) {
                    $canRecord = false;
                    $message = $type === 'check_in' 
                        ? 'تم تسجيل دخول اليوم بالفعل' 
                        : 'تم تسجيل خروج اليوم بالفعل';
                }
            } else {
                Log::info('MobileAttendance: لا توجد بصمات اليوم');
                // إذا لم توجد بصمات اليوم وكان النوع خروج
                if ($type === 'check_out') {
                    $canRecord = false;
                    $message = 'يجب تسجيل دخول أولاً';
                }
            }

            Log::info('MobileAttendance: نتيجة التحقق', [
                'can_record' => $canRecord,
                'message' => $message,
                'last_type' => $lastType
            ]);

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
            Log::error('MobileAttendance: حدث خطأ في التحقق من إمكانية تسجيل البصمة', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في التحقق من إمكانية تسجيل البصمة',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
