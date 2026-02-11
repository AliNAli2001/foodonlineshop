<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Adjustment extends Model
{
    protected $table = 'adjustments';

    protected $fillable = [
        'quantity',
        'adjustment_type',
        'reason',
        'date',
        'adjustable_id',
        'adjustable_type',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'date' => 'date',
    ];

    const TYPES = ['gain' => 'ربح', 'loss' => 'خسارة'];

    /**
     * Get the parent adjustable model (DamagedGoods, etc.).
     */
    public function adjustable(): MorphTo
    {
        return $this->morphTo();
    }
}
