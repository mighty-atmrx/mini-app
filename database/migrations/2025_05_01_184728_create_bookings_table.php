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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_id')->constrained('experts');
            $table->foreignId('service_id')->constrained('services');
            $table->foreignId('user_id')->constrained('users');
            $table->date('date');
            $table->time('time');
            $table->enum('status', ['waiting', 'paid', 'rejected', 'completed', 'failed'])->default('waiting');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
