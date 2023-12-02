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
        Schema::create('sublevels', function (Blueprint $table) {
            $table->id();
            $table->string('sublevel_name')->unique();
            $table->foreignId('level_id')->constrained('levels')->onDelete('cascade');
            $table->text('level_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sublevels');
    }
};
