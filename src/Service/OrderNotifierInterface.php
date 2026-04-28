<?php

declare(strict_types=1);

namespace App\Service;

interface OrderNotifierInterface
{
    public function notify(int $purchaseNo, string $buyerEmail, string $deliveryAddress, array $items, float $total): void;
}
