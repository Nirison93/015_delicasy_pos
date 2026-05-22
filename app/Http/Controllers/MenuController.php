<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CompanyInfo;
use App\Models\Product;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Public food menu — no authentication required.
     * Displays all categories and the products under each category.
     */
    public function index()
    {
        // Load top-level categories that have at least one active product
        // Only show food categories (category_type = 0)
        $categories = Category::whereNull('parent_id')
            ->where('category_type', 0)
            ->with([
                'children' => function ($q) {
                    $q->where('category_type', 0)
                        ->with([
                            'products' => function ($pq) {
                                $pq->where('stock_quantity', '>', 0)
                                    ->select('id', 'category_id', 'name', 'selling_price', 'discounted_price', 'discount', 'image', 'description', 'is_beverage')
                                    ->orderBy('name');
                            },
                        ]);
                },
                'products' => function ($q) {
                    $q->where('stock_quantity', '>', 0)
                        ->select('id', 'category_id', 'name', 'selling_price', 'discounted_price', 'discount', 'image', 'description', 'is_beverage')
                        ->orderBy('name');
                },
            ])
            ->orderBy('name')
            ->get();

        // Flatten: merge parent products + children products per section
        // Then filter out empty categories
        $sections = collect();

        foreach ($categories as $parent) {
            // Direct products under the parent
            if ($parent->products->isNotEmpty()) {
                $sections->push([
                    'id'       => 'cat-' . $parent->id,
                    'name'     => $parent->name,
                    'image'    => $parent->image,
                    'products' => $parent->products,
                ]);
            }
            // Sub-category products
            foreach ($parent->children as $child) {
                if ($child->products->isNotEmpty()) {
                    $sections->push([
                        'id'       => 'cat-' . $child->id,
                        'name'     => $child->name,
                        'image'    => $child->image,
                        'products' => $child->products,
                    ]);
                }
            }
        }

        $company = CompanyInfo::first();

        return view('menu', compact('sections', 'company'));
    }
}
