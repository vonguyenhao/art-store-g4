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
    <h1>Artwork not available</h1>
    <p>This artwork is currently unavailable or may have been removed.</p>
    <a class="button" href="/">Browse artworks</a>
</section>

<?php else: ?>

<section class="product-detail">

    <div class="artwork-media artwork-media-large">
        <?php if ($product['image_path']): ?>
            <img src="/product_image.php?id=<?= (int) $product['product_no'] ?>"
                 alt="<?= e($product['description']) ?>">
        <?php else: ?>
            <span class="artwork-placeholder">Image coming soon</span>
        <?php endif; ?>
    </div>

    <div class="panel product-detail-info">

        <p class="badge">
            <?= $product['is_available'] ? 'Available' : 'Currently Unavailable' ?>
        </p>

        <h1><?= e($product['description']) ?></h1>

        <p class="muted product-meta">
            <?= e($product['category']) ?>
            <?php if ($product['colour']): ?> | <?= e($product['colour']) ?><?php endif; ?>
            <?php if ($product['size']): ?> | <?= e($product['size']) ?><?php endif; ?>
        </p>

        <p class="price"><?= money($product['price']) ?></p>

        <div class="product-extra">
            <p>- Local Darwin artwork</p>
            <p>- Secure packaging included</p>
            <p>- Delivery available Australia-wide</p>
            <p>- Maximum 20 units per order</p>
        </div>

        <?php if ($product['is_available']): ?>
            <form class="compact-form" method="post" action="/cart.php">

                <input type="hidden" name="csrf_token" value="<?= e($csrf->token()) ?>">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_no" value="<?= (int) $product['product_no'] ?>">

                <label class="quantity-field">
                    Quantity
                    <input class="quantity-input"
                           type="number"
                           name="quantity"
                           value="1"
                           min="1"
                           max="20"
                           required>
                </label>

                <div class="actions">
                    <button type="submit">Add to Cart</button>
                    <a class="button secondary" href="/">Back to Shop</a>
                </div>

            </form>
        <?php else: ?>
            <p class="error">This artwork is currently unavailable.</p>
        <?php endif; ?>

    </div>
</section>

<section class="panel product-information">
    <h2>Artwork Information</h2>

    <div class="product-info-grid">

        <div class="info-item">
            <strong>Category</strong>
            <span><?= e($product['category']) ?></span>
        </div>

        <?php if ($product['size']): ?>
            <div class="info-item">
                <strong>Size</strong>
                <span><?= e($product['size']) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($product['colour']): ?>
            <div class="info-item">
                <strong>Colours</strong>
                <span><?= e($product['colour']) ?></span>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php endif; ?>

<?php $view->footer(); ?>