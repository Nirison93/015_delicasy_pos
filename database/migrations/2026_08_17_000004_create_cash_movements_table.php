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
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cash_drawer_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('type', ['cash_in', 'cash_out', 'cash_drop']);
            $table->decimal('amount', 10, 2);
            $table->string('reason', 255)->nullable();
            $table->timestamps();

            $table->foreign('cash_drawer_id')->references('id')->on('cash_drawers')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index('cash_drawer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};
