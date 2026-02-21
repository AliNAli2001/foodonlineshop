<?php

namespace App\Exceptions;

use Exception;

class InvalidOrderStatusTransitionException extends Exception
{
    public function __construct(
        public readonly string $fromStatus,
        public readonly string $toStatus
    ) {
        parent::__construct("Cannot change status from {$fromStatus} to {$toStatus} for this order.");
    }
}

