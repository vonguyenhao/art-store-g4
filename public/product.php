<?php

require __DIR__ . '/../src/bootstrap.php';

$productsRepository = app('products');
$csrf = app('csrf');
$view = app('view');

$productNo = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);

$view->header('Artwork details');

try {
    $product = $productNo ? $productsRepository->availableById((int) $productNo) : null;
} catch (Throwable $error) {
    echo '<p class="error">' . e(dbErrorMessage($error)) . '</p>';
    $view->footer();
    exit;
}
?>

<?php if (!$product): ?>
    <section class="panel">
        <h1>Artwork unavailable</h1>
        <p>This artwork is not available, or the link is no longer valid.</p>
        <a class="button" href="/">Browse available artworks</a>
    </section>
<?php else: ?>
    <section class="product-detail">
        <div class="artwork-media artwork-media-large">
            <?php if ($product['image_path']): ?>
                <img src="<?= e($product['image_path']) ?>" alt="<?= e($product['description']) ?>">
            <?php else: ?>
                <span class="artwork-placeholder">Artwork image coming soon</span>
            <?php endif; ?>
        </div>
        <div class="panel product-detail-info">
            <h1><?= e($product['description']) ?></h1>
            <p class="muted product-meta">
                <?= e($product['category']) ?>
                <?php if ($product['colour']): ?> | <?= e($product['colour']) ?><?php endif; ?>
                <?php if ($product['size']): ?> | <?= e($product['size']) ?><?php endif; ?>
            </p>
            <p class="price"><?= money($product['price']) ?></p>
            <form class="compact-form" method="post" action="/cart.php">
                <input type="hidden" name="csrf_token" value="<?= e($csrf->token()) ?>">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_no" value="<?= (int) $product['product_no'] ?>">
                <label class="quantity-field">
                    Quantity
                    <input class="quantity-input" type="number" name="quantity" value="1" min="1" max="20" required>
                </label>
                <div class="actions">
                    <button type="submit">Add to cart</button>
                    <a class="button secondary" href="/">Back to shop</a>
                </div>
            </form>
        </div>
    </section>
<?php endif; ?>

<?php $view->footer(); ?>
