<?php

require __DIR__ . '/../src/bootstrap.php';

$session = app('session');
$view = app('view');
$view->header('Order received');

$purchaseNo = $session->get('last_purchase_no');
$session->remove('last_purchase_no');
?>

<section class="panel">
    <h1>Order received</h1>
    <?php if ($purchaseNo): ?>
        <p>Your purchase order number is <strong>#<?= (int) $purchaseNo ?></strong>.</p>
    <?php endif; ?>
    <p>A purchase order summary has been recorded. In this prototype, buyer and business emails are written to <code>storage/mail/orders.log</code>.</p>
    <a class="button" href="/">Continue shopping</a>
</section>

<?php $view->footer(); ?>
