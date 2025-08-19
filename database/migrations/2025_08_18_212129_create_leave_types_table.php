<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // مثال: سنوي، عارض، مرضي
            $table->string('code')->unique(); // مثال: AL, SL
            $table->boolean('is_paid')->default(true);
            $table->boolean('requires_approval')->default(true);
            $table->unsignedInteger('max_per_request_days')->nullable();
            $table->decimal('accrual_rate_per_month', 6, 2)->nullable(); // لاستخدام سياسة التراكم
            $table->unsignedInteger('carry_over_limit_days')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
