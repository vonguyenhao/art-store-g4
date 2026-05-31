<?php

declare(strict_types=1);

namespace App\Service;

use App\Core\Session;
use App\Repository\ProductRepository;
use RuntimeException;

final class CartService
{
    public function __construct(
        private readonly Session $session,
        private readonly ProductRepository $products
    ) {
    }

    public function raw(): array
    {
        return $this->session->get('cart', []);
    }

    public function add(int $productNo, int $quantity): void
    {
        if ($productNo < 1) {
            throw new RuntimeException('Please choose a valid artwork.');
        }

        if ($quantity < 1) {
            throw new RuntimeException('Please enter a quantity of at least 1.');
        }

        if (!$this->products->availableById($productNo)) {
            throw new RuntimeException('This artwork is no longer available.');
        }

        $cart = $this->raw();
        $cart[$productNo] = min(20, ((int) ($cart[$productNo] ?? 0)) + min(20, $quantity));
        $this->set($cart);
    }

    public function update(array $quantities): void
    {
        $cart = [];
        foreach ($quantities as $productNo => $quantity) {
            $cart[(int) $productNo] = max(0, min(20, (int) $quantity));
        }
        $this->set($cart);
    }

    public function remove(int $productNo): void
    {
        $cart = $this->raw();
        unset($cart[$productNo]);
        $this->set($cart);
    }

    public function clear(): void
    {
        $this->session->set('cart', []);
    }

    public function count(): int
    {
        return array_sum(array_map('intval', $this->raw()));
    }

    public function items(): array
    {
        $cart = $this->raw();
        $items = [];
        $total = 0.0;
        $availableProductNos = [];

        foreach ($this->products->availableByIds(array_keys($cart)) as $product) {
            $productNo = (int) $product['product_no'];
            $availableProductNos[] = $productNo;
            $quantity = (int) ($cart[$productNo] ?? 0);
            if ($quantity < 1) {
                continue;
            }

            $lineTotal = $quantity * (float) $product['price'];
            $items[] = compact('product', 'quantity', 'lineTotal');
            $total += $lineTotal;
        }

        $removedCount = count(array_diff(array_map('intval', array_keys($cart)), $availableProductNos));
        if ($removedCount > 0) {
            $this->set(array_intersect_key($cart, array_flip($availableProductNos)));
        }

        return [$items, $total, $removedCount];
    }

    private function set(array $cart): void
    {
        $cleanCart = [];
        foreach ($cart as $productNo => $quantity) {
            $productNo = (int) $productNo;
            $quantity = (int) $quantity;
            if ($productNo > 0 && $quantity > 0) {
                $cleanCart[$productNo] = min(20, $quantity);
            }
        }

        $this->session->set('cart', $cleanCart);
    }
}
