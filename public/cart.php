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
        $cartService->add((int) ($_POST['product_no'] ?? 0), (int) ($_POST['quantity'] ?? 1));
        $session->flash('Artwork added to cart.');
    }

    if ($action === 'update') {
        $cartService->update($_POST['quantities'] ?? []);
        $session->flash('Cart updated.');
    }

    if ($action === 'clear') {
        $cartService->clear();
        $session->flash('Cart cleared.');
    }

    redirect('/cart.php');
}

$view->header('Cart');

try {
    [$items, $total] = $cartService->items();
} catch (Throwable $error) {
    echo '<p class="error">' . e(dbErrorMessage($error)) . '</p>';
    $view->footer();
    exit;
}
?>

<h1>Shopping cart</h1>

<?php if (!$items): ?>
    <p>Your cart is empty.</p>
    <a class="button" href="/">Browse artworks</a>
<?php else: ?>
    <form method="post" action="/cart.php">
        <input type="hidden" name="csrf_token" value="<?= e($csrf->token()) ?>">
        <input type="hidden" name="action" value="update">
        <table>
            <thead>
                <tr>
                    <th>Artwork</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= e($item['product']['description']) ?></td>
                        <td><?= money($item['product']['price']) ?></td>
                        <td>
                            <input
                                type="number"
                                name="quantities[<?= (int) $item['product']['product_no'] ?>]"
                                value="<?= (int) $item['quantity'] ?>"
                                min="0"
                                max="20"
                            >
                        </td>
                        <td><?= money($item['lineTotal']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3">Order total</th>
                    <th><?= money($total) ?></th>
                </tr>
            </tfoot>
        </table>
        <div class="actions">
            <button type="submit">Update cart</button>
            <a class="button" href="/checkout.php">Checkout</a>
        </div>
    </form>
    <form method="post" action="/cart.php">
        <input type="hidden" name="csrf_token" value="<?= e($csrf->token()) ?>">
        <input type="hidden" name="action" value="clear">
        <button class="secondary" type="submit">Clear cart</button>
    </form>
<?php endif; ?>

<?php $view->footer(); ?>
