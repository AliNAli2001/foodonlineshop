<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Order::create(
            [
                'created_by_admin_id' => 1,
                'total_amount' => 100.00,
                'status' => 'pending',
                'order_source' => 'inside_city',
                'delivery_method' => 'delivery',
                'address_details' => '123 Main St',
                'latitude' => 12.3456,
                'longitude' => 12.3456,
                'general_notes' => 'Test order',
            ]
        );  
    }
}
