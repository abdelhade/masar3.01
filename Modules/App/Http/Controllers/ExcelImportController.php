<?php

namespace Modules\App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
// use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelImportController extends Controller
{
    public function preview(Request $request, $model)
    {
        $file = $request->file('file');

        if (!$file) {
            return response()->json(['success' => false, 'error' => 'الملف غير موجود'], 400);
        }

        try {
            // قراءة محتوى الشيت
            // $spreadsheet = IOFactory::load($file->getRealPath());
            // $sheet = $spreadsheet->getActiveSheet();
            // $data = $sheet->toArray();
            $data = []; // Placeholder - PhpSpreadsheet not available

            // أخذ أول 6 صفوف (الهيدر + 5 صفوف)
            $rows = array_slice($data, 0, 6);

            return response()->json(['success' => true, 'data' => $rows]);
        } catch (\Exception) {
            return response()->json(['success' => false, 'error' => 'خطأ في قراءة الملف: '], 500);
        }
    }

    // الاستيراد الفعلي للبيانات داخل الموديل
    public function import(Request $request, $model)
    {
        $className = 'App\\Models\\' . Str::studly($model);

        if (!class_exists($className)) {
            return response()->json(['success' => false, 'error' => 'الموديل غير موجود: ' . $className], 404);
        }

        try {
            $file = $request->file('file');
            $mapping = json_decode($request->input('mapping', '{}'), true);
            $validationRules = json_decode($request->input('validation_rules', '{}'), true);

            if (!$file) {
                return response()->json(['success' => false, 'error' => 'الملف غير موجود'], 400);
            }
            // قراءة محتوى الشيت
            // $spreadsheet = IOFactory::load($file->getRealPath());
            // $sheet = $spreadsheet->getActiveSheet();
            // $data = $sheet->toArray();
            $data = []; // Placeholder - PhpSpreadsheet not available

            // أول صف هو الهيدر
            $headers = array_shift($data);

            $success = 0;
            $failed = 0;
            $errors = [];

            foreach ($data as $index => $row) {
                // تخطي الصفوف الفارغة
                if (empty(array_filter($row))) {
                    continue;
                }

                // تحويل الصف إلى array associative
                $rowData = [];
                foreach ($headers as $colIndex => $header) {
                    if (isset($mapping[$header])) {
                        $dbColumn = $mapping[$header];
                        $value = $row[$colIndex] ?? null;
                        // تنظيف القيم الفارغة
                        $rowData[$dbColumn] = ($value !== '' && $value !== null) ? $value : null;
                    }
                }

                // تخطي الصف إذا كان فارغاً بعد المعالجة
                if (empty(array_filter($rowData))) {
                    continue;
                }

                // إضافة branch_id و tenant تلقائياً إذا كان المستخدم مسجل دخول
                if (Auth::check()) {
                    if (!isset($rowData['branch_id']) && Auth::user()->branch_id) {
                        $rowData['branch_id'] = Auth::user()->branch_id;
                    }
                    if (!isset($rowData['tenant']) && Auth::user()->tenant) {
                        $rowData['tenant'] = Auth::user()->tenant;
                    }
                }

                // إضافة timestamps
                $rowData['created_at'] = now();
                $rowData['updated_at'] = now();

                // التحقق من الصحة (إذا كانت موجودة)
                if (!empty($validationRules)) {
                    $validator = Validator::make($rowData, $validationRules);
                    if ($validator->fails()) {
                        $failed++;
                        $errors[] = [
                            'row' => $index + 2, // +2 لأننا حذفنا الهيدر وبدأنا من 0
                            'errors' => $validator->errors()->all()
                        ];
                        continue;
                    }
                }

                try {
                    $className::create($rowData);
                    $success++;
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = [
                        'row' => $index + 2,
                        'message' => $e->getMessage()
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'results' => [
                    'success' => $success,
                    'failed' => $failed,
                    'errors' => $errors
                ]
            ]);
        } catch (\Exception) {
            return response()->json([
                'success' => false,
                'error' => 'خطأ في الاستيراد: '
            ], 500);
        }
    }

    // تحميل Template
    public function template(Request $request)
    {
        // PhpSpreadsheet functionality disabled due to dependency issues
        return response()->json(['success' => false, 'error' => 'Excel functionality temporarily disabled'], 503);
        
        /* Original code commented out due to PhpSpreadsheet dependency issues
        try {
            $headers = json_decode($request->input('headers', '[]'), true);
            $filename = $request->input('filename', 'template.xlsx');

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // كتابة الهيدر
            foreach ($headers as $index => $header) {
                $sheet->setCellValueByColumnAndRow($index + 1, 1, $header);
            }

            // تنسيق الهيدر
            $headerStyle = $sheet->getStyle('A1:' . chr(64 + count($headers)) . '1');
            $headerStyle->getFont()->setBold(true);
            $headerStyle->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE0E0E0');

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;
        } catch (\Exception) {
            return response()->json(['success' => false, 'error' => 'خطأ في إنشاء Template: '], 500);
        }
        */
    }
}
