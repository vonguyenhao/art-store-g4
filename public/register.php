<?php

require __DIR__ . '/../src/bootstrap.php';

$view = app('view');
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');

        if (!$email || !$password || !$firstName || !$lastName) {
            $error = 'All fields are required.';
        } else {
            $db = app('database');

            $existingCustomer = $db->fetchOne(
                "SELECT email FROM customers WHERE email = ?",
                [$email]
            );

            if ($existingCustomer) {

                $error = 'Email already registered.';

            } else {

                $passwordHash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $db->execute("
                    INSERT INTO customers
                    (
                        email, first_name, last_name, address, city, state,
                        postcode, country, phone, password_hash
                    )
                    VALUES (?, ?, ?, '', '', '', '', 'Australia', '', ?)
                ", [
                    $email,
                    $firstName,
                    $lastName,
                    $passwordHash
                ]);

                header('Location: /login.php');
                exit;
            }
    }
}

$view->header('Register');
?>

<h1>Register</h1>

<?php if ($error): ?>
    <p class="error"><?= e($error) ?></p>
<?php endif; ?>

<form method="POST">
    <input type="text" name="first_name" placeholder="First Name">
    <input type="text" name="last_name" placeholder="Last Name">
    <input type="email" name="email" placeholder="Email">
    <input type="password" name="password" placeholder="Password">

    <button type="submit">Register</button>
</form>

<?php $view->footer(); ?>