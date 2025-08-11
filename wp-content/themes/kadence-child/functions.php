<?php
/**
 * Kadence Child Theme Functions
 * Enhanced with WooCommerce Single Page Checkout
 * 
 * @package KadenceChild
 * @version 1.0.0
 * @author Your Name
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * =============================================================================
 * THEME SETUP & ASSETS
 * =============================================================================
 */

/**
 * Enqueue parent and child theme styles
 * 
 * @since 1.0.0
 */
function kadence_child_enqueue_styles() {
    wp_enqueue_style('kadence-parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('kadence-child-style', get_stylesheet_uri(), array('kadence-parent-style'));
        wp_enqueue_style(
        'single-page-checkout-css',
        get_stylesheet_directory_uri() . '/single-page-checkout.css',
        array(),
        '3.3.8' // Updated for enhanced checkout form design
    );
    
    // Enqueue responsive CSS file for mobile and tablet
    wp_enqueue_style(
        'single-page-checkout-responsive-css',
        get_stylesheet_directory_uri() . '/single-page-checkout-responsive.css',
        array('single-page-checkout-css'),
        '1.0.5' // Responsive design optimizations
    );
    
    // Enqueue jQuery if not already loaded
    wp_enqueue_script('jquery');
    
    // Enqueue custom checkout validation script
    if (is_page() && has_shortcode(get_post()->post_content, 'single_page_checkout')) {
        wp_enqueue_script(
            'checkout-validation',
            get_stylesheet_directory_uri() . '/checkout-validation.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        // Pass AJAX URL and nonce to the script
        wp_localize_script('checkout-validation', 'checkout_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('spc_nonce'),
            'min_products' => 3 // Minimum products required
        ));
    }
    
    // Let WooCommerce handle its own scripts on the checkout page.
    // The [woocommerce_checkout] shortcode will trigger the necessary script enqueues.
    if (is_page() && has_shortcode(get_post()->post_content, 'single_page_checkout')) {
        wp_enqueue_script('wc-checkout');
    }
}
add_action('wp_enqueue_scripts', 'kadence_child_enqueue_styles');

/**
 * =============================================================================
 * WOOCOMMERCE INITIALIZATION
 * =============================================================================
 */

/**
 * Initialize WooCommerce session for single page checkout
 * 
 * @since 1.0.0
 */
function spc_initialize_woocommerce() {
    if (class_exists('WooCommerce') && WC() && WC()->session) {
        if (!WC()->session->has_session()) {
            WC()->session->set_customer_session_cookie(true);
        }
    }
}
add_action('woocommerce_init', 'spc_initialize_woocommerce');

/**
 * =============================================================================
 * SINGLE PAGE CHECKOUT CLASS
 * =============================================================================
 */

class SinglePageCheckout {
    
    /**
     * Default shortcode attributes
     */
    const DEFAULT_ATTRIBUTES = array(
        'products_per_page' => 50, // Increased to show more products across categories
        'category' => '',
        'columns' => 3
    );
    
    /**
     * Template directory path
     */
    const TEMPLATE_DIR = 'templates/single-page-checkout/';
    
    /**
     * Initialize the class
     */
    public static function init() {
        add_shortcode('single_page_checkout', array(__CLASS__, 'render_shortcode'));
        add_shortcode('test_products', array(__CLASS__, 'test_products_shortcode'));
        
        // AJAX handlers
        add_action('wp_ajax_spc_add_to_cart', array(__CLASS__, 'ajax_add_to_cart'));
        add_action('wp_ajax_nopriv_spc_add_to_cart', array(__CLASS__, 'ajax_add_to_cart'));
        add_action('wp_ajax_spc_remove_from_cart', array(__CLASS__, 'ajax_remove_from_cart'));
        add_action('wp_ajax_nopriv_spc_remove_from_cart', array(__CLASS__, 'ajax_remove_from_cart'));
        add_action('wp_ajax_spc_update_quantity', array(__CLASS__, 'ajax_update_quantity'));
        add_action('wp_ajax_nopriv_spc_update_quantity', array(__CLASS__, 'ajax_update_quantity'));
        add_action('wp_ajax_spc_get_cart_item_key', array(__CLASS__, 'ajax_get_cart_item_key'));
        add_action('wp_ajax_nopriv_spc_get_cart_item_key', array(__CLASS__, 'ajax_get_cart_item_key'));
        add_action('wp_ajax_spc_get_cart_content', array(__CLASS__, 'ajax_get_cart_content'));
        add_action('wp_ajax_nopriv_spc_get_cart_content', array(__CLASS__, 'ajax_get_cart_content'));
        add_action('wp_ajax_spc_get_cart_count', array(__CLASS__, 'ajax_get_cart_count'));
        add_action('wp_ajax_nopriv_spc_get_cart_count', array(__CLASS__, 'ajax_get_cart_count'));
        
        // Add shortcode for custom order received page
        add_shortcode('custom_order_received', array(__CLASS__, 'custom_order_received_shortcode'));
    }
    
    /**
     * Load template file
     * 
     * @param string $template_name Template name without .php extension
     * @param array $args Variables to pass to template
     * @return string Template output
     */
    public static function load_template($template_name, $args = array()) {
        $template_path = get_stylesheet_directory() . '/' . self::TEMPLATE_DIR . $template_name . '.php';
        
        if (!file_exists($template_path)) {
            return '<p>Template not found: ' . esc_html($template_name) . '</p>';
        }
        
        // Extract args to variables
        if (!empty($args)) {
            extract($args, EXTR_SKIP);
        }
        
        // Make $atts available to all templates
        if (!isset($atts)) {
            $atts = self::DEFAULT_ATTRIBUTES;
        }
        
        ob_start();
        include $template_path;
        return ob_get_clean();
    }
    
    /**
     * Check if WooCommerce is available and active
     * 
     * @return bool
     */
    public static function is_woocommerce_available() {
        return class_exists('WooCommerce');
    }
    
    /**
     * Check if WooCommerce cart is available
     * 
     * @return bool
     */
    public static function is_cart_available() {
        return self::is_woocommerce_available() && WC() && WC()->cart;
    }
    
    /**
     * Render the single page checkout shortcode
     * 
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public static function render_shortcode($atts) {
        if (!self::is_woocommerce_available()) {
            return '<div class="spc-error"><p>WooCommerce is not active. Please install and activate WooCommerce.</p></div>';
        }
        
        $atts = shortcode_atts(self::DEFAULT_ATTRIBUTES, $atts, 'single_page_checkout');
        
        return self::load_template('main', compact('atts'));
    }
    
    /**
     * Get products query
     * 
     * @param array $atts Shortcode attributes
     * @return WP_Query
     */
    public static function get_products($atts) {
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => intval($atts['products_per_page']),
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => '_stock_status',
                    'value' => 'instock'
                )
            ),
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_visibility',
                    'field' => 'name',
                    'terms' => array('exclude-from-catalog', 'exclude-from-search'),
                    'operator' => 'NOT IN'
                )
            ),
            'orderby' => 'menu_order',
            'order' => 'ASC'
        );
        
        // Only apply category filter if specifically requested
        if (!empty($atts['category']) && $atts['category'] !== 'all') {
            $args['tax_query'][] = array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => sanitize_text_field($atts['category'])
            );
        }
        
        return new WP_Query($args);
    }
    
    /**
     * Get cart content HTML
     * 
     * @return string Cart HTML
     */
    public static function get_cart_content() {
        return self::load_template('cart-items');
    }
    
    /**
     * Get order review/summary content
     * 
     * @return string Order review HTML
     */
    public static function get_order_review() {
        if (!self::is_cart_available() || WC()->cart->is_empty()) {
            return '<div class="spc-cart-empty-message"></div>';
        }

        // Double check that cart has items
        if (WC()->cart->get_cart_contents_count() === 0) {
            return '<div class="spc-cart-empty-message"></div>';
        }

        // Check if there are any visible products after filtering
        $enabler_id = get_option('spc_enabler_product_id');
        $has_visible_products = false;
        
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
            
            // Skip enabler product
            if ($enabler_id && $cart_item['product_id'] == $enabler_id) {
                continue;
            }
            
            // Check if product is hidden from catalog
            $product_visibility_terms = wp_get_post_terms($cart_item['product_id'], 'product_visibility', array('fields' => 'names'));
            $is_hidden = in_array('exclude-from-catalog', $product_visibility_terms) || in_array('exclude-from-search', $product_visibility_terms);
            
            // Skip hidden products
            if ($is_hidden) {
                continue;
            }
            
            if ($_product && $_product->exists() && $cart_item['quantity'] > 0) {
                $has_visible_products = true;
                break;
            }
        }
        
        // If no visible products, show empty message
        if (!$has_visible_products) {
            return '<p style="text-align: center; color: var(--spc-text-secondary); padding: var(--spc-space-lg);">Your cart is empty. Select products to add them to your cart.</p>';
        }

        ob_start();
        ?>
