<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccHeadTable extends Migration
{
    public function up(): void
    {
        // Ensure InnoDB engine for foreign keys
        Schema::create('acc_head', function (Blueprint $table) {
            $table->engine = 'InnoDB'; // Set InnoDB engine

            // Primary columns
            $table->id();
            $table->string('code', 50)->nullable();
            $table->boolean('deletable')->default(true);
            $table->boolean('editable')->default(true);
            $table->string('aname', 150);
            $table->string('phone', 50)->nullable();
            $table->string('address', 250)->nullable();
            $table->string('e_mail', 150)->nullable();
            $table->boolean('employees_expensses')->nullable();
            $table->boolean('constant')->nullable();
            $table->boolean('is_stock')->nullable();
            $table->boolean('is_fund')->nullable();
            $table->boolean('rentable')->nullable();

            // Parent ID without foreign key constraint
            $table->foreignId('parent_id')->nullable()->constrained('acc_head')->onDelete('restrict');

            // Business logic columns
            $table->tinyInteger('nature')->nullable(); // 0 => Debit, 1 => Credit
            $table->tinyInteger('kind')->nullable(); // Custom business logic
            $table->boolean('is_basic')->default(false);

            // Balance and financial columns
            $table->decimal('start_balance', 18, 3)->default(0);
            $table->decimal('credit', 18, 3)->default(0);
            $table->decimal('debit', 18, 3)->default(0);
            $table->decimal('balance', 18, 3)->default(0);

            // Additional settings
            $table->boolean('secret')->default(false);
            $table->dateTime('crtime')->nullable();
            $table->dateTime('mdtime')->nullable();
            $table->text('info')->nullable();
            $table->boolean('isdeleted')->default(false);

            // Tenant and branch ID without foreign key constraint
            $table->unsignedBigInteger('tenant')->nullable();
            $table->unsignedBigInteger('branch')->nullable();
            $table->unsignedInteger('project_id')->nullable();
            $table->unsignedInteger('rent_to')->nullable();
            // Indexes (These are optional but can improve query performance)
            $table->index('code');
            $table->index('parent_id');
            $table->index('tenant');
            $table->index('branch');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acc_head');
    }
}
