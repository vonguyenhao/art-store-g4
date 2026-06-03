<?php

declare(strict_types=1);

namespace App\Core;

use App\Service\AuthService;
use App\Service\CartService;

final class View
{
    public function __construct(
        private readonly array $config,
        private readonly Session $session,
        private readonly CartService $cart,
        private readonly AuthService $auth
    ) {
    }

    public function header(string $title): void
    {
        $flash = $this->session->flash();
        $isAdminPage = str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin');
        $cartCount = $this->cart->count();
        ?>
        <!doctype html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title><?= e($title) ?> | <?= e($this->config['app_name']) ?></title>
            <link rel="stylesheet" href="/assets/style.css">
        </head>

        <body class="<?= $isAdminPage ? 'admin-section' : 'public-section' ?>">
        <header class="site-header">
            <a class="brand" href="/"><?= e($this->config['app_name']) ?></a>

            <nav>
                <?php if ($this->auth->check()): ?>
                    <a href="/">Shop</a>
                    <a href="/admin/index.php">Admin Dashboard</a>
                    <a href="/admin/products.php">Products</a>
                    <a href="/admin/orders.php">Orders</a>
                    <a href="/admin/news.php">News</a>
                    <a href="/admin/testimonials.php">Testimonials Moderation</a>
                    <a href="/admin/logout.php">Logout</a>
                <?php else: ?>
                    <a href="/">Shop</a>
                    <a href="/testimonials.php">Testimonials</a>
                    <a href="/cart.php">
                        Cart<?= $cartCount > 0 ? ' (' . (int) $cartCount . ')' : '' ?>
                    </a>
                    <a href="/admin/login.php">Admin</a>
                <?php endif; ?>
            </nav>
        </header>

        <main class="container">
            <?php if ($flash): ?>
                <p class="flash"><?= e($flash) ?></p>
            <?php endif; ?>
        <?php
    }

    public function footer(): void
    {
        $isAdminPage = str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin');
        $cartCount = $this->cart->count();
        ?>
        </main>

        <?php if (!$isAdminPage): ?>
            <a class="floating-cart-button" href="/cart.php" aria-label="View shopping cart">
                <span class="floating-cart-icon">🛒</span>

                <?php if ($cartCount > 0): ?>
                    <span class="floating-cart-count"><?= (int) $cartCount ?></span>
                <?php endif; ?>

                <span class="floating-cart-text">Cart</span>
            </a>
        <?php endif; ?>

        <footer class="site-footer">Darwin Art Store - CDU_HIT326_Group 4</footer>
        </body>
        </html>
        <?php
    }
}