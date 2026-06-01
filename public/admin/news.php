<?php

require __DIR__ . '/../../src/bootstrap.php';

$auth = app('auth');
$csrf = app('csrf');
$session = app('session');
$newsRepository = app('news');
$view = app('view');
$auth->require();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf->verify($_POST);

    try {
        $title = trim($_POST['title'] ?? '');
        $message = trim($_POST['message'] ?? '');
        if ($title === '' || $message === '') {
            throw new RuntimeException('Title and message are required.');
        }

        $newsRepository->create($title, $message, isset($_POST['is_published']));
        $session->flash('News item added.');
        redirect('/admin/news.php');
    } catch (Throwable $error) {
        $newsError = $error->getMessage();
    }
}

$view->header('News');

try {
    $newsItems = $newsRepository->all();
} catch (Throwable $error) {
    echo '<p class="error">' . e(dbErrorMessage($error)) . '</p>';
    $view->footer();
    exit;
}
?>

<section class="admin-page-header">
    <p><a href="/admin/index.php">Back to dashboard</a></p>
    <h1>News management</h1>
    <p class="muted">Create owner updates for the storefront homepage.</p>
</section>

<?php if (!empty($newsError)): ?>
    <p class="error"><?= e($newsError) ?></p>
<?php endif; ?>

<section class="panel">
    <h2>Add news item</h2>
    <form method="post" action="/admin/news.php">
        <input type="hidden" name="csrf_token" value="<?= e($csrf->token()) ?>">
        <label>Title <input name="title" required></label>
        <label>Message <textarea name="message" required></textarea></label>
        <label class="checkbox-label"><input type="checkbox" name="is_published" value="1" checked> Published</label>
        <button type="submit">Add news</button>
    </form>
</section>

<?php if (!$newsItems): ?>
    <section class="panel empty-state">
        <h2>No news items yet</h2>
        <p>Published news items will appear on the storefront homepage.</p>
    </section>
<?php else: ?>
<section class="grid admin-list-grid">
    <?php foreach ($newsItems as $news): ?>
        <article class="card admin-card">
            <h2><?= e($news['title']) ?></h2>
            <p><?= nl2br(e($news['message'])) ?></p>
            <p class="muted"><?= $news['is_published'] ? 'Published' : 'Draft' ?> | <?= e($news['created_at']) ?></p>
        </article>
    <?php endforeach; ?>
</section>
<?php endif; ?>

<?php $view->footer(); ?>
