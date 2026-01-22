<?php

namespace App\Services;

use App\Models\Product;
use App\Models\InventoryBatch;
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
            ])
            ->orderBy('product_stocks.available_quantity', 'asc')
            ->get();
    }
  

    public function expiredSoonInventories(int $days = 7): Collection
    {
        return InventoryBatch::query()
            ->with('product')
            ->whereNotNull('expiry_date')
            ->where('status', '!=', 'expired')
            ->whereBetween('expiry_date', [
                now()->addDay()->toDate(),
                now()->addDays($days)->toDate(),
            ])
            ->orderBy('expiry_date', 'asc')
            ->get();
    }
}
