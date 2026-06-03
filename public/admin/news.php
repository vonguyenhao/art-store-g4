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

        if ($action === 'create') {
            $newsRepository->create(
                trim($_POST['title'] ?? ''),
                trim($_POST['message'] ?? ''),
                isset($_POST['is_published'])
            );
            $session->flash('News item added.');
        } elseif ($action === 'update') {
            $newsRepository->update(
                (int) ($_POST['news_id'] ?? 0),
                trim($_POST['title'] ?? ''),
                trim($_POST['message'] ?? ''),
                isset($_POST['is_published'])
            );
            $session->flash('News item updated.');
        } elseif ($action === 'delete') {
            $newsRepository->delete((int) ($_POST['news_id'] ?? 0));
            $session->flash('News item deleted.');
        } elseif ($action === 'set_homepage') {
            $newsRepository->setHomepageNews((int) ($_POST['news_id'] ?? 0));
            $session->flash('Homepage news updated.');
        } elseif ($action === 'unpublish') {
            $newsRepository->unpublish((int) ($_POST['news_id'] ?? 0));
            $session->flash('News item changed to draft.');
        } else {
            throw new RuntimeException('Invalid news action.');
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
        Create, edit, remove, and choose which news item appears on the customer homepage.
    </p>
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
                <input type="checkbox" name="is_published" value="1">
                Set as homepage news now
            </label>

            <button type="submit">Add news</button>
        </form>
    </section>
<?php endif; ?>

<?php if (!$newsItems): ?>
    <section class="panel empty-state">
        <h2>No news items yet</h2>
        <p>Create a news item and select it for the customer homepage.</p>
    </section>
<?php else: ?>

<section class="grid admin-list-grid">
    <?php foreach ($newsItems as $news): ?>
        <?php if ($editingNewsId !== 0 && $editingNewsId !== (int) $news['news_id']): ?>
            <?php continue; ?>
        <?php endif; ?>

        <article class="card admin-card admin-news-card">
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
                        <input type="checkbox" name="is_published" value="1" <?= (int) $news['is_published'] === 1 ? 'checked' : '' ?>>
                        Show on homepage
                    </label>

                    <div class="actions">
                        <button type="submit">Save changes</button>
                        <a class="button secondary" href="/admin/news.php">Back</a>
                    </div>
                </form>
            <?php else: ?>
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
                    <a class="button" href="/admin/news.php?edit=<?= (int) $news['news_id'] ?>">Edit</a>

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
