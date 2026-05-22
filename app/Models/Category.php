<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;


class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_type',
        'parent_id',
        'is_featured',
         'image',
         
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id', 'id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id', 'id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }

    protected $casts = [
        'is_featured' => 'boolean'
    ];

    /**
     * Get the image URL attribute
     */
    protected function getImageAttribute($value)
    {
        if (!$value) {
            return null;
        }
        // Convert relative path to full URL
        return Storage::url($value);
    }

    public function getHierarchyStringAttribute(): string
    {
        $hierarchy = [];
        $category = $this->parent;



        // Traverse the hierarchy without additional queries
        while ($category) {
            $hierarchy[] = $category->name;
            $category = $category->parent; // Uses Eloquent's cached relationship
        }

        return implode(' ----> ', array_reverse($hierarchy));
    }
}
