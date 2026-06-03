<?php

require __DIR__ . '/../src/bootstrap.php';

$view = app('view');
$csrf = app('csrf');
$productsRepository = app('products');
$search = trim($_GET['search'] ?? '');

$view->header('Shop');

try {
    $products = $productsRepository->searchAvailable($search);
    $latestNews = app('news')->latestPublished();
    $homepageTestimonials = app('testimonials')->approvedLimit(3);
} catch (Throwable $error) {
    echo '<p class="error">' . e(dbErrorMessage($error)) . '</p>';
    $view->footer();
    exit;
}
?>

<section class="hero">
    <div class="hero-content">
        <p class="eyebrow">Darwin local art marketplace</p>
        <h1>Discover original artworks from a small Darwin art company</h1>
        <p>
            Browse available artworks, view details, add pieces to your cart, and submit a purchase request
            directly through our online store.
        </p>
        <div class="actions">
            <a class="button" href="#available-artworks">Browse artworks</a>
            <a class="button secondary" href="/testimonials.php">Read testimonials</a>
        </div>
    </div>

    <div class="hero-panel">
        <h2>Why shop with us?</h2>
        <ul class="feature-list">
            <li>Original Darwin-inspired artworks</li>
            <li>Clear product details before ordering</li>
            <li>Simple online purchase request process</li>
            <li>Positive customer feedback </li>
        </ul>
    </div>
</section>

<?php if ($latestNews): ?>
    <section class="panel news-panel">
        <p class="eyebrow">Latest news</p>
        <h2><?= e($latestNews['title']) ?></h2>
        <p><?= nl2br(e($latestNews['message'])) ?></p>
    </section>
<?php endif; ?>

<section id="available-artworks" class="section-block">
    <div class="section-heading">
        <div>
            <p class="eyebrow">Available artworks</p>
            <h2>Shop current pieces</h2>
        </div>
    </div>

    <section class="panel">
        <form method="get" action="/#available-artworks">
            <label>
                Search artworks
                <input
                    type="search"
                    name="search"
                    value="<?= e($search) ?>"
                    placeholder="Search by artwork name, category, colour, or size"
                >
            </label>

            <div class="actions">
                <button type="submit">Search</button>

                <?php if ($search !== ''): ?>
                    <a class="button secondary" href="/#available-artworks">Clear search</a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <?php if (!$products): ?>
        <section class="panel empty-state">
            <?php if ($search !== ''): ?>
                <h2>No artworks found</h2>
                <p>No available artworks matched your search for "<?= e($search) ?>". Try another keyword.</p>
            <?php else: ?>
                <h2>No artworks are currently available</h2>
                <p>Please check back later for new Darwin artworks.</p>
            <?php endif; ?>
        </section>
    <?php else: ?>
        <?php if ($search !== ''): ?>
            <p class="muted">
                Showing <?= count($products) ?> result<?= count($products) === 1 ? '' : 's' ?>
                for "<?= e($search) ?>".
            </p>
        <?php endif; ?>

        <section class="grid product-grid" aria-label="Available artworks">
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
                        <p class="badge"><?= e($product['category']) ?></p>

                        <h3>
                            <a href="/product.php?id=<?= (int) $product['product_no'] ?>">
                                <?= e($product['description']) ?>
                            </a>
                        </h3>

                        <p class="muted product-meta">
                            <?php if ($product['colour']): ?>
                                Colour: <?= e($product['colour']) ?>
                            <?php endif; ?>

                            <?php if ($product['size']): ?>
                                <?= $product['colour'] ? ' | ' : '' ?>Size: <?= e($product['size']) ?>
                            <?php endif; ?>
                        </p>

                        <p class="price"><?= money($product['price']) ?></p>
                    </div>

                    <form class="card-action-form" method="post" action="/cart.php">
                        <input type="hidden" name="csrf_token" value="<?= e($csrf->token()) ?>">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_no" value="<?= (int) $product['product_no'] ?>">

                        <label>
                            Quantity
                            <input type="number" name="quantity" value="1" min="1" max="20" required>
                        </label>

                        <div class="actions">
                            <button type="submit">Add to cart</button>
                            <a class="button secondary" href="/product.php?id=<?= (int) $product['product_no'] ?>">Details</a>
                        </div>
                    </form>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</section>

<?php if ($homepageTestimonials): ?>
    <section class="panel homepage-testimonials">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Customer feedback</p>
                <h2>What our customers say</h2>
            </div>
            <a href="/testimonials.php">View all testimonials</a>
        </div>

        <section class="grid testimonial-grid" aria-label="Customer testimonials">
            <?php foreach ($homepageTestimonials as $testimonial): ?>
                <article class="card testimonial-card">
                    <h3><?= e($testimonial['customer_name']) ?></h3>

                    <p class="rating-stars" aria-label="<?= (int) $testimonial['rating'] ?> out of 5 stars">
                        <?= e(ratingStars($testimonial['rating'])) ?>
                    </p>

                    <p><?= nl2br(e($testimonial['message'])) ?></p>
                </article>
            <?php endforeach; ?>
        </section>
    </section>
<?php endif; ?>

<?php $view->footer(); ?>