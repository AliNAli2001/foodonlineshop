<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'name_ar',
        'name_en',
        'featured',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the image for this category.
     */
    public function image(): HasOne
    {
        return $this->hasOne(CategoryImage::class, 'category_id');
    }

    /**
     * Get all products in this category.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_categories', 'category_id', 'product_id');
    }

    /**
     * Get the name in the specified language.
     */
    public function getName(string $language = 'en'): string
    {
        return $language === 'ar' ? $this->name_ar : $this->name_en;
    }
}

