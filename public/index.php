<?php

require __DIR__ . '/../src/bootstrap.php';

$view = app('view');
$csrf = app('csrf');
$view->header('Shop');

try {
    $products = app('products')->available();
    $latestNews = app('news')->latestPublished();
} catch (Throwable $error) {
    echo '<p class="error">' . e(dbErrorMessage($error)) . '</p>';
    $view->footer();
    exit;
}
?>

<?php if ($latestNews): ?>
    <section class="panel">
        <h1><?= e($latestNews['title']) ?></h1>
        <p><?= nl2br(e($latestNews['message'])) ?></p>
    </section>
<?php else: ?>
    <h1>Available artworks</h1>
<?php endif; ?>

<section class="grid" aria-label="Available artworks">
    <?php foreach ($products as $product): ?>
        <article class="card">
            <h2><?= e($product['description']) ?></h2>
            <p class="muted">
                <?= e($product['category']) ?>
                <?php if ($product['colour']): ?> | <?= e($product['colour']) ?><?php endif; ?>
                <?php if ($product['size']): ?> | <?= e($product['size']) ?><?php endif; ?>
            </p>
            <p class="price"><?= money($product['price']) ?></p>
            <form method="post" action="/cart.php">
                <input type="hidden" name="csrf_token" value="<?= e($csrf->token()) ?>">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_no" value="<?= (int) $product['product_no'] ?>">
                <label>
                    Quantity
                    <input type="number" name="quantity" value="1" min="1" max="20">
                </label>
                <button type="submit">Add to cart</button>
            </form>
        </article>
    <?php endforeach; ?>
</section>

<?php if (!$products): ?>
    <p>No artworks are currently available.</p>
<?php endif; ?>

<?php $view->footer(); ?>
