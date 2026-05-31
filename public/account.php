<?php

require __DIR__ . '/../src/bootstrap.php';

$view = app('view');
$db = app('database');
$errors = [];
$success = '';

if (!isset($_SESSION['customer_email'])) {
    redirect('/login.php');
}

$email = $_SESSION['customer_email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $postcode = trim($_POST['postcode'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';

    if ($firstName === '') {
            $errors[] = 'First name is required.';
        }

        if ($lastName === '') {
            $errors[] = 'Last name is required.';
        }

        if ($phone === '') {
            $errors[] = 'Phone number is required.';
        } elseif (!preg_match('/^[0-9\s\-\+\(\)]{8,20}$/', $phone)) {
            $errors[] = 'Phone number format is invalid.';
        }

        if ($address === '') {
            $errors[] = 'Address is required.';
        }

        if ($city === '') {
            $errors[] = 'City is required.';
        }

        if ($state === '') {
            $errors[] = 'State is required.';
        }

        if ($postcode === '') {
            $errors[] = 'Postcode is required.';
        } elseif (!preg_match('/^[0-9]{4}$/', $postcode)) {
            $errors[] = 'Postcode must be 4 digits.';
        }

        if ($newPassword !== '' && strlen($newPassword) < 8) {
            $errors[] = 'New password must be at least 8 characters.';
        }

        if (!$errors) {
        $db->execute("
            UPDATE customers
            SET first_name = ?, last_name = ?, phone = ?, address = ?, city = ?, state = ?, postcode = ?
            WHERE email = ?
        ", [
            $firstName,
            $lastName,
            $phone,
            $address,
            $city,
            $state,
            $postcode,
            $email
        ]);

        if ($newPassword !== '') {
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

            $db->execute("
                UPDATE customers
                SET password_hash = ?
                WHERE email = ?
            ", [
                $passwordHash,
                $email
            ]);
        }

        $_SESSION['customer_name'] = $firstName;
        $success = 'Account details updated successfully.';
    }
}

$customer = $db->fetchOne(
    "SELECT * FROM customers WHERE email = ?",
    [$email]
);

$view->header('My Account');
?>

<h1>My Account</h1>

<?php if ($errors): ?>
    <div class="error">
        <ul>
            <?php foreach ($errors as $message): ?>
                <li><?= e($message) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <p class="flash"><?= e($success) ?></p>
<?php endif; ?>

<form method="post">
    <label>
        Email
        <input type="email" value="<?= e($customer['email']) ?>" disabled>
    </label>

    <label>
        First Name
        <input type="text" name="first_name" value="<?= e($customer['first_name']) ?>">
    </label>

    <label>
        Last Name
        <input type="text" name="last_name" value="<?= e($customer['last_name']) ?>">
    </label>

    <label>
        Phone
        <input type="text" name="phone" value="<?= e($customer['phone']) ?>">
    </label>

    <label>
        Address
        <input type="text" name="address" value="<?= e($customer['address']) ?>">
    </label>

    <label>
        City
        <input type="text" name="city" value="<?= e($customer['city']) ?>">
    </label>

    <label>
        State
        <input type="text" name="state" value="<?= e($customer['state']) ?>">
    </label>

    <label>
        Postcode
        <input type="text" name="postcode" value="<?= e($customer['postcode']) ?>">
    </label>

    <label>
        New Password
        <input type="password" name="new_password" placeholder="Leave blank to keep current password">
    </label>

    <button type="submit">Update Account</button>
</form>

<?php $view->footer(); ?>