<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
