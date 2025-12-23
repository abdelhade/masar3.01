<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // update the existing enum value of salary_type to add the new values
            $table->enum('salary_type', ['ساعات عمل فقط', 'ساعات عمل و إضافي يومى', 'ساعات عمل و إضافي للمده', 'حضور فقط', 'إنتاج فقط', 'ثابت + ساعات عمل مرن'])->nullable()->change();
            $table->unsignedInteger('allowed_permission_days')->default(0);
            $table->unsignedInteger('allowed_late_days')->default(0);
            $table->unsignedInteger('allowed_absent_days')->default(0);
            $table->boolean('is_errand_allowed')->default(false);
            $table->unsignedInteger('allowed_errand_days')->default(0);
            $table->foreignId('line_manager_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->decimal('flexible_hourly_wage', 10, 2)->default(0);
            // Composite index for branch_id and name for faster search queries
            // This helps with BranchScope filtering and name searches
            if (! $this->indexExists('employees', 'employees_branch_id_name_index')) {
                $table->index(['branch_id', 'name'], 'employees_branch_id_name_index');
            }

            // Index on status for faster filtering
            if (! $this->indexExists('employees', 'employees_status_index')) {
                $table->index('status', 'employees_status_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['allowed_permission_days', 'allowed_late_days', 'allowed_absent_days', 'is_errand_allowed', 'allowed_errand_days', 'line_manager_id', 'flexible_hourly_wage']);
            $table->enum('salary_type', ['ساعات عمل فقط', 'ساعات عمل و إضافي يومى', 'ساعات عمل و إضافي للمده', 'حضور فقط', 'إنتاج فقط'])->change();
            $table->dropIndex('employees_branch_id_name_index');
            $table->dropIndex('employees_status_index');
        });
    }

    /**
     * Check if index exists (database-agnostic)
     */
    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        try {
            if ($driver === 'sqlite') {
                // SQLite: Check using pragma index_list
                $indexes = $connection->select("PRAGMA index_list({$table})");
                foreach ($indexes as $idx) {
                    if ($idx->name === $index) {
                        return true;
                    }
                }

                return false;
            } elseif ($driver === 'mysql') {
                // MySQL: Use information_schema
                $databaseName = $connection->getDatabaseName();
                $result = $connection->select(
                    'SELECT COUNT(*) as count 
                     FROM information_schema.statistics 
                     WHERE table_schema = ? 
                     AND table_name = ? 
                     AND index_name = ?',
                    [$databaseName, $table, $index]
                );

                return $result[0]->count > 0;
            } else {
                // For other databases, assume index doesn't exist (safe default)
                return false;
            }
        } catch (\Exception $e) {
            // If check fails, assume index doesn't exist
            return false;
        }
    }
};
