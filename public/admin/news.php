<?php

require __DIR__ . '/../../src/bootstrap.php';

$auth = app('auth');
$csrf = app('csrf');
$session = app('session');
$newsRepository = app('news');
$view = app('view');
$auth->require();

$editingNewsId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf->verify($_POST);

    try {
        $action = $_POST['action'] ?? 'create';

        if ($action === 'delete') {
            $newsId = (int) ($_POST['news_id'] ?? 0);
            if ($newsId < 1) {
                throw new RuntimeException('Invalid news item.');
            }

            $newsRepository->delete($newsId);
            $session->flash('News item deleted.');
            redirect('/admin/news.php');
        }

        $title = trim($_POST['title'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if ($title === '' || $message === '') {
            throw new RuntimeException('Title and message are required.');
        }

        if ($action === 'update') {
            $newsId = (int) ($_POST['news_id'] ?? 0);
            if ($newsId < 1) {
                throw new RuntimeException('Invalid news item.');
            }

            $newsRepository->update($newsId, $title, $message, isset($_POST['is_published']));
            $session->flash('News item updated.');
            redirect('/admin/news.php');
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
    <p class="muted">Create, edit, and remove owner updates for the storefront homepage.</p>
</section>

<?php if (!empty($newsError)): ?>
    <p class="error"><?= e($newsError) ?></p>
<?php endif; ?>

<?php if ($editingNewsId === 0): ?>
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
                <input type="checkbox" name="is_published" value="1" checked>
                Published
            </label>

            <button type="submit">Add news</button>
        </form>
    </section>
<?php endif; ?>

<?php if (!$newsItems): ?>
    <section class="panel empty-state">
        <h2>No news items yet</h2>
        <p>Published news items will appear on the storefront homepage.</p>
    </section>
<?php else: ?>
<section class="grid admin-list-grid">
    <?php foreach ($newsItems as $news): ?>

        <?php if ($editingNewsId !== 0 && $editingNewsId !== (int) $news['news_id']): ?>
            <?php continue; ?>
        <?php endif; ?>

        <article class="card admin-card">
            <?php if ($editingNewsId === (int) $news['news_id']): ?>
                <h2>Edit news item</h2>

                <form method="post" action="/admin/news.php">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf->token()) ?>">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="news_id" value="<?= (int) $news['news_id'] ?>">

                    <label>
                        Title
                        <input name="title" value="<?= e($news['title']) ?>" required>
                    </label>

                    <label>
                        Message
                        <textarea name="message" required><?= e($news['message']) ?></textarea>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" name="is_published" value="1" <?= $news['is_published'] ? 'checked' : '' ?>>
                        Published
                    </label>

                    <div class="actions">
                        <button type="submit">Save changes</button>
                        <a class="button secondary" href="/admin/news.php">Back</a>
                    </div>
                </form>
            <?php else: ?>
                <h2><?= e($news['title']) ?></h2>

                <p><?= nl2br(e($news['message'])) ?></p>

                <p class="muted">
                    <?= $news['is_published'] ? 'Published' : 'Draft' ?> |
                    <?= e($news['created_at']) ?>
                </p>

                <div class="actions">
                    <a class="button" href="/admin/news.php?edit=<?= (int) $news['news_id'] ?>">Edit</a>

                    <form method="post" action="/admin/news.php" onsubmit="return confirm('Delete this news item?');">
                        <input type="hidden" name="csrf_token" value="<?= e($csrf->token()) ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="news_id" value="<?= (int) $news['news_id'] ?>">

                        <button class="secondary" type="submit">Delete news</button>
                    </form>
                </div>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</section>
<?php endif; ?>

<?php $view->footer(); ?>