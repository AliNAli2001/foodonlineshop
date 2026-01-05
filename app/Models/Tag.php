<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    
    protected $table = 'tags';

    protected $fillable = [
        'name_ar',
        'name_en',
      
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];



    /**
     * Get all products in this category.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_tags', 'tag_id', 'product_id');
    }

    /**
     * Get the name in the specified language.
     */
    public function getName(string $language = 'en'): string
    {
        return $language === 'ar' ? $this->name_ar : $this->name_en;
    }
}
