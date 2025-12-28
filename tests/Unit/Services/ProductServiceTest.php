<?php

namespace Tests\Unit\Services;

use App\Services\ProductService;
use App\Services\InventoryService;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    protected ProductService $productService;
    protected InventoryService $inventoryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->inventoryService = app(InventoryService::class);
        $this->productService = new ProductService($this->inventoryService);
    }

    public function test_product_service_can_be_instantiated()
    {
        $this->assertInstanceOf(ProductService::class, $this->productService);
    }

    public function test_create_product_method_exists()
    {
        $this->assertTrue(method_exists($this->productService, 'createProduct'));
    }

    public function test_update_product_method_exists()
    {
        $this->assertTrue(method_exists($this->productService, 'updateProduct'));
    }

    public function test_get_all_products_method_exists()
    {
        $this->assertTrue(method_exists($this->productService, 'getAllProducts'));
    }

    public function test_get_product_method_exists()
    {
        $this->assertTrue(method_exists($this->productService, 'getProduct'));
    }

    public function test_delete_product_method_exists()
    {
        $this->assertTrue(method_exists($this->productService, 'deleteProduct'));
    }
}

