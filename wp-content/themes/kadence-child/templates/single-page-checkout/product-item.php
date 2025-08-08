<?php
/**
 * Single Product Item Template
 * 
 * @package KadenceChild
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

global $product;

if (!$product) {
    $product = wc_get_product(get_the_ID());
}

if (!$product) {
    return;
}

// Check if product is in cart
$in_cart = false;
if (SinglePageCheckout::is_cart_available()) {
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        if ($cart_item['product_id'] == $product->get_id()) {
            $in_cart = true;
            break;
        }
    }
}
?>

<div class="spc-product-item spc-selectable-product <?php echo $in_cart ? 'spc-product-selected' : ''; ?>" 
     data-product-id="<?php echo esc_attr($product->get_id()); ?>"
     data-product-name="<?php echo esc_attr($product->get_name()); ?>">
    <div class="spc-product-image">
        <?php echo $product->get_image('medium'); ?>
        <div class="spc-selection-indicator">
            <span class="spc-checkmark">✓</span>
        </div>
    </div>
    <div class="spc-product-info">
        <h3 class="spc-product-title"><?php echo esc_html($product->get_name()); ?></h3>
        <div class="spc-product-price"><?php echo $product->get_price_html(); ?></div>
        <div class="spc-product-status">
            <span class="spc-status-text"><?php echo $in_cart ? 'Selected' : 'Click to Select'; ?></span>
        </div>
    </div>
</div>
