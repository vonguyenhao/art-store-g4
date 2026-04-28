<?php

declare(strict_types=1);

namespace App\Service;

use App\Core\Session;
use App\Repository\ProductRepository;

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
        $cart = $this->raw();
        $cart[$productNo] = ($cart[$productNo] ?? 0) + max(1, min(20, $quantity));
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

        foreach ($this->products->availableByIds(array_keys($cart)) as $product) {
            $quantity = (int) ($cart[(int) $product['product_no']] ?? 0);
            if ($quantity < 1) {
                continue;
            }

            $lineTotal = $quantity * (float) $product['price'];
            $items[] = compact('product', 'quantity', 'lineTotal');
            $total += $lineTotal;
        }

        return [$items, $total];
    }

    private function set(array $cart): void
    {
        $this->session->set('cart', array_filter($cart, fn ($quantity) => (int) $quantity > 0));
    }
}
