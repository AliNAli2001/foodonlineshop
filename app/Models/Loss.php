<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loss extends Model
{
    protected $table = 'losses';

    protected $fillable = [
        'quantity',
        'type',
        'reason',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    const TYPES = ['shipping_costs' => 'تكاليف الشحن', 'general_costs' => 'تكاليف عامة', 'delivery_costs' => 'تكاليف التوصيل', 'other' => 'آخرى'];
}
