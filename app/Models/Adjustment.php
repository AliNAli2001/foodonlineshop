<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Adjustment extends Model
{
    protected $table = 'adjustments';

    protected $fillable = [
        'quantity',
        'adjustment_type',
        'reason',
        'date',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'date' => 'date',
    ];

    const TYPES = ['gain' => 'ربح', 'loss' => 'خسارة'];
}
