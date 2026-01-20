<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItemBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'inventory_batch_id',
        'quantity',
        'cost_price',
    ];

    /* ================= Relationships ================= */

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function inventoryBatch()
    {
        return $this->belongsTo(InventoryBatch::class);
    }
}