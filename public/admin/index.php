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

<section class="section-block">
    <div class="section-heading">
        <div>
            <p class="eyebrow">Order workflow</p>
            <h2>Order status overview</h2>
        </div>
    </div>

    <section class="grid admin-dashboard-grid">
        <article class="card admin-card">
            <h2>Pending</h2>
            <p class="price"><?= (int) $counts['pending_orders'] ?></p>
            <p class="muted">Orders waiting for staff review.</p>
        </article>

        <article class="card admin-card">
            <h2>Processing</h2>
            <p class="price"><?= (int) $counts['processing_orders'] ?></p>
            <p class="muted">Orders currently being prepared.</p>
        </article>

        <article class="card admin-card">
            <h2>Completed</h2>
            <p class="price"><?= (int) $counts['completed_orders'] ?></p>
            <p class="muted">Orders that have been finalised.</p>
        </article>

        <article class="card admin-card">
            <h2>Cancelled</h2>
            <p class="price"><?= (int) $counts['cancelled_orders'] ?></p>
            <p class="muted">Orders cancelled by staff.</p>
        </article>
    </section>
</section>

<?php $view->footer(); ?>