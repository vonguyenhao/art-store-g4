<?php

require __DIR__ . '/../../src/bootstrap.php';
$auth = app('auth');
$ordersRepository = app('orders');
$view = app('view');
$auth->require();

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

<h1>Orders</h1>

<?php if ($selectedOrder): ?>
    <section class="panel">
        <h2>Purchase #<?= (int) $selectedOrder['purchase_no'] ?></h2>
        <p>
            <?= e($selectedOrder['first_name'] . ' ' . $selectedOrder['last_name']) ?><br>
            <?= e($selectedOrder['customer_email']) ?><br>
            <?= e($selectedOrder['phone']) ?><br>
            <?= e($selectedOrder['delivery_address']) ?>
        </p>
        <table>
            <thead><tr><th>Item</th><th>Quantity</th><th>Price</th><th>Total</th></tr></thead>
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
        <p class="price">Order total <?= money($selectedOrder['total_amount']) ?></p>
    </section>
<?php endif; ?>

<table>
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
                <td><a href="/admin/orders.php?purchase_no=<?= (int) $order['purchase_no'] ?>">#<?= (int) $order['purchase_no'] ?></a></td>
                <td><?= e($order['first_name'] . ' ' . $order['last_name']) ?><br><?= e($order['customer_email']) ?></td>
                <td><?= e($order['purchase_date']) ?></td>
                <td><?= money($order['total_amount']) ?></td>
                <td><?= e($order['status']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php if (!$orders): ?>
    <p>No orders have been submitted yet.</p>
<?php endif; ?>

<?php $view->footer(); ?>
