<?php

require __DIR__ . '/../src/bootstrap.php';

$view = app('view');
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $db = app('database');

    $customer = $db->fetchOne(
        "SELECT * FROM customers WHERE email = ?",
        [$email]
    );

    if (
        $customer &&
        !empty($customer['password_hash']) &&
        password_verify($password, $customer['password_hash'])
    ) {
        $_SESSION['customer_email'] = $customer['email'];
        $_SESSION['customer_name'] = $customer['first_name'];

        header('Location: /account.php');
        exit;
    }

    $error = 'Invalid login details.';
}

$view->header('Customer Login');
?>

<h1>Customer Login</h1>

<?php if ($error): ?>
    <p class="error"><?= e($error) ?></p>
<?php endif; ?>

<form method="POST">
    <input type="email" name="email" placeholder="Email">
    <input type="password" name="password" placeholder="Password">
    <button type="submit">Login</button>
</form>

<?php $view->footer(); ?>