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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('coach_id')->nullable();
            $table->time('training_start_time')->nullable();
            $table->time('training_end_time')->nullable();
            $table->float('session_duration')->nullable();
            $table->boolean('is_attended')->default(true);

            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('coach_id')->references('id')->on('coaches');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
