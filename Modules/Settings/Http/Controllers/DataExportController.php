<?php

namespace Modules\Settings\Http\Controllers;

use ZipArchive;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DataExportController extends Controller
{
    public function exportAllData()
    {
        try {
            $zipFileName = 'erp_data_export_' . date('Y-m-d_H-i-s') . '.zip';
            $zipPath = storage_path('app/temp/' . $zipFileName);

            // التأكد من وجود مجلد temp
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0777, true);
            }

            $zip = new ZipArchive;

            if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {

                // الحصول على جميع أسماء الجداول
                $tables = DB::select('SHOW TABLES');
                $databaseName = DB::getDatabaseName();
                $tableKey = 'Tables_in_' . $databaseName;

                foreach ($tables as $table) {
                    $tableName = $table->$tableKey;

                    // تخطي جداول Laravel الداخلية إذا أردت
                    if (in_array($tableName, ['migrations', 'password_resets', 'failed_jobs'])) {
                        continue;
                    }

                    // الحصول على البيانات من كل جدول
                    $data = DB::table($tableName)->get();

                    // تحويل البيانات إلى JSON
                    $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

                    // إضافة ملف JSON للـ ZIP
                    $zip->addFromString($tableName . '.json', $jsonData);

                    // أو يمكنك تصديرها كـ CSV
                    if (!$data->isEmpty()) {
                        $csvData = $this->arrayToCsv($data->toArray());
                        $zip->addFromString($tableName . '.csv', $csvData);
                    }
                }

                // إضافة ملف معلومات التصدير
                $exportInfo = [
                    'export_date' => now()->toDateTimeString(),
                    'database_name' => $databaseName,
                    'laravel_version' => app()->version(),
                    'total_tables' => count($tables),
                ];

                $zip->addFromString('export_info.json', json_encode($exportInfo, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

                $zip->close();

                // تنزيل الملف
                return response()->download($zipPath)->deleteFileAfterSend(true);
            } else {
                return response()->json(['error' => 'فشل في إنشاء ملف ZIP'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'حدث خطأ: ' . $e->getMessage()], 500);
        }
    }

    // طريقة بديلة: تصدير قاعدة البيانات كـ SQL dump
    public function exportSqlDump()
    {
        try {
            $filename = 'erp_database_' . date('Y-m-d_H-i-s') . '.sql';
            $filePath = storage_path('app/temp/' . $filename);

            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0777, true);
            }

            $tables = DB::select('SHOW TABLES');
            $databaseName = DB::getDatabaseName();
            $tableKey = 'Tables_in_' . $databaseName;

            $sqlDump = "-- Database Export: {$databaseName}\n";
            $sqlDump .= "-- Date: " . now()->toDateTimeString() . "\n\n";

            foreach ($tables as $table) {
                $tableName = $table->$tableKey;

                // تخطي جداول Laravel الداخلية
                if (in_array($tableName, ['migrations', 'failed_jobs', 'password_resets'])) {
                    continue;
                }

                // جلب بيانات الجدول
                $rows = DB::table($tableName)->get();

                if ($rows->isEmpty()) {
                    continue;
                }

                foreach ($rows as $row) {
                    $values = array_map(function ($value) {
                        return is_null($value) ? 'NULL' : "'" . str_replace("'", "''", $value) . "'";
                    }, (array) $row);

                    $sqlDump .= "INSERT INTO `{$tableName}` (`" . implode('`,`', array_keys((array) $row)) . "`) VALUES (" . implode(',', $values) . ");\n";
                }

                $sqlDump .= "\n";
            }

            file_put_contents($filePath, $sqlDump);

            return response()->download($filePath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json(['error' => 'حدث خطأ: ' . $e->getMessage()], 500);
        }
    }


    // دالة مساعدة لتحويل المصفوفة إلى CSV
    private function arrayToCsv($array)
    {
        if (empty($array)) {
            return '';
        }

        $output = fopen('php://temp', 'r+');

        // كتابة العناوين
        $headers = array_keys((array) $array[0]);
        fputcsv($output, $headers);

        // كتابة البيانات
        foreach ($array as $row) {
            fputcsv($output, array_values((array) $row));
        }

        rewind($output);
        $csvData = stream_get_contents($output);
        fclose($output);

        return $csvData;
    }
}
