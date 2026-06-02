<?php

require __DIR__ . '/../../src/bootstrap.php';

$auth = app('auth');
$csrf = app('csrf');
$session = app('session');
$ordersRepository = app('orders');
$view = app('view');

$auth->require();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf->verify($_POST);

    try {
        $purchaseNo = (int) ($_POST['purchase_no'] ?? 0);
        $status = (string) ($_POST['status'] ?? '');

        $ordersRepository->updateStatus($purchaseNo, $status);
        $session->flash('Order status updated.');
        redirect('/admin/orders.php');
    } catch (Throwable $error) {
        $orderError = $error->getMessage();
    }
}

$view->header('Orders');

try {
    $orders = $ordersRepository->all();
    $selectedOrder = null;
    $items = [];

    if (isset($_GET['purchase_no'])) {
        $selectedOrder = $ordersRepository->findWithCustomer((int) $_GET['purchase_no']);
        $items = $ordersRepository->items((int) $_GET['purchase_no']);
    }
} catch (Throwable $error) {
    echo '<p class="error">' . e(dbErrorMessage($error)) . '</p>';
    $view->footer();
    exit;
}
?>

<section class="admin-page-header">
    <p><a href="/admin/index.php">Back to dashboard</a></p>
    <h1>Orders</h1>
    <p class="muted">Review submitted purchase orders and update order progress.</p>
</section>

<?php if (!empty($orderError)): ?>
    <p class="error"><?= e($orderError) ?></p>
<?php endif; ?>

<?php if ($selectedOrder): ?>
    <section class="panel">
        <h2>Purchase #<?= (int) $selectedOrder['purchase_no'] ?></h2>
        <p>
            <?= e($selectedOrder['first_name'] . ' ' . $selectedOrder['last_name']) ?><br>
            <?= e($selectedOrder['customer_email']) ?><br>
            <?= e($selectedOrder['phone']) ?><br>
            <?= e($selectedOrder['delivery_address']) ?>
        </p>

        <p><strong>Status:</strong> <?= e(ucfirst($selectedOrder['status'])) ?></p>

        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= e($item['description_snapshot']) ?></td>
                            <td><?= (int) $item['quantity'] ?></td>
                            <td><?= money($item['item_price']) ?></td>
                            <td><?= money((float) $item['item_price'] * (int) $item['quantity']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <p class="price">Order total <?= money($selectedOrder['total_amount']) ?></p>
    </section>
<?php endif; ?>

<?php if (!$orders): ?>
    <section class="panel empty-state">
        <h2>No orders yet</h2>
        <p>Submitted customer orders will appear here.</p>
    </section>
<?php else: ?>
    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Purchase</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>
                            <a href="/admin/orders.php?purchase_no=<?= (int) $order['purchase_no'] ?>">
                                #<?= (int) $order['purchase_no'] ?>
                            </a>
                        </td>
                        <td>
                            <?= e($order['first_name'] . ' ' . $order['last_name']) ?><br>
                            <?= e($order['customer_email']) ?>
                        </td>
                        <td><?= e($order['purchase_date']) ?></td>
                        <td><?= money($order['total_amount']) ?></td>
                        <td>
                            <form method="post" action="/admin/orders.php" class="actions">
                                <input type="hidden" name="csrf_token" value="<?= e($csrf->token()) ?>">
                                <input type="hidden" name="purchase_no" value="<?= (int) $order['purchase_no'] ?>">

                                <select name="status">
                                    <?php foreach (['pending', 'processing', 'completed', 'cancelled'] as $status): ?>
                                        <option value="<?= e($status) ?>" <?= $order['status'] === $status ? 'selected' : '' ?>>
                                            <?= e(ucfirst($status)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <button type="submit">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php $view->footer(); ?>