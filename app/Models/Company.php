<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Company extends Model
{
       protected $fillable = [
        'name_ar',
        'name_en',
        'logo',
    ];

   public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

       public function getFullUrlAttribute()
    {
        return $this->logo ? Storage::disk('public')->url($this->logo) : null;
    }
}
