<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waiter_orders', function (Blueprint $table) {
            $table->dropForeign(['table_id']);
            $table->foreign('table_id')->references('id')->on('restaurant_tables')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('waiter_orders', function (Blueprint $table) {
            $table->dropForeign(['table_id']);
            $table->foreign('table_id')->references('id')->on('shifts')->onDelete('cascade');
        });
    }
};
