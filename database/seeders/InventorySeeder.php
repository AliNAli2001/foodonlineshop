<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Services\InventoryService;
use App\Models\Product;
class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(InventoryService $inventoryService): void
    {
        $products = Product::all();

        foreach ($products as $product) {

            // Random starting stock
            $quantity = rand(5, 50);
            $validated = [
                'batch_number' => 'BATCH-'.rand(1000, 9999).'-' . $product->id,
                'expiry_date' => now()->addMonths(rand(3, 18)),
                'available_quantity' => $quantity,
                'cost_price' => $product->selling_price * 0.6, // example cost
                'reason' => 'Initial Stock Testing',
            ];
            $inventoryService->createBatch($product->id, $validated);
        }
    }
}
