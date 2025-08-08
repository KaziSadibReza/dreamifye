<?php
/**
 * Custom WooCommerce Thank You Page Template Override
 * 
 * @package KadenceChild
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Get order ID from URL parameters
$order_id = absint(get_query_var('order-received'));
$order_key = isset($_GET['key']) ? sanitize_text_field($_GET['key']) : '';

if ($order_id) {
    $order = wc_get_order($order_id);
    
    if ($order && (!$order_key || $order->get_order_key() === $order_key)) {
        // Display our custom order received template
        echo SinglePageCheckout::load_template('order-received', array('order_id' => $order_id));
    } else {
        echo '<div class="spc-error"><p>Order not found or invalid order key.</p></div>';
    }
} else {
    echo '<div class="spc-error"><p>Invalid order information.</p></div>';
}

get_footer();
?>
