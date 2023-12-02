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
        Schema::table('branch_category', function (Blueprint $table) {
            $table->dropColumn('price_per_1');
            $table->dropColumn('price_per_2');
            $table->dropColumn('price_per_4');
            $table->dropColumn('price_per_6');
            $table->dropColumn('price_per_8');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_category', function (Blueprint $table) {
            //
        });
    }
};
