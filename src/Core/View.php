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
        ?>
        <!doctype html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title><?= e($title) ?> | <?= e($this->config['app_name']) ?></title>
            <link rel="stylesheet" href="/assets/style.css">
        </head>
        <body>
        <header class="site-header">
            <a class="brand" href="/"><?= e($this->config['app_name']) ?></a>
            <nav>
                <a href="/">Shop</a>
                <a href="/testimonials.php">Testimonials</a>
                <a href="/cart.php">Cart (<?= $this->cart->count() ?>)</a>
                <?php if ($this->auth->check()): ?>
                    <a href="/admin/index.php">Admin</a>
                    <a href="/admin/logout.php">Logout</a>
                <?php else: ?>
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
        ?>
        </main>
        <footer class="site-footer">Functional prototype for HIT326 Option 2.</footer>
        </body>
        </html>
        <?php
    }
}
