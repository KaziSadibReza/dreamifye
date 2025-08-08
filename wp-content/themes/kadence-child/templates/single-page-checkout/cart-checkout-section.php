<?php
/**
 * Cart and Checkout Section Template
 * 
 * @package KadenceChild
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="spc-cart-checkout-section">
    <div class="spc-cart-section">
        <h2 class="spc-section-title">Your Cart</h2>
        <div id="spc-cart-items">
            <?php echo SinglePageCheckout::get_cart_content(); ?>
        </div>
    </div>

    <div class="spc-checkout-section">
        <h2 class="spc-section-title">Checkout</h2>
        <div id="spc-checkout-form">
            <?php echo SinglePageCheckout::get_checkout_content(); ?>
        </div>
    </div>
</div>