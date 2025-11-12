<?php

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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_blocked')->default(false)->after('email_verified_at');
            $table->boolean('is_active')->default(true)->after('is_blocked');
            $table->timestamp('blocked_at')->nullable()->after('is_active');
            $table->string('blocked_reason')->nullable()->after('blocked_at');
            $table->timestamp('last_login_at')->nullable()->after('blocked_reason');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            $table->integer('failed_login_attempts')->default(0)->after('last_login_ip');
            $table->timestamp('last_failed_login_at')->nullable()->after('failed_login_attempts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_blocked',
                'is_active',
                'blocked_at',
                'blocked_reason',
                'last_login_at',
                'last_login_ip',
                'failed_login_attempts',
                'last_failed_login_at',
            ]);
        });
    }
};
