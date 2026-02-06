<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Run the migrations.
     * Create issue_comments table for issue comments
     */
    public function up(): void
    {
        Schema::create('issue_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_id')->constrained('issues')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('comment');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('issue_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issue_comments');
    }
};
