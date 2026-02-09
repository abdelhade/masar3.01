<?php

namespace Modules\Progress\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BackupController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view progress-backup')->only(['index']);
        $this->middleware('can:create progress-backup')->only(['export', 'import']);
        $this->middleware('can:download progress-backup')->only(['download']);
        $this->middleware('can:delete progress-backup')->only(['destroy']);
    }

    /**
     * عرض صفحة النسخ الاحتياطي
     */
    public function index()
    {
        // جلب النسخ الاحتياطية الموجودة
        $backups = $this->getBackupFiles();
        
        return view('progress::backup.index', compact('backups'));
    }

    /**
     * إنشاء نسخة احتياطية وتحميلها
     */
    public function export()
    {
        try {
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            
            $filename = 'backup_' . Carbon::now()->format('Y-m-d_H-i-s') . '.sql';
            $filepath = storage_path('app/backups/' . $filename);
            
            // إنشاء مجلد backups إذا لم يكن موجوداً
            if (!file_exists(storage_path('app/backups'))) {
                mkdir(storage_path('app/backups'), 0755, true);
            }
            
            // تنفيذ أمر mysqldump
            $dumpCommand = $this->getMysqlDumpPath();
            $command = sprintf(
                '%s --user=%s --password=%s --host=%s %s > %s',
                $dumpCommand,
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($host),
                escapeshellarg($database),
                escapeshellarg($filepath)
            );
            
            \exec($command, $output, $return_var);
            
            if ($return_var !== 0) {
                \Log::error('Backup failed. Output: ' . implode("\n", $output));
                throw new \Exception('فشل إنشاء النسخة الاحتياطية. رمز الخطأ: ' . $return_var . '. تفاصيل: ' . implode(" ", $output));
            }
            
            // تحميل الملف
            return response()->download($filepath, $filename)->deleteFileAfterSend(false);
            
        } catch (\Exception $e) {
            return back()->with('error', 'خطأ في إنشاء النسخة الاحتياطية: ' . $e->getMessage());
        }
    }

    /**
     * استيراد نسخة احتياطية
     */
    public function import(Request $request)
    {
        $request->validate([
            'sql_file' => 'required|file|max:102400' // Max 100MB
        ]);
        
        // التحقق من امتداد الملف يدوياً
        $file = $request->file('sql_file');
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (!in_array($extension, ['sql', 'txt'])) {
            return back()->withErrors(['sql_file' => 'يجب أن يكون الملف بصيغة SQL أو TXT']);
        }
        
        try {
            DB::beginTransaction();
            
            $filepath = $file->getRealPath();
            
            // قراءة محتوى الملف
            $sql = file_get_contents($filepath);
            
            if (empty($sql)) {
                throw new \Exception('ملف SQL فارغ');
            }
            
            // تعطيل foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            // تقسيم الملف إلى استعلامات منفصلة
            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                function($statement) {
                    return !empty($statement) && 
                           !preg_match('/^(--|\/\*|#)/', $statement);
                }
            );
            
            // تنفيذ كل استعلام
            foreach ($statements as $statement) {
                if (!empty(trim($statement))) {
                    try {
                        DB::unprepared($statement);
                    } catch (\Exception $e) {
                        \Log::warning('SQL Statement Error: ' . $e->getMessage());
                        // Continue with next statement
                    }
                }
            }
            
            // إعادة تفعيل foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            DB::commit();
            
            return redirect()->route('progress.backup.index')
                ->with('success', 'تم استيراد النسخة الاحتياطية بنجاح');
                
        } catch (\Exception $e) {
            DB::rollBack();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            return back()
                ->with('error', 'خطأ في استيراد النسخة الاحتياطية: ' . $e->getMessage());
        }
    }

    /**
     * حذف نسخة احتياطية
     */
    public function destroy($filename)
    {
        try {
            $filepath = storage_path('app/backups/' . $filename);
            
            if (file_exists($filepath)) {
                unlink($filepath);
                return back()->with('success', 'تم حذف النسخة الاحتياطية بنجاح');
            }
            
            return back()->with('error', 'الملف غير موجود');
            
        } catch (\Exception $e) {
            return back()->with('error', 'خطأ في حذف النسخة الاحتياطية');
        }
    }

    /**
     * تحميل نسخة احتياطية موجودة
     */
    public function download($filename)
    {
        $filepath = storage_path('app/backups/' . $filename);
        
        if (file_exists($filepath)) {
            return response()->download($filepath);
        }
        
        return back()->with('error', 'الملف غير موجود');
    }

    /**
     * جلب قائمة النسخ الاحتياطية
     */
    private function getBackupFiles()
    {
        $backupPath = storage_path('app/backups');
        
        if (!file_exists($backupPath)) {
            return collect([]);
        }
        
        $files = scandir($backupPath);
        $backups = collect();
        
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                $filepath = $backupPath . '/' . $file;
                $backups->push([
                    'name' => $file,
                    'size' => $this->formatBytes(filesize($filepath)),
                    'date' => Carbon::createFromTimestamp(filemtime($filepath))->format('Y-m-d H:i:s'),
                    'created_at' => Carbon::createFromTimestamp(filectime($filepath))->format('Y-m-d H:i:s')
                ]);
            }
        }
        
        return $backups->sortByDesc('date');
    }

    /**
     * تحويل حجم الملف لصيغة قابلة للقراءة
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * الحصول على مسار mysqldump
     */
    private function getMysqlDumpPath()
    {
        // 1. Check if configured in env/config (custom)
        $configPath = config('database.dump_command_path');
        if ($configPath && file_exists($configPath)) {
            return $configPath;
        }

        // 2. Check common XAMPP/Laragon paths
        $commonPaths = [
            'C:\xampp\mysql\bin\mysqldump.exe',
            'D:\xampp\mysql\bin\mysqldump.exe',
            'C:\laragon\bin\mysql\mysql-5.7.33-winx64\bin\mysqldump.exe', // Example fallback
            'C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe',
        ];

        foreach ($commonPaths as $path) {
            if (file_exists($path)) {
                return '"' . $path . '"'; // Quote for safety
            }
        }

        // 3. Fallback to system path
        return 'mysqldump';
    }
}
