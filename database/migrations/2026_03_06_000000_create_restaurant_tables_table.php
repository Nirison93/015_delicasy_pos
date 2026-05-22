<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_tables', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('number')->unique();
            $table->string('name')->nullable();
            $table->enum('status', ['available', 'occupied'])->default('available');
            $table->timestamps();
        });

        // Seed with 10 default tables
        for ($i = 1; $i <= 10; $i++) {
            DB::table('restaurant_tables')->insert([
                'number' => $i,
                'name'   => 'Table ' . $i,
                'status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_tables');
    }
};
