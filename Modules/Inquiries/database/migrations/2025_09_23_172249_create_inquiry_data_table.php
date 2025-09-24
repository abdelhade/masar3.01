<?php

use Modules\Inquiries\Enums\KonTitle;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Modules\Inquiries\Enums\StatusForKon;
use Modules\Inquiries\Enums\InquiryStatus;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inquiry_data', function (Blueprint $table) {
            $table->id();
            $table->string('project_name');
            $table->string('project_id')->nullable(); // ID (if any)

            // Foreign keys for Clients based on ClientType
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null'); // Client
            $table->foreignId('main_contractor_id')->nullable()->constrained('clients')->onDelete('set null'); // Main Contractor (ClientType: MainContractor)
            $table->foreignId('consultant_id')->nullable()->constrained('clients')->onDelete('set null'); // Consultant (ClientType: Consultant)
            $table->foreignId('owner_id')->nullable()->constrained('clients')->onDelete('set null'); // Owner (ClientType: Owner)

            // Location: Tree structure, starting from City
            $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('set null'); // location_city
            $table->foreignId('town_id')->nullable()->constrained('towns')->onDelete('set null'); // location_zone (dependent on city)

            $table->date('inquiry_date');
            $table->date('req_submittal_date')->nullable(); // From inquiry Sheet
            $table->date('project_start_date')->nullable();

            // Enums for statuses
            $table->enum('status', array_column(InquiryStatus::cases(), 'value'))->default(InquiryStatus::JOB_IN_HAND->value);
            $table->enum('status_for_kon', array_column(StatusForKon::cases(), 'value'))->nullable()->default(StatusForKon::EXTENSION->value); // Example from sheet
            $table->enum('kon_title', array_column(KonTitle::cases(), 'value'))->default(KonTitle::MAIN_PILING_CONTRACTOR->value);

            $table->json('submittals')->nullable(); // Required Submittal Checklist
            $table->json('conditions')->nullable(); // Working Conditions Checklist
            $table->integer('total_conditions_score')->default(0); // Total score
            $table->integer('project_difficulty')->default(1); // Computed difficulty
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inquiry_data');
    }
};
