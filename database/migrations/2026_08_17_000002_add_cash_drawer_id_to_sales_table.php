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
        Schema::table('sales', function (Blueprint $table) {
            $table->unsignedBigInteger('cash_drawer_id')->nullable()->after('user_id');
            $table->foreign('cash_drawer_id')->references('id')->on('cash_drawers')->onDelete('set null');
            $table->index('cash_drawer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['cash_drawer_id']);
            $table->dropIndex(['cash_drawer_id']);
            $table->dropColumn('cash_drawer_id');
        });
    }
};
