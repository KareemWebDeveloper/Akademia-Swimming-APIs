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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('category_name')->nullable();
            $table->date('subscription_date');
            $table->date('expiration_date');
            $table->date('freeze_start_date')->nullable();
            $table->date('freeze_end_date')->nullable();
            $table->string('academy_name')->nullable();
            $table->integer('number_of_sessions');
            $table->integer('sessions_per_week');
            $table->float('sale')->nullable();
            $table->enum('state',['active','inactive'])->default('active');
            $table->boolean('isfrozen')->default(false);

            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
