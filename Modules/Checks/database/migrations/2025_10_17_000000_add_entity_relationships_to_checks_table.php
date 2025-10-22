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
        Schema::table('checks', function (Blueprint $table) {
            // Add foreign key relationships for entity linking
            $table->foreignId('invoice_id')->nullable()->after('oper_id')->constrained('operhead')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->after('invoice_id')->constrained('acc_head')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->after('supplier_id')->constrained('acc_head')->nullOnDelete();
            
            // Add handled_by (employee/user) field
            $table->foreignId('handled_by')->nullable()->after('customer_id')->constrained('users')->nullOnDelete();
            
            // Add indexes for better query performance
            $table->index(['invoice_id', 'type']);
            $table->index(['supplier_id', 'type']);
            $table->index(['customer_id', 'type']);
            $table->index('handled_by');
            $table->index('check_number');
            $table->index('due_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checks', function (Blueprint $table) {
            // Drop foreign key constraints
            $table->dropForeign(['invoice_id']);
            $table->dropForeign(['supplier_id']);
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['handled_by']);
            
            // Drop columns
            $table->dropColumn(['invoice_id', 'supplier_id', 'customer_id', 'handled_by']);
            
            // Drop indexes
            $table->dropIndex(['invoice_id', 'type']);
            $table->dropIndex(['supplier_id', 'type']);
            $table->dropIndex(['customer_id', 'type']);
            $table->dropIndex(['handled_by']);
            $table->dropIndex(['check_number']);
            $table->dropIndex(['due_date']);
            $table->dropIndex(['status']);
        });
    }
};