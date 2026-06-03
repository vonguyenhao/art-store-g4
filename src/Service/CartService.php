<?php

declare(strict_types=1);

namespace App\Service;

use App\Core\Session;
use App\Repository\ProductRepository;
use RuntimeException;

final class CartService
{
    private const MAX_QUANTITY_PER_PRODUCT = 20;

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

        $product = $this->products->availableById($productNo);

        if (!$product) {
            throw new RuntimeException('This artwork is currently out of stock or unavailable.');
        }

        $cart = $this->raw();

        $currentQuantity = (int) ($cart[$productNo] ?? 0);

        if ($currentQuantity >= self::MAX_QUANTITY_PER_PRODUCT) {
            throw new RuntimeException('You already have the maximum quantity for this artwork in your cart.');
        }

        $newQuantity = $currentQuantity + $quantity;

        if ($newQuantity > self::MAX_QUANTITY_PER_PRODUCT) {
            throw new RuntimeException(
                'Only ' . self::MAX_QUANTITY_PER_PRODUCT . ' of this artwork can be added to one order.'
            );
        }

        $cart[$productNo] = $newQuantity;

        $this->set($cart);
    }

    public function update(array $quantities): void
    {
        $cart = [];

        foreach ($quantities as $productNo => $quantity) {
            $productNo = (int) $productNo;
            $quantity = (int) $quantity;

            if ($productNo < 1 || $quantity < 1) {
                continue;
            }

            if (!$this->products->availableById($productNo)) {
                continue;
            }

            if ($quantity > self::MAX_QUANTITY_PER_PRODUCT) {
                throw new RuntimeException(
                    'Quantity cannot be more than ' . self::MAX_QUANTITY_PER_PRODUCT . ' for one artwork.'
                );
            }

            $cart[$productNo] = $quantity;
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

            $items[] = [
                'product' => $product,
                'quantity' => $quantity,
                'lineTotal' => $lineTotal,
            ];

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
                $cleanCart[$productNo] = min(self::MAX_QUANTITY_PER_PRODUCT, $quantity);
            }
        }

        $this->session->set('cart', $cleanCart);
    }
}