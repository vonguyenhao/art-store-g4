<?php

declare(strict_types=1);

session_start();

$config = require __DIR__ . '/config.php';

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $path = __DIR__ . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

require __DIR__ . '/functions.php';

$session = new App\Core\Session();
$database = new App\Core\Database($config['db']);
$products = new App\Repository\ProductRepository($database);
$news = new App\Repository\NewsRepository($database);
$testimonials = new App\Repository\TestimonialRepository($database);
$admins = new App\Repository\AdminRepository($database);
$orders = new App\Repository\OrderRepository($database);
$cart = new App\Service\CartService($session, $products);
$auth = new App\Service\AuthService($session, $admins);
$csrf = new App\Core\Csrf($session);
$notifier = new App\Service\FileOrderNotifier(
    $config['business_email'],
    dirname(__DIR__) . '/storage/mail/orders.log'
);
$checkout = new App\Service\CheckoutService($cart, $orders, $notifier);
$view = new App\Core\View($config, $session, $cart, $auth);

$container = compact(
    'config',
    'session',
    'database',
    'products',
    'news',
    'testimonials',
    'admins',
    'orders',
    'cart',
    'auth',
    'csrf',
    'notifier',
    'checkout',
    'view'
);
