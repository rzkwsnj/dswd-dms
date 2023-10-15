<?php

use App\Models\Citizen;
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
        Schema::create('citizen_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Citizen::class);
            $table->string('reference')->nullable();
            $table->string('payout_period_year')->nullable();
            $table->string('payout_period_semester')->nullable();
            $table->date('date');
            $table->string('method')->nullable();
            $table->decimal('amount');
            $table->string('currency')->nullable();
            $table->enum('status', ['paid', 'unpaid', 'pending'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citizen_payouts');
    }
};
