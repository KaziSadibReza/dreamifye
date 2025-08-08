<?php
/**
 * Thankyou page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/thankyou.php.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.1.0
 */

defined( 'ABSPATH' ) || exit;

// Get order information
$order_id = $order ? $order->get_id() : 0;

if ( $order && is_object($order) && method_exists($order, 'get_id') ) :
    // Display custom order received page with proper order data
    echo SinglePageCheckout::load_template('order-received', array(
        'order_id' => $order_id,
        'order' => $order
    ));
else :
    ?>
    <div class="spc-error">
        <p><?php esc_html_e( 'Thank you. Your order has been received.', 'woocommerce' ); ?></p>
    </div>
    <?php
endif;
?>
