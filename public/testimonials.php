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

        if (!$email || $name === '' || $message === '') {
            throw new RuntimeException('Please provide your name, email, and testimonial.');
        }

        $testimonialsRepository->create($email, $name, $message);
        $session->flash('Thank you. Your testimonial is waiting for moderation.');
        redirect('/testimonials.php');
    } catch (Throwable $error) {
        $testimonialError = $error->getMessage();
    }
}

$view->header('Testimonials');

try {
    $testimonials = $testimonialsRepository->approved();
} catch (Throwable $error) {
    echo '<p class="error">' . e(dbErrorMessage($error)) . '</p>';
    $view->footer();
    exit;
}
?>

<h1>Testimonials</h1>

<?php if (!empty($testimonialError)): ?>
    <p class="error"><?= e($testimonialError) ?></p>
<?php endif; ?>

<section class="grid">
    <?php foreach ($testimonials as $testimonial): ?>
        <article class="card">
            <h2><?= e($testimonial['customer_name']) ?></h2>
            <p><?= nl2br(e($testimonial['message'])) ?></p>
        </article>
    <?php endforeach; ?>
</section>

<?php if (!$testimonials): ?>
    <p>No approved testimonials yet.</p>
<?php endif; ?>

<section class="panel">
    <h2>Leave a testimonial</h2>
    <form method="post" action="/testimonials.php">
        <input type="hidden" name="csrf_token" value="<?= e($csrf->token()) ?>">
        <label>Name <input name="customer_name" required></label>
        <label>Email <input type="email" name="customer_email" required></label>
        <label>Message <textarea name="message" required></textarea></label>
        <button type="submit">Submit for moderation</button>
    </form>
</section>

<?php $view->footer(); ?>
