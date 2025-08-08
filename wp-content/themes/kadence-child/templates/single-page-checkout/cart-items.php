<?php
/**
 * Cart Items Template - Modern Design
 * 
 * @package KadenceChild
 * @version 2.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

if (!SinglePageCheckout::is_cart_available()) {
    echo '<p style="text-align: center; color: var(--spc-text-secondary); padding: var(--spc-space-lg);">Cart is not available.</p>';
    return;
}

if (WC()->cart->is_empty()) {
    echo '<p style="text-align: center; color: var(--spc-text-secondary); padding: var(--spc-space-lg);">Your cart is empty. Select products to add them to your cart.</p>';
    return;
}
?>

<?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) : ?>
<?php 
    $product = $cart_item['data'];
    $quantity = $cart_item['quantity'];
    $product_id = $cart_item['product_id'];
    
    if (!$product) continue;
    
    $product_image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'thumbnail');
    $product_image_url = $product_image ? $product_image[0] : wc_placeholder_img_src('thumbnail');
    ?>
<div class="spc-cart-item" data-product-id="<?php echo esc_attr($product_id); ?>">
    <img src="<?php echo esc_url($product_image_url); ?>" alt="<?php echo esc_attr($product->get_name()); ?>"
        class="spc-cart-item-image" loading="lazy">

    <div class="spc-cart-item-details">
        <div class="spc-cart-item-title"><?php echo esc_html($product->get_name()); ?></div>
        <div class="spc-cart-item-price"><?php echo $product->get_price_html(); ?></div>
    </div>

    <div class="spc-quantity-controls">
        <button class="spc-quantity-btn spc-decrease-qty" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>"
            <?php echo ($quantity <= 1) ? 'disabled' : ''; ?> title="Decrease quantity">
            −
        </button>
        <span class="spc-quantity-display"><?php echo esc_html($quantity); ?></span>
        <button class="spc-quantity-btn spc-increase-qty" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>"
            title="Increase quantity">
            +
        </button>
    </div>

    <button class="spc-remove-btn" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>"
        data-product-id="<?php echo esc_attr($product_id); ?>" title="Remove from cart">
        ×
    </button>
</div>
<?php endforeach; ?>