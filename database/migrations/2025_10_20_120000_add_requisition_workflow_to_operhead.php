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
        Schema::table('operhead', function (Blueprint $table) {
            // workflow state for multi-stage documents (0 = draft, 1 = submitted, 2 = approved, 3 = quoted, 4 = purchase_order, 5 = received, 6 = purchase_invoiced, 7 = transferred/closed)
            if (! Schema::hasColumn('operhead', 'workflow_state')) {
                $table->unsignedTinyInteger('workflow_state')->default(0)->after('status')->comment('workflow stage for multi-stage operations');
            }

            // origin and parent links to chain created documents
            if (! Schema::hasColumn('operhead', 'origin_id')) {
                $table->unsignedBigInteger('origin_id')->nullable()->after('workflow_state');
                $table->foreign('origin_id')->references('id')->on('operhead')->onDelete('set null');
            }

            if (! Schema::hasColumn('operhead', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('origin_id');
                $table->foreign('parent_id')->references('id')->on('operhead')->onDelete('set null');
            }

            // approval metadata
            if (! Schema::hasColumn('operhead', 'approved_by')) {
                $table->unsignedInteger('approved_by')->nullable()->after('parent_id');
            }
            if (! Schema::hasColumn('operhead', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (! Schema::hasColumn('operhead', 'approval_notes')) {
                $table->string('approval_notes', 500)->nullable()->after('approved_at');
            }

            // lock flag to prevent edits once document is approved/converted
            if (! Schema::hasColumn('operhead', 'is_locked')) {
                $table->tinyInteger('is_locked')->default(0)->after('closed');
            }

            $table->index('workflow_state');
            $table->index('origin_id');
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operhead', function (Blueprint $table) {
            if (Schema::hasColumn('operhead', 'workflow_state')) {
                $table->dropIndex(['workflow_state']);
                $table->dropColumn('workflow_state');
            }
            if (Schema::hasColumn('operhead', 'origin_id')) {
                $table->dropForeign(['origin_id']);
                $table->dropIndex(['origin_id']);
                $table->dropColumn('origin_id');
            }
            if (Schema::hasColumn('operhead', 'parent_id')) {
                $table->dropForeign(['parent_id']);
                $table->dropIndex(['parent_id']);
                $table->dropColumn('parent_id');
            }
            if (Schema::hasColumn('operhead', 'approved_by')) {
                $table->dropColumn('approved_by');
            }
            if (Schema::hasColumn('operhead', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
            if (Schema::hasColumn('operhead', 'approval_notes')) {
                $table->dropColumn('approval_notes');
            }
            if (Schema::hasColumn('operhead', 'is_locked')) {
                $table->dropColumn('is_locked');
            }
        });
    }
};
