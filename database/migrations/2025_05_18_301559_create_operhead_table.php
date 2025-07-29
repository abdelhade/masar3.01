<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migration: Create the operhead table
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('operhead', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('pro_id')->nullable();
            $table->unsignedInteger('branch_id')->nullable();
            $table->unsignedTinyInteger('is_stock')->nullable();
            $table->unsignedTinyInteger('is_finance')->nullable();
            $table->unsignedTinyInteger('is_manager')->nullable();
            $table->unsignedTinyInteger('is_journal')->nullable();
            $table->unsignedTinyInteger('journal_type')->nullable();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->decimal('currency_rate', 15, 6)->default(1);
            $table->string('info', 200)->nullable();
            $table->timestamp('start_time')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('end_time')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('pro_date')->nullable();
            $table->date('accural_date')->nullable();

            $table->unsignedInteger('pro_pattren')->nullable();
            $table->string('pro_num', 50)->nullable();
            $table->string('pro_serial', 50)->nullable();
            $table->string('tax_num', 50)->nullable();

            $table->unsignedInteger('price_list')->nullable();
            $table->foreignId('store_id')->nullable()->constrained('acc_head')->nullOnDelete();
            $table->foreignId('emp_id')->nullable()->constrained('acc_head')->nullOnDelete();
            $table->foreignId('emp2_id')->nullable()->constrained('acc_head')->nullOnDelete();

            $table->foreignId('acc1')->nullable()->constrained('acc_head')->nullOnDelete();
            $table->decimal('acc1_before', 15, 2)->nullable();
            $table->decimal('acc1_after', 15, 2)->nullable();

             $table->foreignId('acc2')->nullable()->constrained('acc_head')->nullOnDelete();
            $table->decimal('acc2_before', 15, 2)->nullable();
            $table->decimal('acc2_after', 15, 2)->nullable();

            $table->decimal('pro_value', 15, 2)->nullable();
            $table->decimal('fat_cost', 15, 2)->nullable();
            $table->unsignedInteger('cost_center')->nullable();
            $table->decimal('profit', 15, 2)->nullable();

            $table->decimal('fat_total', 15, 2)->nullable();
            $table->decimal('fat_net', 15, 2)->default(0);
            $table->decimal('fat_disc', 15, 2)->nullable();
            $table->decimal('fat_disc_per', 5, 2)->nullable();
            $table->decimal('fat_plus', 15, 2)->nullable();
            $table->decimal('fat_plus_per', 5, 2)->nullable();
            $table->decimal('fat_tax', 15, 2)->nullable();
            $table->decimal('fat_tax_per', 5, 2)->nullable();

            $table->timestamp('crtime')->useCurrent();
            $table->tinyInteger('acc_fund')->default(0);
            $table->unsignedInteger('op2')->default(0);
            $table->tinyInteger('isdeleted')->default(0);

            $table->timestamps();

            $table->unsignedInteger('user')->default(1);
            $table->unsignedInteger('tenant')->default(0);
            $table->unsignedInteger('branch')->default(0);
            $table->tinyInteger('closed')->default(0);

            $table->string('info2', 200)->nullable();
            $table->string('info3', 200)->nullable();
            $table->string('details', 200)->nullable();

            $table->unsignedInteger('pro_type')->nullable();
            $table->unsignedInteger('project_id')->nullable();
            $table->foreignId('acc3')->nullable()->constrained('acc_head')->nullOnDelete();
            // علاقات Foreign Keys
            $table->foreign('pro_type')->references('id')->on('pro_types')->onDelete('set null');

            $table->index('acc1');
            $table->index('acc2');
            $table->index('emp2_id');
            $table->index('emp_id');
            $table->index('journal_type');
            $table->index('user');
            $table->index('cost_center');
            $table->index('store_id');
            $table->index('price_list');
        });
    }

    /**
     * التراجع عن المايغريشن
     */
    public function down(): void
    {
        Schema::dropIfExists('operhead');
    }
};
