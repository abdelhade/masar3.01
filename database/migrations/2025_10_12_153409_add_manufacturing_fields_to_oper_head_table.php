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
                $table->foreignId('manufacturing_order_id')->nullable()->constrained('manufacturing_orders')->onDelete('set null');
                $table->foreignId('manufacturing_stage_id')->nullable()->constrained('manufacturing_order_stage')->onDelete('set null');
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::table('operhead', function (Blueprint $table) {
                $table->dropForeign(['manufacturing_order_id']);
                $table->dropForeign(['manufacturing_stage_id']);
                $table->dropColumn(['manufacturing_order_id', 'manufacturing_stage_id']);
            });
        }
    };
