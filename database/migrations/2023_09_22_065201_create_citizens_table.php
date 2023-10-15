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
        Schema::create('citizens', function (Blueprint $table) {
            $table->id();
            $table->string('photo')->nullable();
            $table->string('batch', 60)->nullable();
            $table->string('control_no')->unique()->nullable();
            $table->string('scid', 60)->nullable();
            $table->string('identifier')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('extra_name')->nullable();
            $table->string('representative')->nullable();
            $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('province_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('barangay_id')->nullable()->constrained()->nullOnDelete();
            $table->longText('address')->nullable();
            $table->longText('extra_address')->nullable();
            $table->enum('gender', ['male', 'female']);
            $table->date('birthday')->nullable();
            $table->string('disability_status')->nullable();
            $table->string('ip_status')->nullable();
            $table->longText('correction_remarks')->nullable();
            $table->string('food_status')->nullable();
            $table->string('medicine_vitamin_status')->nullable();
            $table->string('medical_health_check_status')->nullable();
            $table->string('clothing_status')->nullable();
            $table->string('utilities_status')->nullable();
            $table->string('debit_payment_status')->nullable();
            $table->string('livelihood_activities_status')->nullable();
            $table->string('other_status')->nullable();
            $table->enum('citizen_status', ['active', 'inactive', 'waitlisted', 'deceased', 'double_entry', 'pension', 'transferred', 'unlocated', 'well_off', 'unknown', 'other']);
            $table->string('replacement')->nullable();
            $table->string('quarter_of_separation')->nullable();
            $table->longText('detailed_remarks')->nullable();
            $table->longText('remarks')->nullable();
            $table->date('date_downloaded')->nullable();
            $table->longText('additional')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citizens');
    }
};
