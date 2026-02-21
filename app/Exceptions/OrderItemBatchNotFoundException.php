<?php

namespace App\Exceptions;

use Exception;

class OrderItemBatchNotFoundException extends Exception
{
    public function __construct(int $orderItemId)
    {
        parent::__construct("No inventory batches linked for order item {$orderItemId}.");
    }
}

