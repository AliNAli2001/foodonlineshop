<?php

namespace Tests\Unit\Services;

use App\Services\ReturnService;
use Tests\TestCase;

class ReturnServiceTest extends TestCase
{
    protected ReturnService $returnService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->returnService = app(ReturnService::class);
    }

    public function test_return_service_can_be_instantiated()
    {
        $this->assertInstanceOf(ReturnService::class, $this->returnService);
    }

    public function test_create_return_method_exists()
    {
        $this->assertTrue(method_exists($this->returnService, 'createReturn'));
    }

    public function test_get_all_returns_method_exists()
    {
        $this->assertTrue(method_exists($this->returnService, 'getAllReturns'));
    }

    public function test_get_return_method_exists()
    {
        $this->assertTrue(method_exists($this->returnService, 'getReturn'));
    }

    public function test_delete_return_method_exists()
    {
        $this->assertTrue(method_exists($this->returnService, 'deleteReturn'));
    }

    public function test_get_order_returns_method_exists()
    {
        $this->assertTrue(method_exists($this->returnService, 'getOrderReturns'));
    }
}

