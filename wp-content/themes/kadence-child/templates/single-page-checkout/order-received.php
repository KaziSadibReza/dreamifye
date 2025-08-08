<?php
/**
 * Custom Order Received Template
 * 
 * @package KadenceChild
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get order ID from different sources
$order_id = 0;
$order = null;

// Check if order and order_id are passed as template parameters (from WooCommerce template override)
if (isset($order) && is_object($order) && method_exists($order, 'get_id')) {
    // Order object passed directly from template
    $order_id = $order->get_id();
} elseif (isset($order_id) && $order_id && is_numeric($order_id)) {
    // Order ID passed from template parameter
    $order_id = intval($order_id);
    $order = wc_get_order($order_id);
} elseif (isset($_GET['order_id'])) {
    // From URL parameter
    $order_id = intval($_GET['order_id']);
    $order = wc_get_order($order_id);
} elseif (isset($_GET['order-received']) && $_GET['order-received']) {
    // From WooCommerce URL parameter
    $order_id = intval($_GET['order-received']);
    $order = wc_get_order($order_id);
} elseif (isset($GLOBALS['order']) && is_object($GLOBALS['order']) && method_exists($GLOBALS['order'], 'get_id')) {
    // From global order variable (WooCommerce context) - ensure it's an object
    $order = $GLOBALS['order'];
    $order_id = $order->get_id();
}

// If we still don't have an order, check for the order parameter in the URL directly
if (!$order_id && !$order) {
    global $wp;
    if (isset($wp->query_vars['order-received'])) {
        $order_id = intval($wp->query_vars['order-received']);
        $order = wc_get_order($order_id);
    }
}

if (!$order_id && !$order) {
    echo '<div class="spc-error"><p>Invalid order information.</p></div>';
    return;
}

if (!$order && $order_id) {
    $order = wc_get_order($order_id);
}

if (!$order || !is_object($order) || !method_exists($order, 'get_id')) {
    echo '<div class="spc-error"><p>Order not found or invalid.</p></div>';
    return;
}

// Update order_id if we got it from the order object
if (!$order_id && $order) {
    $order_id = $order->get_id();
}

$order_key = isset($_GET['key']) ? sanitize_text_field($_GET['key']) : '';

// Verify order key if provided
if ($order_key && $order->get_order_key() !== $order_key) {
    echo '<div class="spc-error"><p>Invalid order key.</p></div>';
    return;
}
?>

<div class="spc-order-received">
    <div class="spc-success-header">
        <div class="spc-success-icon">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="10" fill="#10B981" />
                <path d="m9 12 2 2 4-4" stroke="white" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>
        </div>
        <h1 class="spc-success-title">Order Confirmed!</h1>
        <p class="spc-success-subtitle">Thank you for your purchase. Your order has been received and is being
            processed.</p>
    </div>

    <div class="spc-order-details-grid">
        <!-- Order Summary Card -->
        <div class="spc-order-card spc-order-summary">
            <h3 class="spc-card-title">Order Summary</h3>
            <div class="spc-order-info">
                <div class="spc-info-row">
                    <span class="spc-info-label">Order Number:</span>
                    <span class="spc-info-value">#<?php echo $order->get_order_number(); ?></span>
                </div>
                <div class="spc-info-row">
                    <span class="spc-info-label">Order Date:</span>
                    <span class="spc-info-value"><?php echo $order->get_date_created()->format('F j, Y'); ?></span>
                </div>
                <div class="spc-info-row">
                    <span class="spc-info-label">Status:</span>
                    <span class="spc-info-value spc-status-<?php echo esc_attr($order->get_status()); ?>">
                        <?php echo ucfirst($order->get_status()); ?>
                    </span>
                </div>
                <div class="spc-info-row">
                    <span class="spc-info-label">Total:</span>
                    <span class="spc-info-value spc-total"><?php echo $order->get_formatted_order_total(); ?></span>
                </div>
            </div>
        </div>

        <!-- Order Items Card -->
        <div class="spc-order-card spc-order-items">
            <h3 class="spc-card-title">Items Ordered</h3>
            <div class="spc-items-list">
                <?php foreach ($order->get_items() as $item_id => $item) : ?>
                <?php 
                    // Get product from order item
                    $product_id = isset($item['product_id']) ? $item['product_id'] : 0;
                    $product = $product_id ? wc_get_product($product_id) : null;
                    if (!$product) continue;
                    ?>
                <div class="spc-order-item">
                    <div class="spc-item-image">
                        <?php echo $product->get_image('thumbnail'); ?>
                    </div>
                    <div class="spc-item-details">
                        <h4 class="spc-item-name">
                            <?php echo isset($item['name']) ? esc_html($item['name']) : $product->get_name(); ?></h4>
                        <div class="spc-item-meta">
                            <span class="spc-item-quantity">Qty:
                                <?php echo isset($item['qty']) ? esc_html($item['qty']) : 1; ?></span>
                            <span
                                class="spc-item-price"><?php echo $order->get_formatted_line_subtotal($item); ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Billing Information Card -->
        <div class="spc-order-card spc-billing-info">
            <h3 class="spc-card-title">Billing Information</h3>
            <div class="spc-address">
                <p><strong><?php echo $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(); ?></strong>
                </p>
                <?php if ($order->get_billing_company()) : ?>
                <p><?php echo $order->get_billing_company(); ?></p>
                <?php endif; ?>
                <p><?php echo $order->get_billing_address_1(); ?></p>
                <?php if ($order->get_billing_address_2()) : ?>
                <p><?php echo $order->get_billing_address_2(); ?></p>
                <?php endif; ?>
                <p><?php echo $order->get_billing_city() . ', ' . $order->get_billing_state() . ' ' . $order->get_billing_postcode(); ?>
                </p>
                <p><?php echo $order->get_billing_country(); ?></p>
                <?php if ($order->get_billing_phone()) : ?>
                <p><strong>Phone:</strong> <?php echo $order->get_billing_phone(); ?></p>
                <?php endif; ?>
                <p><strong>Email:</strong> <?php echo $order->get_billing_email(); ?></p>
            </div>
        </div>

        <!-- Shipping Information Card -->
        <?php if ($order->has_shipping_address()) : ?>
        <div class="spc-order-card spc-shipping-info">
            <h3 class="spc-card-title">Shipping Information</h3>
            <div class="spc-address">
                <p><strong><?php echo $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(); ?></strong>
                </p>
                <?php if ($order->get_shipping_company()) : ?>
                <p><?php echo $order->get_shipping_company(); ?></p>
                <?php endif; ?>
                <p><?php echo $order->get_shipping_address_1(); ?></p>
                <?php if ($order->get_shipping_address_2()) : ?>
                <p><?php echo $order->get_shipping_address_2(); ?></p>
                <?php endif; ?>
                <p><?php echo $order->get_shipping_city() . ', ' . $order->get_shipping_state() . ' ' . $order->get_shipping_postcode(); ?>
                </p>
                <p><?php echo $order->get_shipping_country(); ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Order Totals -->
    <div class="spc-order-totals">
        <h3 class="spc-card-title">Order Totals</h3>
        <div class="spc-totals-table">
            <div class="spc-total-row">
                <span class="spc-total-label">Subtotal:</span>
                <span class="spc-total-value"><?php echo $order->get_subtotal_to_display(); ?></span>
            </div>

            <?php foreach ($order->get_tax_totals() as $tax) : ?>
            <div class="spc-total-row">
                <span class="spc-total-label"><?php echo $tax->label; ?>:</span>
                <span class="spc-total-value"><?php echo $tax->formatted_amount; ?></span>
            </div>
            <?php endforeach; ?>

            <?php if ($order->get_shipping_total() > 0) : ?>
            <div class="spc-total-row">
                <span class="spc-total-label">Shipping:</span>
                <span class="spc-total-value"><?php echo wc_price($order->get_shipping_total()); ?></span>
            </div>
            <?php endif; ?>

            <div class="spc-total-row spc-total-final">
                <span class="spc-total-label">Total:</span>
                <span class="spc-total-value"><?php echo $order->get_formatted_order_total(); ?></span>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="spc-order-actions">
        <a href="<?php echo wc_get_account_endpoint_url('orders'); ?>" class="spc-btn spc-btn-secondary">
            View All Orders
        </a>
        <a href="<?php echo home_url(); ?>" class="spc-btn spc-btn-primary">
            Continue Shopping
        </a>
    </div>

    <!-- Additional Information -->
    <div class="spc-order-notes">
        <div class="spc-note-card">
            <h4>What happens next?</h4>
            <ul>
                <li>You will receive an order confirmation email shortly</li>
                <li>We'll notify you when your order ships</li>
                <li>Track your order status in your account</li>
                <li>Contact us if you have any questions</li>
            </ul>
        </div>
    </div>
</div>