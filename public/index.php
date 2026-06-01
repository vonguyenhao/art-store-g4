<?php

require __DIR__ . '/../src/bootstrap.php';

$view = app('view');
$csrf = app('csrf');
$view->header('Shop');


?>



<?php

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
        <article class="card product-card">
            <a class="artwork-media" href="/product.php?id=<?= (int) $product['product_no'] ?>" aria-label="View <?= e($product['description']) ?>">
                <?php if ($product['image_path']): ?>
                    <img src="/product_image.php?id=<?= (int) $product['product_no'] ?>" alt="<?= e($product['description']) ?>">
                <?php else: ?>
                    <span class="artwork-placeholder">Artwork image coming soon</span>
                <?php endif; ?>
            </a>
            <div class="product-card-body">
                <h2>
                    <a href="/product.php?id=<?= (int) $product['product_no'] ?>">
                        <?= e($product['description']) ?>
                    </a>
                </h2>
                <p class="muted product-meta">
                    <?= e($product['category']) ?>
                    <?php if ($product['colour']): ?> | <?= e($product['colour']) ?><?php endif; ?>
                    <?php if ($product['size']): ?> | <?= e($product['size']) ?><?php endif; ?>
                </p>
                <p class="price"><?= money($product['price']) ?></p>
                <p><a href="/product.php?id=<?= (int) $product['product_no'] ?>">View details</a></p>
            </div>
            <form method="post" action="/cart.php">
                <input type="hidden" name="csrf_token" value="<?= e($csrf->token()) ?>">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_no" value="<?= (int) $product['product_no'] ?>">
                <label>
                    Quantity
                    <input type="number" name="quantity" value="1" min="1" max="20" required>
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
