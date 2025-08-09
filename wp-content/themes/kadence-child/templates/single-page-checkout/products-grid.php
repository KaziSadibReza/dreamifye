<?php
/**
 * Products Grid Template - Category-based Flex Grid & Slider Design
 * 
 * @package KadenceChild
 * @version 3.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get products
$products = SinglePageCheckout::get_products($atts);

if ($products->have_posts()): 
    // Group products by category
    $products_by_category = array();
    
    // Debug: Count total products
    $total_products = $products->found_posts;
    
    while ($products->have_posts()): $products->the_post();
        global $product;
        
        if (!$product || !$product->is_purchasable()) {
            continue;
        }
        
        $product_id = get_the_ID();
        $product_categories = wp_get_post_terms($product_id, 'product_cat');
        
        // Store product data once
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
        
        $product_data = array(
            'id' => $product_id,
            'title' => get_the_title(),
            'price' => $product_price,
            'image_url' => $product_image_url,
            'full_image_url' => $product_full_image_url,
            'in_cart' => $in_cart
        );
        
        // Add product to ALL its categories
        if (!empty($product_categories)) {
            foreach ($product_categories as $category) {
                $category_slug = $category->slug;
                $category_name = $category->name;
                
                if (!isset($products_by_category[$category_slug])) {
                    $products_by_category[$category_slug] = array(
                        'name' => $category_name,
                        'products' => array()
                    );
                }
                
                $products_by_category[$category_slug]['products'][] = $product_data;
            }
        } else {
            // Handle uncategorized products
            $category_slug = 'uncategorized';
            $category_name = 'Uncategorized';
            
            if (!isset($products_by_category[$category_slug])) {
                $products_by_category[$category_slug] = array(
                    'name' => $category_name,
                    'products' => array()
                );
            }
            
            $products_by_category[$category_slug]['products'][] = $product_data;
        }
    endwhile;
    wp_reset_postdata();
    
    // Debug information (remove this in production)
    if (current_user_can('administrator')) {
        echo "<!-- Debug: Found " . $total_products . " total products -->";
        echo "<!-- Debug: Categories found: " . implode(', ', array_keys($products_by_category)) . " -->";
        foreach ($products_by_category as $slug => $data) {
            echo "<!-- Debug: Category '$slug' has " . count($data['products']) . " products -->";
        }
    }
    
    // Display products grouped by category
    foreach ($products_by_category as $category_slug => $category_data): ?>
        
        <div class="spc-category-section" data-category="<?php echo esc_attr($category_slug); ?>">
            <!-- Category Header -->
            <div class="spc-category-header">
                <h2 class="spc-category-title"><?php echo esc_html($category_data['name']); ?></h2>
                <div class="spc-category-controls">
                    <div class="spc-view-toggle" data-category="<?php echo esc_attr($category_slug); ?>">
                        <button class="spc-view-btn" data-view="grid" data-category="<?php echo esc_attr($category_slug); ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M3 3h8v8H3zM13 3h8v8h-8zM3 13h8v8H3zM13 13h8v8h-8z"/>
                            </svg>
                            Grid
                        </button>
                        <button class="spc-view-btn active" data-view="slider" data-category="<?php echo esc_attr($category_slug); ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M4 6h16v2H4zM4 11h16v2H4zM4 16h16v2H4z"/>
                            </svg>
                            Slider
                        </button>
                    </div>
                </div>
            </div>

            <!-- Category-specific Card Loaders -->
            <div class="spc-category-loading" data-category="<?php echo esc_attr($category_slug); ?>" style="display: none;">
                <!-- Card Loaders for Grid -->
                <div class="spc-card-loader-grid" style="display: none;">
                    <?php for ($i = 0; $i < 6; $i++): ?>
                    <div class="spc-card-loader spc-card-loader-item">
                        <div class="spc-card-loader-image">
                            <div class="spc-card-loader-shimmer"></div>
                        </div>
                        <div class="spc-card-loader-overlay">
                            <div class="spc-card-loader-price"></div>
                            <div class="spc-card-loader-btn"></div>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
                <!-- Card Loaders for Slider -->
                <div class="spc-card-loader-slider">
                    <div class="spc-card-loader-slides">
                        <?php for ($i = 0; $i < 3; $i++): ?>
                        <div class="spc-card-loader spc-card-loader-slide">
                            <div class="spc-card-loader-image">
                                <div class="spc-card-loader-shimmer"></div>
                            </div>
                            <div class="spc-card-loader-overlay">
                                <div class="spc-card-loader-price"></div>
                                <div class="spc-card-loader-btn"></div>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <!-- Grid Layout for this category -->
            <div class="spc-products-grid" data-layout="grid" data-category="<?php echo esc_attr($category_slug); ?>" style="display: none;">
                <?php foreach ($category_data['products'] as $product_data): ?>
                    <div class="spc-product-item <?php echo $product_data['in_cart'] ? 'selected' : ''; ?>"
                        data-product-id="<?php echo esc_attr($product_data['id']); ?>">
                        <div class="spc-product-image-container">
                            <img src="<?php echo esc_url($product_data['image_url']); ?>" 
                                alt="<?php echo esc_attr($product_data['title']); ?>"
                                class="spc-product-image" loading="lazy">
                            <div class="spc-product-overlay">
                                <div class="spc-product-price"><?php echo $product_data['price']; ?></div>
                                <button class="spc-zoom-btn" data-full-image="<?php echo esc_url($product_data['full_image_url']); ?>">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" />
                                        <path d="M12 10h-2v2H9v-2H7V9h2V7h1v2h2v1z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Slider Layout for this category -->
            <div class="spc-products-slider active" data-layout="slider" data-category="<?php echo esc_attr($category_slug); ?>">
                <div class="spc-slider-container">
                    <button class="spc-slider-btn spc-slider-prev" data-category="<?php echo esc_attr($category_slug); ?>">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z" />
                        </svg>
                    </button>
                    <div class="spc-slider-track swiper" data-category="<?php echo esc_attr($category_slug); ?>">
                        <div class="swiper-wrapper">
                            <?php foreach ($category_data['products'] as $product_data): ?>
                                <div class="swiper-slide">
                                    <div class="spc-slider-item spc-product-item <?php echo $product_data['in_cart'] ? 'selected' : ''; ?>"
                                        data-product-id="<?php echo esc_attr($product_data['id']); ?>">
                                        <div class="spc-product-image-container">
                                            <img src="<?php echo esc_url($product_data['image_url']); ?>"
                                                alt="<?php echo esc_attr($product_data['title']); ?>" 
                                                class="spc-product-image" loading="lazy">
                                            <div class="spc-product-overlay">
                                                <div class="spc-product-price"><?php echo $product_data['price']; ?></div>
                                                <button class="spc-zoom-btn"
                                                    data-full-image="<?php echo esc_url($product_data['full_image_url']); ?>">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                                        <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" />
                                                        <path d="M12 10h-2v2H9v-2H7V9h2V7h1v2h2v1z" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button class="spc-slider-btn spc-slider-next" data-category="<?php echo esc_attr($category_slug); ?>">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
    <?php endforeach; ?>

<!-- Image Zoom Modal -->
<div id="spc-zoom-modal" class="spc-zoom-modal">
    <div class="spc-zoom-backdrop"></div>
    <div class="spc-zoom-content">
        <button class="spc-zoom-close">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path
                    d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
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