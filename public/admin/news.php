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
        $action = $_POST['action'] ?? 'create';

        if ($action === 'create') {
            $title = trim($_POST['title'] ?? '');
            $message = trim($_POST['message'] ?? '');

            $newsRepository->create($title, $message, isset($_POST['is_published']));
            $session->flash('News item added.');
        }

        if ($action === 'set_homepage') {
            $newsRepository->setHomepageNews((int) ($_POST['news_id'] ?? 0));
            $session->flash('Homepage news updated.');
        }

        if ($action === 'unpublish') {
            $newsRepository->unpublish((int) ($_POST['news_id'] ?? 0));
            $session->flash('News item changed to draft.');
        }

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
    <p class="muted">
        Create news updates and choose which item appears on the customer homepage.
    </p>
</section>

<?php if (!empty($newsError)): ?>
    <p class="error"><?= e($newsError) ?></p>
<?php endif; ?>

<section class="panel">
    <h2>Add news item</h2>

    <form method="post" action="/admin/news.php">
        <input type="hidden" name="csrf_token" value="<?= e($csrf->token()) ?>">
        <input type="hidden" name="action" value="create">

        <label>
            Title
            <input name="title" required>
        </label>

        <label>
            Message
            <textarea name="message" required></textarea>
        </label>

        <label class="checkbox-label">
            <input type="checkbox" name="is_published" value="1">
            Set as homepage news now
        </label>

        <button type="submit">Add news</button>
    </form>
</section>

<?php if (!$newsItems): ?>
    <section class="panel empty-state">
        <h2>No news items yet</h2>
        <p>Create a news item and select it for the customer homepage.</p>
    </section>
<?php else: ?>

<section class="grid admin-list-grid">
    <?php foreach ($newsItems as $news): ?>
        <article class="card admin-card admin-news-card">
            <?php if ((int) $news['is_published'] === 1): ?>
                <p class="badge">Showing on homepage</p>
            <?php else: ?>
                <p class="badge">Draft</p>
            <?php endif; ?>

            <h2><?= e($news['title']) ?></h2>

            <p><?= nl2br(e($news['message'])) ?></p>

            <p class="muted">
                Created: <?= e($news['created_at']) ?>
            </p>

            <div class="actions admin-news-actions">
                <?php if ((int) $news['is_published'] !== 1): ?>
                    <form method="post" action="/admin/news.php">
                        <input type="hidden" name="csrf_token" value="<?= e($csrf->token()) ?>">
                        <input type="hidden" name="action" value="set_homepage">
                        <input type="hidden" name="news_id" value="<?= (int) $news['news_id'] ?>">
                        <button type="submit">Set as homepage news</button>
                    </form>
                <?php else: ?>
                    <form method="post" action="/admin/news.php">
                        <input type="hidden" name="csrf_token" value="<?= e($csrf->token()) ?>">
                        <input type="hidden" name="action" value="unpublish">
                        <input type="hidden" name="news_id" value="<?= (int) $news['news_id'] ?>">
                        <button class="secondary" type="submit">Hide from homepage</button>
                    </form>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<?php endif; ?>

<?php $view->footer(); ?>