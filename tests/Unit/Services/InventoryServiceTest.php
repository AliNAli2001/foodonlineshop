<?php

namespace Tests\Unit\Services;

use App\Services\InventoryService;
use App\Services\InventoryMovementService;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    protected InventoryService $inventoryService;
    protected InventoryMovementService $inventoryMovementService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->inventoryMovementService = app(InventoryMovementService::class);
        $this->inventoryService = new InventoryService($this->inventoryMovementService);
    }

    public function test_inventory_service_can_be_instantiated()
    {
        $this->assertInstanceOf(InventoryService::class, $this->inventoryService);
    }

    public function test_create_batch_method_exists()
    {
        $this->assertTrue(method_exists($this->inventoryService, 'createBatch'));
    }

    public function test_update_batch_method_exists()
    {
        $this->assertTrue(method_exists($this->inventoryService, 'updateBatch'));
    }

    public function test_get_all_products_with_batches_method_exists()
    {
        $this->assertTrue(method_exists($this->inventoryService, 'getAllProductsWithBatches'));
    }

    public function test_get_product_batches_method_exists()
    {
        $this->assertTrue(method_exists($this->inventoryService, 'getProductBatches'));
    }

    public function test_get_batch_method_exists()
    {
        $this->assertTrue(method_exists($this->inventoryService, 'getBatch'));
    }
}

