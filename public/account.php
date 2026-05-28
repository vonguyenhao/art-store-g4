<?php

require __DIR__ . '/../src/bootstrap.php';

$view = app('view');
$csrf = app('csrf');
$view->header('Shop');


?>

<?php

if (!isset($_SESSION['customer_email'])) {

    header('Location: login.php');
    exit;
}
?>

<h1>My Account</h1>

<p>
    Welcome,
    <?= htmlspecialchars($_SESSION['customer_name']) ?>
</p>

<a href="logout.php">
    Logout
</a>

<?php $view->footer(); ?>