<div class="spc-order-review">
    <h4 class="spc-order-title">Your Order</h4>
    <table class="spc-order-table">
        <thead>
            <tr>
                <th class="spc-product-name">Product</th>
                <th class="spc-product-total">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php
                    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                        $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                        
                        // Skip enabler product
                        if ($enabler_id && $cart_item['product_id'] == $enabler_id) {
                            continue;
                        }
                        
                        // Check if product is hidden from catalog
                        $product_visibility_terms = wp_get_post_terms($cart_item['product_id'], 'product_visibility', array('fields' => 'names'));
                        $is_hidden = in_array('exclude-from-catalog', $product_visibility_terms) || in_array('exclude-from-search', $product_visibility_terms);
                        
                        // Skip hidden products in order review
                        if ($is_hidden) {
                            continue;
                        }
                        
                        if ($_product && $_product->exists() && $cart_item['quantity'] > 0) {
                            ?>
            <tr class="spc-cart-item-row">
                <td class="spc-product-name">
                    <?php echo wp_kses_post($_product->get_name()); ?>
                    <span class="spc-quantity"> × <?php echo esc_html($cart_item['quantity']); ?></span>
                </td>
                <td class="spc-product-total">
                    <?php echo WC()->cart->get_product_subtotal($_product, $cart_item['quantity']); ?>
                </td>
            </tr>
            <?php
                        }
                    }
                    ?>
        </tbody>
        <tfoot>
            <tr class="spc-cart-subtotal">
                <th>Subtotal</th>
                <td><?php wc_cart_totals_subtotal_html(); ?></td>
            </tr>
            <?php foreach (WC()->cart->get_coupons() as $code => $coupon) : ?>
            <tr class="spc-cart-discount coupon-<?php echo esc_attr(sanitize_title($code)); ?>">
                <th>Coupon: <?php echo esc_html(wc_cart_totals_coupon_label($coupon)); ?></th>
                <td><?php wc_cart_totals_coupon_html($coupon); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>
            <?php do_action('woocommerce_cart_totals_before_shipping'); ?>
            <tr class="spc-shipping">
                <th>Shipping</th>
                <td>
                    <?php
                                $packages = WC()->shipping->get_packages();
                                foreach ($packages as $i => $package) {
                                    $chosen_method = WC()->session->get('chosen_shipping_methods')[$i] ?? '';
                                    $available_methods = $package['rates'];
                                    
                                    if (1 < count($available_methods)) {
                                        echo '<ul>';
                                        foreach ($available_methods as $method) {
                                            printf(
                                                '<li>%s: %s</li>',
                                                esc_html($method->label),
                                                $method->cost > 0 ? wc_price($method->cost) : __('Free', 'woocommerce')
                                            );
                                        }
                                        echo '</ul>';
                                    } elseif (1 === count($available_methods)) {
                                        $method = reset($available_methods);
                                        printf(
                                            '%s: %s',
                                            esc_html($method->label),
                                            $method->cost > 0 ? wc_price($method->cost) : __('Free', 'woocommerce')
                                        );
                                    }
                                }
                                ?>
                </td>
            </tr>
            <?php do_action('woocommerce_cart_totals_after_shipping'); ?>
            <?php elseif (WC()->cart->needs_shipping() && 'yes' === get_option('woocommerce_enable_shipping_calc')) : ?>
            <tr class="spc-shipping">
                <th>Shipping</th>
                <td><?php _e('Shipping options will be updated during checkout.', 'woocommerce'); ?></td>
            </tr>
            <?php endif; ?>
            <?php foreach (WC()->cart->get_fees() as $fee) : ?>
            <tr class="spc-fee">
                <th><?php echo esc_html($fee->name); ?></th>
                <td><?php wc_cart_totals_fee_html($fee); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax()) : ?>
            <?php if ('itemized' === get_option('woocommerce_tax_total_display')) : ?>
            <?php foreach (WC()->cart->get_tax_totals() as $code => $tax) : ?>
            <tr class="spc-tax-rate tax-rate-<?php echo esc_attr(sanitize_title($code)); ?>">
                <th><?php echo esc_html($tax->label); ?></th>
                <td><?php echo wp_kses_post($tax->formatted_amount); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php else : ?>
            <tr class="spc-tax-total">
                <th><?php echo esc_html(WC()->countries->tax_or_vat()); ?></th>
                <td><?php wc_cart_totals_taxes_total_html(); ?></td>
            </tr>
            <?php endif; ?>
            <?php endif; ?>
            <tr class="spc-order-total">
                <th>Total</th>
                <td><?php wc_cart_totals_order_total_html(); ?></td>
            </tr>
        </tfoot>
    </table>
