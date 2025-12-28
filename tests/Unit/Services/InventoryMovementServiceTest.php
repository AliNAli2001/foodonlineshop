<?php

namespace Tests\Unit\Services;

use App\Services\InventoryMovementService;
use Tests\TestCase;

class InventoryMovementServiceTest extends TestCase
{
    protected InventoryMovementService $inventoryMovementService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->inventoryMovementService = app(InventoryMovementService::class);
    }

    public function test_inventory_movement_service_can_be_instantiated()
    {
        $this->assertInstanceOf(InventoryMovementService::class, $this->inventoryMovementService);
    }

    public function test_log_movement_method_exists()
    {
        $this->assertTrue(method_exists($this->inventoryMovementService, 'logMovement'));
    }

    public function test_get_product_movements_method_exists()
    {
        $this->assertTrue(method_exists($this->inventoryMovementService, 'getProductMovements'));
    }

    public function test_get_batch_movements_method_exists()
    {
        $this->assertTrue(method_exists($this->inventoryMovementService, 'getBatchMovements'));
    }
}

