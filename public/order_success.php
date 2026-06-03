<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

$session = app('session');
$view = app('view');

$view->header('Order Confirmed');

$purchaseNo = $session->get('last_purchase_no');
$session->remove('last_purchase_no');
?>

<section class="success-layout">

    <div class="panel success-panel">

        <div class="success-icon">✓</div>

        <p class="eyebrow">Order Received</p>

        <h1>Thank you for your order</h1>

        <?php if ($purchaseNo): ?>
            <div class="order-number-box">
                <span>Your Order Reference</span>
                <strong>#<?= (int) $purchaseNo ?></strong>
            </div>
        <?php endif; ?>

        <p class="success-message">
            Your order has been submitted successfully. Our team will review the order details and contact you if any further information is required.
        </p>

        <div class="success-divider"></div>

        <div class="order-status">
            <h2>Order Process</h2>

            <div class="status-step completed">
                <span>✓</span>
                <div>
                    <strong>Order Submitted</strong>
                    <p>Your selected artwork and customer details have been received.</p>
                </div>
            </div>

            <div class="status-step">
                <span>○</span>
                <div>
                    <strong>Review by Store Team</strong>
                    <p>The store will check the order and prepare the next steps.</p>
                </div>
            </div>

            <div class="status-step">
                <span>○</span>
                <div>
                    <strong>Customer Follow-up</strong>
                    <p>You may be contacted about payment, collection, or delivery details.</p>
                </div>
            </div>
        </div>

        <div class="next-steps">
            <h2>What You Can Do Next</h2>

            <ul>
                <li>Save your order reference for future communication.</li>
                <li>Continue browsing other artworks in the collection.</li>
                <li>Share your experience by leaving a testimonial.</li>
            </ul>
        </div>

        <div class="actions success-actions">
            <a class="button" href="/">Continue Shopping</a>
            <a class="button secondary" href="/testimonials.php">Leave a Testimonial</a>
        </div>

    </div>

</section>

<?php $view->footer(); ?>