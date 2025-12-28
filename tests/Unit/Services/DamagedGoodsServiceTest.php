<?php

namespace Tests\Unit\Services;

use App\Services\DamagedGoodsService;
use App\Services\InventoryMovementService;
use Tests\TestCase;

class DamagedGoodsServiceTest extends TestCase
{
    protected DamagedGoodsService $damagedGoodsService;
    protected InventoryMovementService $inventoryMovementService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->inventoryMovementService = app(InventoryMovementService::class);
        $this->damagedGoodsService = new DamagedGoodsService($this->inventoryMovementService);
    }

    public function test_damaged_goods_service_can_be_instantiated()
    {
        $this->assertInstanceOf(DamagedGoodsService::class, $this->damagedGoodsService);
    }

    public function test_create_damaged_goods_method_exists()
    {
        $this->assertTrue(method_exists($this->damagedGoodsService, 'createDamagedGoods'));
    }

    public function test_get_all_damaged_goods_method_exists()
    {
        $this->assertTrue(method_exists($this->damagedGoodsService, 'getAllDamagedGoods'));
    }

    public function test_get_damaged_goods_method_exists()
    {
        $this->assertTrue(method_exists($this->damagedGoodsService, 'getDamagedGoods'));
    }

    public function test_delete_damaged_goods_method_exists()
    {
        $this->assertTrue(method_exists($this->damagedGoodsService, 'deleteDamagedGoods'));
    }

    public function test_get_available_batches_method_exists()
    {
        $this->assertTrue(method_exists($this->damagedGoodsService, 'getAvailableBatches'));
    }
}

