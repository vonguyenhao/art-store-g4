<?php

require __DIR__ . '/../../src/bootstrap.php';

$auth = app('auth');
$csrf = app('csrf');
$session = app('session');
$view = app('view');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf->verify($_POST);

    try {
        if ($auth->attempt(trim($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''))) {
            $session->flash('Logged in.');
            redirect('/admin/index.php');
        }

        $loginError = 'Invalid admin email or password.';
    } catch (Throwable $error) {
        $loginError = dbErrorMessage($error);
    }
}

$view->header('Admin login');
?>

<section class="panel">
    <h1>Admin login</h1>
    <?php if (!empty($loginError)): ?>
        <p class="error"><?= e($loginError) ?></p>
    <?php endif; ?>
    <form method="post" action="/admin/login.php">
        <input type="hidden" name="csrf_token" value="<?= e($csrf->token()) ?>">
        <label>Email <input type="email" name="email" required value="admin@example.com"></label>
        <label>Password <input type="password" name="password" required></label>
        <button type="submit">Login</button>
    </form>
    <p class="muted">Seed login after importing schema: admin@example.com / admin123</p>
</section>

<?php $view->footer(); ?>
