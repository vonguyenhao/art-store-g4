<?php

require __DIR__ . '/../../src/bootstrap.php';

$auth = app('auth');
$ordersRepository = app('orders');
$view = app('view');
$auth->require();

$view->header('Admin dashboard');

try {
    $counts = $ordersRepository->counts();
} catch (Throwable $error) {
    echo '<p class="error">' . e(dbErrorMessage($error)) . '</p>';
    $view->footer();
    exit;
}
?>

<h1>Admin dashboard</h1>

<section class="grid">
    <article class="card">
        <h2>Products</h2>
        <p class="price"><?= (int) $counts['products'] ?></p>
        <a class="button" href="/admin/products.php">Manage products</a>
    </article>
    <article class="card">
        <h2>Orders</h2>
        <p class="price"><?= (int) $counts['orders'] ?></p>
        <a class="button" href="/admin/orders.php">View orders</a>
    </article>
    <article class="card">
        <h2>Pending testimonials</h2>
        <p class="price"><?= (int) $counts['pending'] ?></p>
        <a class="button" href="/admin/testimonials.php">Moderate</a>
    </article>
    <article class="card">
        <h2>Front page news</h2>
        <p class="muted">Publish the latest owner message.</p>
        <a class="button" href="/admin/news.php">Manage news</a>
    </article>
</section>

<?php $view->footer(); ?>
