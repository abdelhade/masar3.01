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
        Schema::create('hr_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('company_max_leave_percentage', 5, 2)->default(7.00)->comment('النسبة المئوية القصوى للشركة ككل');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_settings');
    }
};
