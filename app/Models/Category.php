<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'name_ar',
        'name_en',
        'featured',
        'category_image',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    /**
     * Get all products in this category.
     */ 
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    /**
     * Get the name in the specified language.
     */
    public function getName(string $language = 'en'): string
    {
        return $language === 'ar' ? $this->name_ar : $this->name_en;
    }
}
