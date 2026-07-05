<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function showBarcode($id)
    {
        $product = Product::findOrFail($id);

        return view('barcode', compact('product'));
    }
    public function index(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }
        Gate::allows('hasRole', ['Admin', 'Manager']);

        $categoryType = $request->input('category_type', '0'); // 0 = Food (default), 1 = Bar

       $allcategories = Category::with('parent')
        ->where('category_type', $categoryType)
        ->orderBy('id', 'desc')
        ->get()
        ->map(function ($category) {
            return [
                'id'    => $category->id,
                'name'  => $category->name,
                'category_type'  => $category->category_type,
                'image' => $category->image,

                'parent' => $category->parent ? [
                    'id'   => $category->parent->id,
                    'name' => $category->parent->name,
                ] : null,

                'hierarchy_string' => $category->hierarchy_string,
            ];
    });





        return Inertia::render('Categories/Index', [
            // 'paginatedcategories' => $paginatedcategories,
            'allcategories' => $allcategories,
            'totalCategories' => $allcategories->count(),
            'categoryType'  => $categoryType,
        ]);
    }

    public function create()
    {
        $categories = Category::all();

        return Inertia::render('Categories/Create', [
            'categories' => $categories,
        ]);
    }





public function store(Request $request)
{



    if (!Gate::allows('hasRole', ['Admin'])) {
        abort(403, 'Unauthorized');
    }

    // Validate inputs (add image rules)
    $validated = $request->validate([
        'category_type' => ['required', 'in:0,1'],
        'name'          => ['required', 'string', 'max:255'],
        'parent_id'     => ['nullable', 'exists:categories,id'],
        'image'         => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:2048'], // <= 2MB
    ]);

    // Normalize parent_id when empty string is sent
    if (empty($validated['parent_id'])) {
        $validated['parent_id'] = null;
    }

    if ($request->hasFile('image')) {
        $path = $request->file('image')->store('categories', 'public');
        $validated['image'] = $path;
    }

    Category::create($validated);

    return redirect()
        ->route('categories.index')
        ->banner('Category created successfully.');
}



    public function edit(Category $category)
    {
        return Inertia::render('Categories/Edit', [
            'category' => $category,
        ]);
    }

public function update(Request $request, Category $category)
{
    if (!Gate::allows('hasRole', ['Admin'])) {
        abort(403, 'Unauthorized');
    }

    // Validate (add image + optional remove flag)
    $validated = $request->validate([
        'category_type' => ['required', 'in:0,1'],
        'name'         => ['required', 'string', 'max:255'],
        'parent_id'    => ['nullable', 'exists:categories,id'],
        'image'        => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:2048'],
        'remove_image' => ['nullable', 'boolean'],
    ]);

    // Normalize parent when "No Parent" -> ""
    if (!$request->filled('parent_id')) {
        $validated['parent_id'] = null;
    }

    // Circular relationship guard
    if (!empty($validated['parent_id'])) {
        $parent = Category::find($validated['parent_id']);
        while ($parent) {
            if ($parent->id === $category->id) {
                return back()->withErrors([
                    'parent_id' => 'A category cannot be its own parent or ancestor.',
                ])->withInput();
            }
            $parent = $parent->parent;
        }
    }

    // Handle category type change - remove parent if switching types
    if (isset($validated['category_type']) && $validated['category_type'] != $category->category_type) {
        $validated['parent_id'] = null;
    }

    // Handle image removal FIRST (before checking for new upload)
    if ($request->boolean('remove_image')) {
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }
        $validated['image'] = null;
    }

    // Handle new image upload (only if not removing)
    if ($request->hasFile('image') && !$request->boolean('remove_image')) {
        // Delete old image if exists
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }
        $validated['image'] = $request->file('image')->store('categories', 'public');
    }

    // Remove remove_image from validated data (not a DB column)
    unset($validated['remove_image']);

    // If no new image was uploaded and removal was not requested,
    // drop 'image' from the payload so the existing value is preserved.
    if (!$request->hasFile('image') && !$request->boolean('remove_image')) {
        unset($validated['image']);
    }

    // Persist
    $category->update($validated);

    return redirect()->route('categories.index')->banner('Category updated successfully.');
}





 public function destroy(Category $category)
{
    if (!Gate::allows('hasRole', ['Admin'])) {
        abort(403, 'Unauthorized');
    }

    // Delete stored image if it exists
    if ($category->image) {
        Storage::disk('public')->delete($category->image);
    }

    $category->delete();

    return redirect()
        ->route('categories.index')
        ->banner('Category deleted successfully.');
}



    public function topCategories(Request $request)
{
    $categoryType = $request->input('category_type');

    $query = Category::whereNull('parent_id')->with('children');

    // Filter by category_type if provided
    if (in_array($categoryType, ['0', '1'])) {
        $query->where('category_type', $categoryType);
    }

    $categories = $query->get()->map(function ($category) {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'image' => $category->image,
            'category_type' => $category->category_type,
            'hierarchy_string' => $category->hierarchy_string,
            'children' => $category->children->map(function ($child) {
                return [
                    'id' => $child->id,
                    'name' => $child->name,
                    'image' => $child->image,
                    'category_type' => $child->category_type,
                    'hierarchy_string' => $child->hierarchy_string,
                ];
            }),
        ];
    });

    return response()->json([
        'categories' => $categories,
    ]);
}

public function show(Category $category)
{
    return response()->json([
        'category' => $category->load('children')
    ]);
}

}
