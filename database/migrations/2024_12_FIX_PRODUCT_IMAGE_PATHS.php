<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Helpers\ImagePathHelper;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix all product image paths
        $products = DB::table('products')->whereNotNull('image')->get();

        foreach ($products as $product) {
            $fixedPath = ImagePathHelper::standardize($product->image);
            if ($fixedPath !== $product->image) {
                DB::table('products')
                    ->where('id', $product->id)
                    ->update(['image' => $fixedPath]);
            }
        }

        // Fix all category image paths
        $categories = DB::table('categories')->whereNotNull('image')->get();

        foreach ($categories as $category) {
            $fixedPath = ImagePathHelper::standardize($category->image);
            if ($fixedPath !== $category->image) {
                DB::table('categories')
                    ->where('id', $category->id)
                    ->update(['image' => $fixedPath]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be safely reversed as we don't know the original format
    }
};