</div>
<?php
        return ob_get_clean();
    }

    /**
     * Get checkout content
     * 
     * @return string Checkout HTML
     */
    public static function get_checkout_content() {
        if (!self::is_cart_available()) {
            return '<p>Checkout is not available.</p>';
        }
        
        // Get enabler product ID
        $enabler_id = get_option('spc_enabler_product_id');
        
        // Check if cart has real products (not just enabler)
        $has_real_products = false;
        foreach (WC()->cart->get_cart() as $cart_item) {
            if ($cart_item['product_id'] != $enabler_id) {
                $has_real_products = true;
                break;
            }
        }
        
        // If no real products, add enabler to show checkout form
        if (!$has_real_products && $enabler_id) {
            // Check if enabler is already in cart to prevent duplicates
            $enabler_in_cart = false;
            foreach (WC()->cart->get_cart() as $cart_item) {
                if ($cart_item['product_id'] == $enabler_id) {
                    $enabler_in_cart = true;
                    break;
                }
            }
            
            // Only add if not already in cart
            if (!$enabler_in_cart) {
                WC()->cart->add_to_cart($enabler_id, 1);
            }
        }
        
        // Hide enabler product from checkout display
        add_filter('woocommerce_cart_item_visible', array(__CLASS__, 'hide_enabler_product'), 10, 3);
        add_filter('woocommerce_checkout_cart_item_visible', array(__CLASS__, 'hide_enabler_product'), 10, 3);
        add_filter('woocommerce_widget_cart_item_visible', array(__CLASS__, 'hide_enabler_product'), 10, 3);
        
        // Ensure cart total calculation excludes enabler product
        add_filter('woocommerce_cart_needs_payment', array(__CLASS__, 'cart_needs_payment_check'), 10, 2);
        
        if (WC()->cart->get_cart_contents_count() > 0) {
            return do_shortcode('[woocommerce_checkout]');
        }
        
        return '<p>Your cart is empty. Please add products to proceed with checkout.</p>';
    }
    
    /**
     * Hide enabler product from cart/checkout displays
     */
    public static function hide_enabler_product($visible, $cart_item, $cart_item_key) {
        $enabler_id = get_option('spc_enabler_product_id');
        if ($enabler_id && $cart_item['product_id'] == $enabler_id) {
            return false;
        }
        return $visible;
    }
    
    /**
     * Check if cart needs payment (exclude enabler product from calculation)
     */
    public static function cart_needs_payment_check($needs_payment, $cart) {
    // Force payment methods to show on the single page checkout.
    return true;
}
    /**
     * AJAX handler for adding to cart
     */
    public static function ajax_add_to_cart() {
        if (!check_ajax_referer('spc_nonce', 'nonce', false)) {
            wp_send_json_error('Security check failed');
            return;
        }
        
        if (!self::is_cart_available()) {
            wp_send_json_error('Cart is not available');
            return;
        }
        
        $product_id = intval($_POST['product_id']);
        
        if (!$product_id) {
            wp_send_json_error('Invalid product ID');
            return;
        }
        
        // Remove enabler product before adding real product
        $enabler_id = get_option('spc_enabler_product_id');
        if ($enabler_id) {
            foreach (WC()->cart->get_cart() as $key => $cart_item) {
                if ($cart_item['product_id'] == $enabler_id) {
                    WC()->cart->remove_cart_item($key);
                    break;
                }
            }
        }
        
        $cart_item_key = WC()->cart->add_to_cart($product_id, 1);
        
        if ($cart_item_key) {
            // Calculate totals to ensure the session is updated
            WC()->cart->calculate_totals();
            
            wp_send_json_success(array(
                'cart_html' => self::get_cart_content(),
                'order_review_html' => self::get_order_review()
            ));
        } else {
            wp_send_json_error('Failed to add product to cart');
        }
    }
    
    /**
     * AJAX handler for removing from cart
     */
    public static function ajax_remove_from_cart() {
        if (!check_ajax_referer('spc_nonce', 'nonce', false)) {
            wp_send_json_error('Security check failed');
            return;
        }
        
        if (!self::is_cart_available()) {
            wp_send_json_error('Cart is not available');
            return;
        }
        
        $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
        
        if (!$cart_item_key) {
            wp_send_json_error('Invalid cart item key');
            return;
        }
        
        WC()->cart->remove_cart_item($cart_item_key);
        
        // Check if cart now only has enabler product or is empty
        $enabler_id = get_option('spc_enabler_product_id');
        $has_real_products = false;
        
        foreach (WC()->cart->get_cart() as $cart_item) {
            if ($cart_item['product_id'] != $enabler_id) {
                $has_real_products = true;
                break;
            }
        }
        
        // If no real products left, add enabler to keep checkout form working
        if (!$has_real_products && $enabler_id) {
            WC()->cart->add_to_cart($enabler_id, 1);
        }
        
        // Calculate totals to ensure the session is updated
        WC()->cart->calculate_totals();
        
        wp_send_json_success(array(
            'cart_html' => self::get_cart_content(),
            'order_review_html' => self::get_order_review()
        ));
    }
    
    /**
     * AJAX handler for updating quantity
     */
    public static function ajax_update_quantity() {
        if (!check_ajax_referer('spc_nonce', 'nonce', false)) {
            wp_send_json_error('Security check failed');
            return;
        }
        
        if (!self::is_cart_available()) {
            wp_send_json_error('Cart is not available');
            return;
        }
        
        $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
        $quantity_action = sanitize_text_field($_POST['quantity_action']);
        
        if (!$cart_item_key || !$quantity_action) {
            wp_send_json_error('Invalid parameters');
            return;
        }
        
        $cart_item = WC()->cart->get_cart_item($cart_item_key);
        
        if (!$cart_item) {
            wp_send_json_error('Cart item not found');
            return;
        }
        
        $current_quantity = $cart_item['quantity'];
        $new_quantity = $current_quantity;
        
        if ($quantity_action === 'increase') {
            $new_quantity = $current_quantity + 1;
        } elseif ($quantity_action === 'decrease') {
            $new_quantity = max(1, $current_quantity - 1);
        }
        
        WC()->cart->set_quantity($cart_item_key, $new_quantity);
        
        // Calculate totals to ensure the session is updated
        WC()->cart->calculate_totals();
        
        wp_send_json_success(array(
            'cart_html' => self::get_cart_content(),
            'order_review_html' => self::get_order_review()
        ));
    }
    
    /**
     * AJAX handler for getting cart item key by product ID
     */
    public static function ajax_get_cart_item_key() {
        if (!check_ajax_referer('spc_nonce', 'nonce', false)) {
            wp_send_json_error('Security check failed');
            return;
        }
        
        if (!self::is_cart_available()) {
            wp_send_json_error('Cart is not available');
            return;
        }
        
        $product_id = intval($_POST['product_id']);
        
        if (!$product_id) {
            wp_send_json_error('Invalid product ID');
            return;
        }
        
        // Find cart item key for this product
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            if ($cart_item['product_id'] == $product_id) {
                wp_send_json_success(array(
                    'cart_item_key' => $cart_item_key
                ));
                return;
            }
        }
        
        wp_send_json_error('Product not found in cart');
    }
    
    /**
     * AJAX handler for getting cart content
     */
    public static function ajax_get_cart_content() {
        if (!check_ajax_referer('spc_nonce', 'nonce', false)) {
            wp_send_json_error('Security check failed');
            return;
        }
        
        if (!self::is_cart_available()) {
            wp_send_json_error('Cart is not available');
            return;
        }
        
        // Get cart total for display
        $total_amount = '';
        if (!WC()->cart->is_empty()) {
            $total_amount = WC()->cart->get_total();
        }
        
        wp_send_json_success(array(
            'cart_html' => self::get_cart_content(),
            'order_review_html' => self::get_order_review(),
            'total_amount' => $total_amount
        ));
    }

    /**
     * AJAX handler for getting cart count
     */
    public static function ajax_get_cart_count() {
        if (!check_ajax_referer('spc_nonce', 'nonce', false)) {
            wp_send_json_error('Security check failed');
            return;
        }
        
        if (!self::is_cart_available()) {
            wp_send_json_error('Cart is not available');
            return;
        }
        
        // Get enabler product ID to exclude from count
        $enabler_id = get_option('spc_enabler_product_id');
        $real_product_count = 0;
        
        // Count only real products (excluding enabler product)
        foreach (WC()->cart->get_cart() as $cart_item) {
            if ($cart_item['product_id'] != $enabler_id) {
                $real_product_count += $cart_item['quantity'];
            }
        }
        
        wp_send_json_success(array(
            'count' => $real_product_count,
            'has_min_products' => $real_product_count >= 3
        ));
    }
    
    /**
     * Custom order received shortcode
     */
    public static function custom_order_received_shortcode($atts) {
        if (!self::is_woocommerce_available()) {
            return '<div class="spc-error"><p>WooCommerce is not active.</p></div>';
        }
        
        return self::load_template('order-received');
    }
    
    /**
     * Create order received page if it doesn't exist
     */
    public static function create_order_received_page() {
        $page_slug = 'order-received';
        
        // Check if page already exists
        $existing_page = get_page_by_path($page_slug);
        if ($existing_page) {
            return $existing_page->ID;
        }
        
        // Create the page
        $page_data = array(
            'post_title' => 'Order Received',
            'post_content' => '[custom_order_received]',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_name' => $page_slug,
            'post_author' => 1
        );
        
        $page_id = wp_insert_post($page_data);
        
        if ($page_id && !is_wp_error($page_id)) {
            return $page_id;
        }
        
        return false;
    }
    
    /**
     * Test shortcode to debug products
     * 
     * @return string Debug information
     */
    public static function test_products_shortcode() {
        $products = get_posts(array(
            'post_type' => 'product',
            'posts_per_page' => 5,
            'post_status' => 'publish'
        ));
        
        $output = '<div class="spc-debug">';
        $output .= '<h3>Debug: Products Found - ' . count($products) . '</h3>';
        
        foreach ($products as $post) {
            $output .= '<p>Product: ' . esc_html($post->post_title) . ' (ID: ' . intval($post->ID) . ')</p>';
        }
        
        $output .= '</div>';
        
        return $output;
    }
}

