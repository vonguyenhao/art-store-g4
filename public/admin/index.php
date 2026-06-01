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

<section class="admin-page-header">
    <p class="muted">Store administration</p>
    <h1>Admin dashboard</h1>
</section>

<section class="grid admin-dashboard-grid">
    <article class="card admin-card">
        <h2>Products</h2>
        <p class="price"><?= (int) $counts['products'] ?></p>
        <p class="muted">Add artworks, update details, and control storefront availability.</p>
        <a class="button" href="/admin/products.php">Manage products</a>
    </article>
    <article class="card admin-card">
        <h2>Orders</h2>
        <p class="price"><?= (int) $counts['orders'] ?></p>
        <p class="muted">Review submitted purchase orders and customer delivery details.</p>
        <a class="button" href="/admin/orders.php">View orders</a>
    </article>
    <article class="card admin-card">
        <h2>Pending testimonials</h2>
        <p class="price"><?= (int) $counts['pending'] ?></p>
        <p class="muted">Approve or reject customer comments before publication.</p>
        <a class="button" href="/admin/testimonials.php">Moderate</a>
    </article>
    <article class="card admin-card">
        <h2>Front page news</h2>
        <p class="muted">Publish the latest owner message.</p>
        <a class="button" href="/admin/news.php">Manage news</a>
    </article>
</section>

<?php $view->footer(); ?>
