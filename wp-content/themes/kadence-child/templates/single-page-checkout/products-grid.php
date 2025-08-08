<?php
/**
 * Products Grid Template - Flex Grid & Slider Design
 * 
 * @package KadenceChild
 * @version 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get products
$products = SinglePageCheckout::get_products($atts);

if ($products->have_posts()): ?>
    <!-- Grid Layout -->
    <div class="spc-products-grid" data-layout="grid" style="display: none;">
        <?php while ($products->have_posts()): $products->the_post();
            global $product;
            
            if (!$product || !$product->is_purchasable()) {
                continue;
            }
            
            $product_id = get_the_ID();
            $product_price = $product->get_price_html();
            $product_image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'medium');
            $product_image_url = $product_image ? $product_image[0] : wc_placeholder_img_src('medium');
            $product_full_image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'large');
            $product_full_image_url = $product_full_image ? $product_full_image[0] : wc_placeholder_img_src('large');
            
            // Check if product is in cart
            $in_cart = false;
            if (WC()->cart) {
                foreach (WC()->cart->get_cart() as $cart_item) {
                    if ($cart_item['product_id'] == $product_id) {
                        $in_cart = true;
                        break;
                    }
                }
            }
            ?>
            <div class="spc-product-item <?php echo $in_cart ? 'selected' : ''; ?>" 
                 data-product-id="<?php echo esc_attr($product_id); ?>">
                <div class="spc-product-image-container">
                    <img src="<?php echo esc_url($product_image_url); ?>" 
                         alt="<?php echo esc_attr(get_the_title()); ?>" 
                         class="spc-product-image"
                         loading="lazy">
                    <div class="spc-product-overlay">
                        <button class="spc-zoom-btn" data-full-image="<?php echo esc_url($product_full_image_url); ?>">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                                <path d="M12 10h-2v2H9v-2H7V9h2V7h1v2h2v1z"/>
                            </svg>
                        </button>
                        <div class="spc-product-price"><?php echo $product_price; ?></div>
                    </div>
                </div>
            </div>
        <?php endwhile;
        wp_reset_postdata(); ?>
    </div>

    <!-- Slider Layout -->
    <div class="spc-products-slider" data-layout="slider">
        <div class="spc-slider-container">
            <button class="spc-slider-btn spc-slider-prev">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                </svg>
            </button>
            <div class="spc-slider-track swiper">
                <div class="swiper-wrapper"><?php $products->rewind_posts();
                while ($products->have_posts()): $products->the_post();
                    global $product;
                    
                    if (!$product || !$product->is_purchasable()) {
                        continue;
                    }
                    
                    $product_id = get_the_ID();
                    $product_price = $product->get_price_html();
                    $product_image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'medium');
                    $product_image_url = $product_image ? $product_image[0] : wc_placeholder_img_src('medium');
                    $product_full_image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'large');
                    $product_full_image_url = $product_full_image ? $product_full_image[0] : wc_placeholder_img_src('large');
                    
                    // Check if product is in cart
                    $in_cart = false;
                    if (WC()->cart) {
                        foreach (WC()->cart->get_cart() as $cart_item) {
                            if ($cart_item['product_id'] == $product_id) {
                                $in_cart = true;
                                break;
                            }
                        }
                    }
                    ?>
                    <div class="swiper-slide">
                        <div class="spc-slider-item spc-product-item <?php echo $in_cart ? 'selected' : ''; ?>" 
                             data-product-id="<?php echo esc_attr($product_id); ?>">
                            <div class="spc-product-image-container">
                                <img src="<?php echo esc_url($product_image_url); ?>" 
                                     alt="<?php echo esc_attr(get_the_title()); ?>" 
                                     class="spc-product-image"
                                     loading="lazy">
                                <div class="spc-product-overlay">
                                    <button class="spc-zoom-btn" data-full-image="<?php echo esc_url($product_full_image_url); ?>">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                                            <path d="M12 10h-2v2H9v-2H7V9h2V7h1v2h2v1z"/>
                                        </svg>
                                    </button>
                                    <div class="spc-product-price"><?php echo $product_price; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile;
                wp_reset_postdata(); ?>
                </div>
            </div>
            <button class="spc-slider-btn spc-slider-next">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Image Zoom Modal -->
    <div id="spc-zoom-modal" class="spc-zoom-modal">
        <div class="spc-zoom-backdrop"></div>
        <div class="spc-zoom-content">
            <button class="spc-zoom-close">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                </svg>
            </button>
            <img id="spc-zoom-image" src="" alt="">
        </div>
    </div>

<?php else: ?>
    <div class="spc-no-products">
        <h3>No Products Found</h3>
        <p>Please check back later for available products.</p>
    </div>
<?php endif; ?>
