<?php

require __DIR__ . '/../src/bootstrap.php';

$view = app('view');

if (!isset($_SESSION['customer_email'])) {
    header('Location: /login.php');
    exit;
}

$db = app('database');

$orders = $db->fetchAll("
    SELECT *
    FROM purchases
    WHERE customer_email = ?
    ORDER BY purchase_date DESC
", [
    $_SESSION['customer_email']
]);

$view->header('Current Orders');
?>

<h1>Current Orders</h1>

<?php if (!$orders): ?>
    <p>You have not placed any orders yet.</p>
<?php else: ?>
    <?php foreach ($orders as $order): ?>
        <section class="panel">
            <h2>Order #<?= e($order['purchase_no']) ?></h2>
            <p>Date: <?= e($order['purchase_date']) ?></p>
            <p>Status: <?= e($order['status']) ?></p>
            <p>Total: <?= money($order['total_amount']) ?></p>

            <?php
            $items = $db->fetchAll("
                SELECT *
                FROM purchase_items
                WHERE purchase_no = ?
            ", [
                $order['purchase_no']
            ]);
            ?>

            <ul>
                <?php foreach ($items as $item): ?>
                    <li>
                        <?= e($item['description_snapshot']) ?>
                        × <?= (int) $item['quantity'] ?>
                        — <?= money($item['item_price']) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endforeach; ?>
<?php endif; ?>

<?php $view->footer(); ?>