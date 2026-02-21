<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    public function __construct(
        public readonly ?int $productId = null,
        public readonly ?string $productName = null,
        public readonly ?int $available = null,
        public readonly ?int $requested = null,
        ?string $message = null
    ) {
        parent::__construct($message ?? $this->buildMessage());
    }

    private function buildMessage(): string
    {
        $label = $this->productName ? "{$this->productName}" : 'product';
        $idPart = $this->productId !== null ? " ({$this->productId})" : '';

        if ($this->available !== null && $this->requested !== null) {
            return "Insufficient stock for {$label}{$idPart}. Available: {$this->available}, requested: {$this->requested}.";
        }

        return "Insufficient stock for {$label}{$idPart}.";
    }
}

