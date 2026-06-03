<?php

require __DIR__ . '/../src/bootstrap.php';

$cartService = app('cart');
$csrf = app('csrf');
$session = app('session');
$view = app('view');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf->verify($_POST);

    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $productNo = filter_var($_POST['product_no'] ?? null, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);
        $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        try {
            $cartService->add((int) $productNo, (int) $quantity);
            $session->flash('Artwork added to cart.');
        } catch (Throwable $error) {
            $session->flash($error->getMessage());
        }

        redirect('/');
    }

    if ($action === 'update') {
        try {
            $cartService->update($_POST['quantities'] ?? []);
            $session->flash('Cart updated.');
        } catch (Throwable $error) {
            $session->flash($error->getMessage());
        }
    }

    if ($action === 'remove') {
        $productNo = filter_var($_POST['product_no'] ?? null, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);
        if ($productNo) {
            $cartService->remove((int) $productNo);
        }
        $session->flash('Item removed.');
    }

    if ($action === 'clear') {
        $cartService->clear();
        $session->flash('Cart cleared.');
    }

    redirect('/cart.php');
}

$view->header('Cart');

try {
    [$items, $total, $removedCount] = $cartService->items();
} catch (Throwable $error) {
    echo '<p class="error">' . e(dbErrorMessage($error)) . '</p>';
    $view->footer();
    exit;
}
?>

<h1>Shopping cart</h1>

<?php if ($removedCount > 0): ?>
    <p class="flash">Some cart items were removed because they are no longer available.</p>
<?php endif; ?>

<?php if (!$items): ?>
    <p>Your cart is empty.</p>
    <a class="button" href="/">Browse artworks</a>
<?php else: ?>
    <form id="cart-update-form" method="post" action="/cart.php">
        <input type="hidden" name="csrf_token" value="<?= e($csrf->token()) ?>">
        <input type="hidden" name="action" value="update">
    </form>
    <div class="table-wrap">
    <table class="cart-table">
        <thead>
            <tr>
                <th>Artwork</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Remove</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <a href="/product.php?id=<?= (int) $item['product']['product_no'] ?>">
                            <?= e($item['product']['description']) ?>
                        </a>
                    </td>
                    <td><?= money($item['product']['price']) ?></td>
                    <td>
                        <input
                            form="cart-update-form"
                            class="quantity-input"
                            type="number"
                            name="quantities[<?= (int) $item['product']['product_no'] ?>]"
                            value="<?= (int) $item['quantity'] ?>"
                            min="0"
                            max="20"
                        >
                    </td>
                    <td><?= money($item['lineTotal']) ?></td>
                    <td>
                        <form method="post" action="/cart.php">
                            <input type="hidden" name="csrf_token" value="<?= e($csrf->token()) ?>">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="product_no" value="<?= (int) $item['product']['product_no'] ?>">
                            <button class="secondary" type="submit">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
                <tr>
                    <th colspan="3">Order total</th>
                    <th><?= money($total) ?></th>
                    <th></th>
                </tr>
        </tfoot>
    </table>
    </div>
    <div class="actions">
        <a class="button" href="/checkout.php">Checkout</a>
        <button class="secondary" form="cart-update-form" type="submit">Update cart</button>
    </div>
    <form method="post" action="/cart.php">
        <input type="hidden" name="csrf_token" value="<?= e($csrf->token()) ?>">
        <input type="hidden" name="action" value="clear">
        <button class="secondary" type="submit">Clear cart</button>
    </form>
<?php endif; ?>

<?php $view->footer(); ?>
