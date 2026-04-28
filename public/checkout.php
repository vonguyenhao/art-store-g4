<?php

require __DIR__ . '/../src/bootstrap.php';

$cart = app('cart');
$checkout = app('checkout');
$csrf = app('csrf');
$session = app('session');
$view = app('view');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf->verify($_POST);

    try {
        $purchaseNo = $checkout->submit($_POST);
        $session->set('last_purchase_no', $purchaseNo);
        redirect('/order_success.php');
    } catch (Throwable $error) {
        $checkoutError = $error->getMessage();
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

<?php if (!empty($checkoutError)): ?>
    <p class="error"><?= e($checkoutError) ?></p>
<?php endif; ?>

<?php if (!$items): ?>
    <p>Your cart is empty.</p>
    <a class="button" href="/">Browse artworks</a>
<?php else: ?>
    <div class="split">
        <form method="post" action="/checkout.php">
            <input type="hidden" name="csrf_token" value="<?= e($csrf->token()) ?>">
            <label>Title <input name="title" value="<?= e($_POST['title'] ?? '') ?>"></label>
            <label>Email <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"></label>
            <label>First name <input name="first_name" required value="<?= e($_POST['first_name'] ?? '') ?>"></label>
            <label>Last name <input name="last_name" required value="<?= e($_POST['last_name'] ?? '') ?>"></label>
            <label>Phone <input name="phone" required value="<?= e($_POST['phone'] ?? '') ?>"></label>
            <label>Address <input name="address" required value="<?= e($_POST['address'] ?? '') ?>"></label>
            <label>City <input name="city" required value="<?= e($_POST['city'] ?? 'Darwin') ?>"></label>
            <label>State <input name="state" required value="<?= e($_POST['state'] ?? 'NT') ?>"></label>
            <label>Postcode <input name="postcode" required value="<?= e($_POST['postcode'] ?? '') ?>"></label>
            <label>Country <input name="country" required value="<?= e($_POST['country'] ?? 'Australia') ?>"></label>
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
