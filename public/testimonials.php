<?php

require __DIR__ . '/../src/bootstrap.php';

$csrf = app('csrf');
$session = app('session');
$testimonialsRepository = app('testimonials');
$view = app('view');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf->verify($_POST);

    try {
        $email = filter_var($_POST['customer_email'] ?? '', FILTER_VALIDATE_EMAIL);
        $name = trim($_POST['customer_name'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $rating = filter_var($_POST['rating'] ?? null, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 5],
        ]);

        if (!$email || $name === '' || $message === '' || !$rating) {
            throw new RuntimeException('Please provide your name, email, rating, and testimonial.');
        }

        $testimonialsRepository->create($email, $name, $message, (int) $rating);
        $session->flash('Testimonial submitted for moderation.');
        redirect('/testimonials.php');
    } catch (Throwable $error) {
        $testimonialError = $error->getMessage();
    }
}

$view->header('Testimonials');

$selectedRating = filter_input(INPUT_GET, 'rating', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 5],
]);

try {
    $testimonials = $testimonialsRepository->approved($selectedRating ? (int) $selectedRating : null);
} catch (Throwable $error) {
    echo '<p class="error">' . e(dbErrorMessage($error)) . '</p>';
    $view->footer();
    exit;
}
?>

<h1>Testimonials</h1>

<nav class="rating-filter" aria-label="Filter testimonials by rating">
    <a class="<?= !$selectedRating ? 'active' : '' ?>" href="/testimonials.php">All</a>
    <?php for ($ratingOption = 5; $ratingOption >= 1; $ratingOption--): ?>
        <a class="<?= (int) $selectedRating === $ratingOption ? 'active' : '' ?>" href="/testimonials.php?rating=<?= $ratingOption ?>">
            <?= $ratingOption ?> stars
        </a>
    <?php endfor; ?>
</nav>

<?php if (!empty($testimonialError)): ?>
    <p class="error"><?= e($testimonialError) ?></p>
<?php endif; ?>

<section class="grid testimonial-grid">
    <?php foreach ($testimonials as $testimonial): ?>
        <article class="card testimonial-card">
            <h2><?= e($testimonial['customer_name']) ?></h2>
            <p class="rating-stars" aria-label="<?= (int) $testimonial['rating'] ?> out of 5 stars"><?= e(ratingStars($testimonial['rating'])) ?></p>
            <p><?= nl2br(e($testimonial['message'])) ?></p>
        </article>
    <?php endforeach; ?>
</section>

<?php if (!$testimonials): ?>
    <section class="panel empty-state">
        <h2>No testimonials yet</h2>
        <p>Approved customer testimonials will appear here after moderation.</p>
    </section>
<?php endif; ?>

<section class="panel testimonial-form-panel">
    <h2>Leave a testimonial</h2>
    <form class="testimonial-form" method="post" action="/testimonials.php">
        <input type="hidden" name="csrf_token" value="<?= e($csrf->token()) ?>">
        <label>Name <input name="customer_name" required></label>
        <label>Email <input type="email" name="customer_email" required></label>
        <label>Rating
            <select name="rating" required>
                <option value="">Choose rating</option>
                <option value="5">5 stars</option>
                <option value="4">4 stars</option>
                <option value="3">3 stars</option>
                <option value="2">2 stars</option>
                <option value="1">1 star</option>
            </select>
        </label>
        <label class="message-field">Message <textarea name="message" required></textarea></label>
        <button type="submit">Submit</button>
    </form>
</section>

<?php $view->footer(); ?>
