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
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id')->nullable();
            $table->string('order_id', 191)->nullable();
            $table->unsignedBigInteger('cash_drawer_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method', 20);
            $table->string('reason', 255)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('set null');
            $table->foreign('cash_drawer_id')->references('id')->on('cash_drawers')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index('sale_id');
            $table->index('cash_drawer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
