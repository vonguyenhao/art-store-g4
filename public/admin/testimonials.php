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

<h1>Moderate testimonials</h1>

<table>
    <thead>
        <tr>
            <th>Customer</th>
            <th>Message</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($testimonials as $testimonial): ?>
            <tr>
                <td><?= e($testimonial['customer_name']) ?><br><?= e($testimonial['customer_email']) ?></td>
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

<?php if (!$testimonials): ?>
    <p>No testimonials have been submitted yet.</p>
<?php endif; ?>

<?php $view->footer(); ?>
