<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('issue_attachments', function (Blueprint $table) {
            $table->dropForeign(['uploaded_by']);
            $table->dropIndex(['uploaded_by']);
            $table->dropColumn('uploaded_by');
        });
    }

    public function down(): void
    {
        Schema::table('issue_attachments', function (Blueprint $table) {
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->index('uploaded_by');
        });
    }
};
