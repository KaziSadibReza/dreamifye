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
                        <path d="M3 3h8v8H3zM13 3h8v8h-8zM3 13h8v8H3zM13 13h8v8h-8z" />
                    </svg>
                    Grid
                </button>
                <button class="spc-view-btn active" data-view="slider"
                    data-category="<?php echo esc_attr($category_slug); ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M4 6h16v2H4zM4 11h16v2H4zM4 16h16v2H4z" />
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
    <div class="spc-products-grid" data-layout="grid" data-category="<?php echo esc_attr($category_slug); ?>"
        style="display: none;">
        <?php foreach ($category_data['products'] as $product_data): ?>
        <div class="spc-product-item <?php echo $product_data['in_cart'] ? 'selected' : ''; ?>"
            data-product-id="<?php echo esc_attr($product_data['id']); ?>">
            <div class="spc-product-image-container">
                <img src="<?php echo esc_url($product_data['image_url']); ?>"
                    alt="<?php echo esc_attr($product_data['title']); ?>" class="spc-product-image" loading="lazy">
                <div class="spc-product-overlay">
                    <div class="spc-product-price"><?php echo $product_data['price']; ?></div>
                    <button class="spc-zoom-btn"
                        data-full-image="<?php echo esc_url($product_data['full_image_url']); ?>">
                        <svg viewBox="0 -0.5 21 21" version="1.1" xmlns="http://www.w3.org/2000/svg"
                            xmlns:xlink="http://www.w3.org/1999/xlink">


                            <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <g id="Dribbble-Light-Preview" transform="translate(-379.000000, -440.000000)"
                                    fill="#000000">
                                    <g id="icons" transform="translate(56.000000, 160.000000)">
                                        <path
                                            d="M332.449994,286.967331 L334.549993,286.967331 C335.129593,286.967331 335.599993,287.416448 335.599993,287.969825 C335.599993,288.523201 335.129593,288.972319 334.549993,288.972319 L332.449994,288.972319 L332.449994,290.977306 C332.449994,291.530683 331.979595,291.9798 331.399995,291.9798 C330.820395,291.9798 330.349996,291.530683 330.349996,290.977306 L330.349996,288.972319 L328.249997,288.972319 C327.670397,288.972319 327.199998,288.523201 327.199998,287.969825 C327.199998,287.416448 327.670397,286.967331 328.249997,286.967331 L330.349996,286.967331 L330.349996,284.962344 C330.349996,284.408967 330.820395,283.95985 331.399995,283.95985 C331.979595,283.95985 332.449994,284.408967 332.449994,284.962344 L332.449994,286.967331 Z M343.692338,299.706019 L343.692338,299.706019 C343.282838,300.097994 342.617138,300.097994 342.207639,299.706019 L338.060141,295.746169 L339.54484,294.328642 L343.692338,298.288493 C344.102887,298.679465 344.102887,299.315046 343.692338,299.706019 L343.692338,299.706019 Z M331.399995,294.034912 C327.926597,294.034912 325.099999,291.337201 325.099999,288.01995 C325.099999,284.7037 327.926597,282.004987 331.399995,282.004987 C334.873393,282.004987 337.699991,284.7037 337.699991,288.01995 C337.699991,291.337201 334.873393,294.034912 331.399995,294.034912 L331.399995,294.034912 Z M331.399995,280 C326.761098,280 323,283.590932 323,288.01995 C323,292.449969 326.761098,296.039899 331.399995,296.039899 C336.038892,296.039899 339.79999,292.449969 339.79999,288.01995 C339.79999,283.590932 336.038892,280 331.399995,280 L331.399995,280 Z"
                                            id="zoom_in-[#1462]">

                                        </path>
                                    </g>
                                </g>
                            </g>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Slider Layout for this category -->
    <div class="spc-products-slider active" data-layout="slider"
        data-category="<?php echo esc_attr($category_slug); ?>">
        <div class="spc-slider-container">
            <button class="spc-slider-btn spc-slider-prev" data-category="<?php echo esc_attr($category_slug); ?>">
                <svg class="spc-slider-icon" viewBox="0 0 24 24" fill="currentColor">
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
                                    alt="<?php echo esc_attr($product_data['title']); ?>" class="spc-product-image"
                                    loading="lazy">
                                <div class="spc-product-overlay">
                                    <div class="spc-product-price"><?php echo $product_data['price']; ?></div>
                                    <button class="spc-zoom-btn"
                                        data-full-image="<?php echo esc_url($product_data['full_image_url']); ?>">
                                        <svg viewBox="0 -0.5 21 21" version="1.1" xmlns="http://www.w3.org/2000/svg"
                                            xmlns:xlink="http://www.w3.org/1999/xlink">
                                            <g id="Page-1" stroke="none" stroke-width="1" fill="none"
                                                fill-rule="evenodd">
                                                <g id="Dribbble-Light-Preview"
                                                    transform="translate(-379.000000, -440.000000)" fill="#000000">
                                                    <g id="icons" transform="translate(56.000000, 160.000000)">
                                                        <path
                                                            d="M332.449994,286.967331 L334.549993,286.967331 C335.129593,286.967331 335.599993,287.416448 335.599993,287.969825 C335.599993,288.523201 335.129593,288.972319 334.549993,288.972319 L332.449994,288.972319 L332.449994,290.977306 C332.449994,291.530683 331.979595,291.9798 331.399995,291.9798 C330.820395,291.9798 330.349996,291.530683 330.349996,290.977306 L330.349996,288.972319 L328.249997,288.972319 C327.670397,288.972319 327.199998,288.523201 327.199998,287.969825 C327.199998,287.416448 327.670397,286.967331 328.249997,286.967331 L330.349996,286.967331 L330.349996,284.962344 C330.349996,284.408967 330.820395,283.95985 331.399995,283.95985 C331.979595,283.95985 332.449994,284.408967 332.449994,284.962344 L332.449994,286.967331 Z M343.692338,299.706019 L343.692338,299.706019 C343.282838,300.097994 342.617138,300.097994 342.207639,299.706019 L338.060141,295.746169 L339.54484,294.328642 L343.692338,298.288493 C344.102887,298.679465 344.102887,299.315046 343.692338,299.706019 L343.692338,299.706019 Z M331.399995,294.034912 C327.926597,294.034912 325.099999,291.337201 325.099999,288.01995 C325.099999,284.7037 327.926597,282.004987 331.399995,282.004987 C334.873393,282.004987 337.699991,284.7037 337.699991,288.01995 C337.699991,291.337201 334.873393,294.034912 331.399995,294.034912 L331.399995,294.034912 Z M331.399995,280 C326.761098,280 323,283.590932 323,288.01995 C323,292.449969 326.761098,296.039899 331.399995,296.039899 C336.038892,296.039899 339.79999,292.449969 339.79999,288.01995 C339.79999,283.590932 336.038892,280 331.399995,280 L331.399995,280 Z"
                                                            id="zoom_in-[#1462]">

                                                        </path>
                                                    </g>
                                                </g>
                                            </g>
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
                <svg class="spc-slider-icon" viewBox="0 0 24 24" fill="currentColor">
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