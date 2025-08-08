<?php
/**
 * Products Section Template
 * 
 * @package KadenceChild
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get products
$products = SinglePageCheckout::get_products($atts);
?>

<div class="spc-products-section">
    <h2 class="spc-section-title">Select Products</h2>
    <div class="spc-products-grid" data-columns="<?php echo esc_attr($atts['columns']); ?>">
        <?php if ($products->have_posts()) : ?>
            <?php while ($products->have_posts()) : $products->the_post(); ?>
                <?php echo SinglePageCheckout::load_template('product-item'); ?>
            <?php endwhile; ?>
            <?php wp_reset_postdata(); ?>
        <?php else : ?>
            <p class="spc-no-products">No products found.</p>
        <?php endif; ?>
    </div>
</div>
