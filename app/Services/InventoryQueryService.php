<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class InventoryQueryService
{
    public function lowStockProducts(): Collection
    {
        return Product::query()
            ->join('product_stocks', 'products.id', '=', 'product_stocks.product_id')
            ->whereColumn(
                'product_stocks.available_quantity',
                '<',
                'products.minimum_alert_quantity'
            )
            ->select([
                'products.*',
                'product_stocks.available_quantity',
                'product_stocks.reserved_quantity',
            ])
            ->orderBy('product_stocks.available_quantity', 'asc')
            ->get();
    }
}
