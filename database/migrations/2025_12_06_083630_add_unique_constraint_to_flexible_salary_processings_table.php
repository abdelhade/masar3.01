<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // تنظيف البيانات المكررة قبل إضافة unique constraint
        // الاحتفاظ بأحدث معالجة أو المعتمدة (approved) لكل موظف في نفس الفترة
        $duplicates = DB::table('flexible_salary_processings')
            ->select('employee_id', 'period_start', 'period_end', DB::raw('COUNT(*) as count'))
            ->groupBy('employee_id', 'period_start', 'period_end')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            // الحصول على جميع المعالجات المكررة لهذا الموظف والفترة
            $processings = DB::table('flexible_salary_processings')
                ->where('employee_id', $duplicate->employee_id)
                ->where('period_start', $duplicate->period_start)
                ->where('period_end', $duplicate->period_end)
                ->orderByRaw("CASE WHEN status = 'approved' THEN 1 WHEN status = 'pending' THEN 2 ELSE 3 END")
                ->orderBy('created_at', 'desc')
                ->get();

            // الاحتفاظ بالأولى (الأفضل: approved > pending > rejected، ثم الأحدث)
            $keepId = $processings->first()->id;

            // حذف الباقي
            $idsToDelete = $processings->skip(1)->pluck('id')->toArray();
            if (! empty($idsToDelete)) {
                DB::table('flexible_salary_processings')
                    ->whereIn('id', $idsToDelete)
                    ->delete();
            }
        }

        // الآن يمكن إضافة unique constraint بأمان
        Schema::table('flexible_salary_processings', function (Blueprint $table) {
            // إضافة unique constraint لمنع معالجة متعددة لنفس الموظف في نفس الفترة
            // هذا يمنع التكرار على مستوى قاعدة البيانات
            $table->unique(['employee_id', 'period_start', 'period_end'], 'unique_employee_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flexible_salary_processings', function (Blueprint $table) {
            $table->dropUnique('unique_employee_period');
        });
    }
};
