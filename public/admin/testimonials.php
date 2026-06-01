<?php

require __DIR__ . '/../../src/bootstrap.php';

$auth = app('auth');
$csrf = app('csrf');
$session = app('session');
$testimonialsRepository = app('testimonials');
$view = app('view');
$auth->require();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf->verify($_POST);

    $status = $_POST['status'] === 'approved' ? 'approved' : 'rejected';
    $testimonialsRepository->moderate((int) $_POST['testimonial_id'], $status);
    $session->flash('Testimonial updated.');
    redirect('/admin/testimonials.php');
}

$view->header('Moderate testimonials');

try {
    $testimonials = $testimonialsRepository->all();
} catch (Throwable $error) {
    echo '<p class="error">' . e(dbErrorMessage($error)) . '</p>';
    $view->footer();
    exit;
}
?>

<section class="admin-page-header">
    <p><a href="/admin/index.php">Back to dashboard</a></p>
    <h1>Moderate testimonials</h1>
    <p class="muted">Approve customer testimonials before they appear on the public page.</p>
</section>

<?php if (!$testimonials): ?>
    <section class="panel empty-state">
        <h2>No testimonials submitted</h2>
        <p>Customer testimonial submissions will appear here for moderation.</p>
    </section>
<?php else: ?>
<div class="table-wrap">
<table class="admin-table">
    <thead>
        <tr>
            <th>Customer</th>
            <th>Rating</th>
            <th>Message</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($testimonials as $testimonial): ?>
            <tr>
                <td><?= e($testimonial['customer_name']) ?><br><?= e($testimonial['customer_email']) ?></td>
                <td><span class="rating-stars"><?= e(ratingStars($testimonial['rating'])) ?></span><br><?= (int) $testimonial['rating'] ?>/5</td>
                <td><?= nl2br(e($testimonial['message'])) ?></td>
                <td><?= e($testimonial['status']) ?></td>
                <td>
                    <form class="actions" method="post" action="/admin/testimonials.php">
                        <input type="hidden" name="csrf_token" value="<?= e($csrf->token()) ?>">
                        <input type="hidden" name="testimonial_id" value="<?= (int) $testimonial['testimonial_id'] ?>">
                        <button type="submit" name="status" value="approved">Approve</button>
                        <button class="secondary" type="submit" name="status" value="rejected">Reject</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>

<?php $view->footer(); ?>
