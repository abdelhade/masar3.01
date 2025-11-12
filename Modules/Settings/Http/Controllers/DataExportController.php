<?php

namespace Modules\Settings\Http\Controllers;

use ZipArchive;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use RealRashid\SweetAlert\Facades\Alert;

class DataExportController extends Controller
{
    /**
     * Export database as proper SQL dump using mysqldump
     */
    public function exportSqlDump()
    {
        try {
            $filename = 'erp_database_' . date('Y-m-d_H-i-s') . '.sql';
            $filePath = storage_path('app/temp/' . $filename);

            // التأكد من وجود مجلد temp
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0777, true);
            }

            // الحصول على بيانات الاتصال بقاعدة البيانات
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port', 3306);

            // بناء أمر mysqldump
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s --port=%s --single-transaction --routines --triggers --events --add-drop-database --databases %s --result-file=%s 2>&1',
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($database),
                escapeshellarg($filePath)
            );

            // تنفيذ الأمر
            exec($command, $output, $returnVar);

            // التحقق من نجاح التنفيذ
            if ($returnVar !== 0 || !file_exists($filePath)) {
                \Log::error('mysqldump failed', [
                    'output' => $output,
                    'return_var' => $returnVar
                ]);

                // Fallback: استخدم PHP إذا mysqldump غير متاح
                return $this->exportSqlDumpFallback();
            }

            // إضافة معلومات إضافية في بداية الملف
            $header = $this->generateSqlHeader();
            $content = file_get_contents($filePath);
            file_put_contents($filePath, $header . "\n\n" . $content);

            return response()->download($filePath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            \Log::error('Export SQL Error: ' . $e->getMessage());
            Alert::toast('حدث خطأ: ' . $e->getMessage(), 'error');
            return back();
        }
    }

    /**
     * Fallback method if mysqldump is not available
     */
    private function exportSqlDumpFallback()
    {
        try {
            $filename = 'erp_database_fallback_' . date('Y-m-d_H-i-s') . '.sql';
            $filePath = storage_path('app/temp/' . $filename);

            $database = config('database.connections.mysql.database');

            // Header
            $sqlDump = $this->generateSqlHeader();
            $sqlDump .= "\n\nSET FOREIGN_KEY_CHECKS=0;\n";
            $sqlDump .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
            $sqlDump .= "START TRANSACTION;\n";
            $sqlDump .= "SET time_zone = '+00:00';\n\n";

            // Drop and create database
            $sqlDump .= "DROP DATABASE IF EXISTS `{$database}`;\n";
            $sqlDump .= "CREATE DATABASE IF NOT EXISTS `{$database}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
            $sqlDump .= "USE `{$database}`;\n\n";

            // Get all tables
            $tables = DB::select('SHOW TABLES');
            $tableKey = 'Tables_in_' . $database;

            foreach ($tables as $table) {
                $tableName = $table->$tableKey;

                // Skip Laravel internal tables
                if (in_array($tableName, ['migrations', 'failed_jobs', 'password_resets', 'personal_access_tokens'])) {
                    continue;
                }

                // Get CREATE TABLE statement
                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                $sqlDump .= "\n-- Table: {$tableName}\n";
                $sqlDump .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $sqlDump .= $createTable[0]->{'Create Table'} . ";\n\n";

                // Get data
                $rows = DB::table($tableName)->get();

                if ($rows->isNotEmpty()) {
                    $sqlDump .= "-- Data for table: {$tableName}\n";

                    foreach ($rows as $row) {
                        $columns = array_keys((array) $row);
                        $values = array_map(function ($value) {
                            if (is_null($value)) {
                                return 'NULL';
                            }
                            return "'" . addslashes($value) . "'";
                        }, array_values((array) $row));

                        $sqlDump .= "INSERT INTO `{$tableName}` (`" .
                            implode('`, `', $columns) .
                            "`) VALUES (" .
                            implode(', ', $values) .
                            ");\n";
                    }
                    $sqlDump .= "\n";
                }
            }

            // Footer
            $sqlDump .= "\nSET FOREIGN_KEY_CHECKS=1;\n";
            $sqlDump .= "COMMIT;\n";

            file_put_contents($filePath, $sqlDump);

            return response()->download($filePath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            \Log::error('Fallback Export Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Export data as JSON/CSV (للأرشفة فقط، مش للـ import كامل)
     */
    public function exportAllData()
    {
        try {
            $zipFileName = 'erp_data_export_' . date('Y-m-d_H-i-s') . '.zip';
            $zipPath = storage_path('app/temp/' . $zipFileName);

            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0777, true);
            }

            $zip = new ZipArchive;

            if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                $tables = DB::select('SHOW TABLES');
                $databaseName = DB::getDatabaseName();
                $tableKey = 'Tables_in_' . $databaseName;

                foreach ($tables as $table) {
                    $tableName = $table->$tableKey;

                    if (in_array($tableName, ['migrations', 'password_resets', 'failed_jobs'])) {
                        continue;
                    }

                    $data = DB::table($tableName)->get();

                    // JSON
                    $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                    $zip->addFromString($tableName . '.json', $jsonData);

                    // CSV
                    if (!$data->isEmpty()) {
                        $csvData = $this->arrayToCsv($data->toArray());
                        $zip->addFromString($tableName . '.csv', $csvData);
                    }
                }

                // Export info
                $exportInfo = [
                    'export_date' => now()->toDateTimeString(),
                    'database_name' => $databaseName,
                    'laravel_version' => app()->version(),
                    'php_version' => PHP_VERSION,
                    'total_tables' => count($tables),
                    'warning' => 'This is DATA ONLY export. To restore full database structure, use SQL dump instead.',
                ];

                $zip->addFromString(
                    'README.txt',
                    "⚠️ هذا الملف يحتوي على البيانات فقط (JSON/CSV)\n\n" .
                        "لاستعادة قاعدة البيانات كاملة (structure + data)، استخدم خيار SQL Database Export\n\n" .
                        "Export Date: " . $exportInfo['export_date'] . "\n" .
                        "Database: " . $exportInfo['database_name'] . "\n" .
                        "Laravel: " . $exportInfo['laravel_version']
                );

                $zip->addFromString('export_info.json', json_encode($exportInfo, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

                $zip->close();

                return response()->download($zipPath)->deleteFileAfterSend(true);
            } else {
                throw new \Exception('فشل في إنشاء ملف ZIP');
            }
        } catch (\Exception $e) {
            \Log::error('Export Data Error: ' . $e->getMessage());
            Alert::toast('حدث خطأ: ' . $e->getMessage(), 'error');
            return back();
        }
    }

    /**
     * Generate SQL file header
     */
    private function generateSqlHeader()
    {
        $database = config('database.connections.mysql.database');
        $projectUrl = url('/');
        $exportDate = now()->format('Y-m-d H:i:s');

        return "-- =============================================\n" .
            "-- ERP Database Backup\n" .
            "-- =============================================\n" .
            "-- Database: {$database}\n" .
            "-- Export Date: {$exportDate}\n" .
            "-- Project URL: {$projectUrl}\n" .
            "-- Laravel Version: " . app()->version() . "\n" .
            "-- PHP Version: " . PHP_VERSION . "\n" .
            "-- =============================================\n" .
            "-- \n" .
            "-- Instructions:\n" .
            "-- 1. Create a new database or drop existing one\n" .
            "-- 2. Import this file: mysql -u user -p database < file.sql\n" .
            "-- 3. Update .env file with database credentials\n" .
            "-- =============================================\n";
    }

    /**
     * Convert array to CSV
     */
    private function arrayToCsv($array)
    {
        if (empty($array)) {
            return '';
        }

        $output = fopen('php://temp', 'r+');
        $headers = array_keys((array) $array[0]);
        fputcsv($output, $headers);

        foreach ($array as $row) {
            fputcsv($output, array_values((array) $row));
        }

        rewind($output);
        $csvData = stream_get_contents($output);
        fclose($output);

        return $csvData;
    }

    /**
     * Get database statistics
     */
    public function getExportStats()
    {
        try {
            $database = config('database.connections.mysql.database');

            $tableCount = DB::select("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = ?", [$database])[0]->count;

            $recordCount = 0;
            $tables = DB::select('SHOW TABLES');
            $tableKey = 'Tables_in_' . $database;

            foreach ($tables as $table) {
                $tableName = $table->$tableKey;
                $count = DB::table($tableName)->count();
                $recordCount += $count;
            }

            $dbSize = DB::select("
                SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.tables
                WHERE table_schema = ?
            ", [$database])[0]->size_mb;

            return response()->json([
                'records' => $recordCount,
                'tables' => $tableCount,
                'size' => $dbSize,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
