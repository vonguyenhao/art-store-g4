<?php

require __DIR__ . '/../src/bootstrap.php';

$cart = app('cart');
$checkout = app('checkout');
$csrf = app('csrf');
$session = app('session');
$view = app('view');


$customer = null;

if (isset($_SESSION['customer_email'])) {
    $customer = app('database')->fetchOne(
        "SELECT * FROM customers WHERE email = ?",
        [$_SESSION['customer_email']]
    );
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf->verify($_POST);

    $email = trim($_POST['email'] ?? '');
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $postcode = trim($_POST['postcode'] ?? '');
    $country = trim($_POST['country'] ?? '');

    if ($email === '') {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email address is invalid.';
    }

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

    if ($country === '') {
        $errors[] = 'Country is required.';
    }

    if (!$errors) {
        try {
            $purchaseNo = $checkout->submit($_POST);
            $session->set('last_purchase_no', $purchaseNo);
            $session->flash('Order submitted.');
            redirect('/order_success.php');
        } catch (Throwable $error) {
            $errors[] = $error->getMessage();
        }
    }
}

$view->header('Checkout');

try {
    [$items, $total] = $cart->items();
} catch (Throwable $error) {
    echo '<p class="error">' . e(dbErrorMessage($error)) . '</p>';
    $view->footer();
    exit;
}
?>

<h1>Checkout</h1>

<?php if ($errors): ?>
    <div class="error">
        <ul>
            <?php foreach ($errors as $message): ?>
                <li><?= e($message) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (!$items): ?>
    <p>Your cart is empty.</p>
    <a class="button" href="/">Browse artworks</a>
<?php else: ?>
    <div class="split">
        <form method="post" action="/checkout.php">
            <input type="hidden" name="csrf_token" value="<?= e($csrf->token()) ?>">
            <label>Email
            <input type="email" name="email" required
            value="<?= e($_POST['email'] ?? $customer['email'] ?? '') ?>">
        </label>

        <label>First name
            <input name="first_name" required
                value="<?= e($_POST['first_name'] ?? $customer['first_name'] ?? '') ?>">
        </label>

        <label>Last name
            <input name="last_name" required
                value="<?= e($_POST['last_name'] ?? $customer['last_name'] ?? '') ?>">
        </label>

        <label>Phone
            <input name="phone" required
                value="<?= e($_POST['phone'] ?? $customer['phone'] ?? '') ?>">
        </label>

        <label>City
            <input name="city" required value="<?= e($_POST['city'] ?? $customer['city'] ?? 'Darwin') ?>">
        </label>

        <label>State
            <input name="state" required value="<?= e($_POST['state'] ?? $customer['state'] ?? 'NT') ?>">
        </label>

        <label>Postcode
            <input name="postcode" required value="<?= e($_POST['postcode'] ?? $customer['postcode'] ?? '') ?>">
        </label>

        <label>Country
            <input name="country" required value="<?= e($_POST['country'] ?? $customer['country'] ?? 'Australia') ?>">
        </label>

        <label>Address
            <input name="address" required
                value="<?= e($_POST['address'] ?? $customer['address'] ?? '') ?>">
        </label>
            <button type="submit">Submit order</button>
        </form>
        <aside class="panel">
            <h2>Order summary</h2>
            <?php foreach ($items as $item): ?>
                <p><?= e($item['product']['description']) ?> x <?= (int) $item['quantity'] ?><br>
                    <strong><?= money($item['lineTotal']) ?></strong></p>
            <?php endforeach; ?>
            <p class="price">Total <?= money($total) ?></p>
        </aside>
    </div>
<?php endif; ?>

<?php $view->footer(); ?>
