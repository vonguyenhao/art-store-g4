<?php

require __DIR__ . '/../src/bootstrap.php';

$session = app('session');
$view = app('view');
$view->header('Order received');

$purchaseNo = $session->get('last_purchase_no');
$session->remove('last_purchase_no');
?>

<section class="success-layout">
    <div class="panel success-panel">
        <p class="success-icon">✓</p>
        <p class="eyebrow">Order submitted</p>
        <h1>Thank you for your purchase order</h1>

        <?php if ($purchaseNo): ?>
            <p>Your purchase order number is <strong>#<?= (int) $purchaseNo ?></strong>.</p>
        <?php endif; ?>

        <p>
            Your order details have been recorded in the database. In this prototype, the buyer
            confirmation and business order copy are simulated in
            <code>storage/mail/orders.log</code>.
        </p>

        <div class="actions">
            <a class="button" href="/">Continue shopping</a>
            <a class="button secondary" href="/testimonials.php">Leave a testimonial</a>
        </div>
    </div>
</section>

<?php $view->footer(); ?>