/**
 * =============================================================================
 * INITIALIZE SINGLE PAGE CHECKOUT
 * =============================================================================
 */

// Initialize the Single Page Checkout functionality
SinglePageCheckout::init();

// Create order received page on theme activation
add_action('after_switch_theme', array('SinglePageCheckout', 'create_order_received_page'));

/**
 * =============================================================================
 * SIMPLE CART ENABLER - ADMIN SETTINGS
 * =============================================================================
 */

// Add admin menu
add_action('admin_menu', 'spc_add_admin_menu');
function spc_add_admin_menu() {
    add_submenu_page(
        'woocommerce',
        'Cart Enabler Settings',
        'Cart Enabler',
        'manage_options',
        'spc-cart-enabler',
        'spc_admin_page'
    );
}

// Register settings
add_action('admin_init', 'spc_register_settings');
function spc_register_settings() {
    register_setting('spc_cart_enabler', 'spc_enabler_product_id');
}

// Admin page content
function spc_admin_page() {
    ?>
<div class="wrap">
    <h1>Cart Enabler Settings</h1>
    <form method="post" action="options.php">
        <?php settings_fields('spc_cart_enabler'); ?>
        <table class="form-table">
            <tr>
                <th scope="row">$0 USD Product ID</th>
                <td>
                    <input type="number" name="spc_enabler_product_id"
                        value="<?php echo esc_attr(get_option('spc_enabler_product_id')); ?>" />
                    <p class="description">Enter the Product ID of a product with $0.00 price</p>

                    <?php 
                        $product_id = get_option('spc_enabler_product_id');
                        if ($product_id) {
                            $product = wc_get_product($product_id);
                            if ($product) {
                                echo '<p><strong>Current Product:</strong> ' . esc_html($product->get_name()) . '</p>';
                                echo '<p><strong>Price:</strong> ' . wc_price($product->get_price()) . '</p>';
                                echo '<p><strong>Status:</strong> ' . ucfirst($product->get_status()) . '</p>';
                                
                                if ($product->get_status() === 'private') {
                                    echo '<p style="color: red;"><strong>⚠️ Warning:</strong> Private products may cause payment method issues. Consider making this product "Published" but hidden from catalog.</p>';
                                }
                                
                                if ($product->get_price() > 0) {
                                    echo '<p style="color: red;"><strong>⚠️ Warning:</strong> This product has a price greater than $0. This may affect checkout behavior.</p>';
                                }
                            } else {
                                echo '<p style="color: red;"><strong>⚠️ Error:</strong> Product not found.</p>';
                            }
                        }
                        ?>
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>

    <div class="spc-admin-instructions">
        <h3>Setup Instructions:</h3>
        <ol>
            <li>Create a new WooCommerce product</li>
            <li>Set the price to exactly <strong>$0.00</strong></li>
            <li>Set product status to <strong>"Published"</strong> (not Private)</li>
            <li>Under "Catalog visibility" select <strong>"Hidden"</strong></li>
            <li>Make it <strong>"Virtual"</strong> (recommended)</li>
            <li>Copy the Product ID and enter it above</li>
        </ol>

        <h3>Troubleshooting:</h3>
        <ul>
            <li><strong>Payment methods not showing?</strong> Make sure the product is Published, not Private</li>
            <li><strong>Duplicate products in cart?</strong> Clear your cart and try again</li>
            <li><strong>Product showing in cart?</strong> Check that the product ID is correct</li>
        </ul>
    </div>
</div>

<style>
.spc-admin-instructions {
    margin-top: 30px;
    padding: 20px;
    background: #f9f9f9;
    border-left: 4px solid #007cba;
}

.spc-admin-instructions ol,
.spc-admin-instructions ul {
    margin-left: 20px;
}
</style>
<?php
}

/**
 * =============================================================================
 * SIMPLE CART MANAGEMENT
 * =============================================================================
 */

// Add $0 product when checkout is loaded and cart is empty of real products
add_action('wp', 'spc_manage_enabler_product');
function spc_manage_enabler_product() {
    if (!is_page() || !has_shortcode(get_post()->post_content, 'single_page_checkout')) {
        return;
    }
    
    $enabler_id = get_option('spc_enabler_product_id');
    if (!$enabler_id || !WC()->cart) return;
    
    // Check if cart has real products
    $has_real_products = false;
    foreach (WC()->cart->get_cart() as $cart_item) {
        if ($cart_item['product_id'] != $enabler_id) {
            $has_real_products = true;
            break;
        }
    }
    
    // If no real products, ensure enabler is in cart
    if (!$has_real_products) {
        $enabler_in_cart = false;
        foreach (WC()->cart->get_cart() as $cart_item) {
            if ($cart_item['product_id'] == $enabler_id) {
                $enabler_in_cart = true;
                break;
            }
        }
        
        if (!$enabler_in_cart) {
            WC()->cart->add_to_cart($enabler_id, 1);
        }
    }
}