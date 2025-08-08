<?php
/**
 * Single Page Checkout Main Template - Modern Design
 * 
 * @package KadenceChild
 * @version 2.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Toast Container -->
<div id="spc-toast-container" class="spc-toast-container"></div>

<div id="single-page-checkout-container" class="spc-container">
    <h1 class="spc-section-title">
        <span>🛍️</span> Select Your Products
    </h1>

    <div class="spc-main-grid">
        <!-- Left Column: Products + Checkout -->
        <div class="spc-main-content">
            <!-- Products Section -->
            <div class="spc-products-section">
                <!-- Products Grid with Card Loader -->
                <div id="spc-products-loading" class="spc-products-container">
                    <!-- Card Loaders for Grid -->
                    <div class="spc-card-loader-grid">
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
                    <div class="spc-card-loader-slider" style="display: none;">
                        <div class="spc-card-loader-slides">
                            <?php for ($i = 0; $i < 4; $i++): ?>
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

                <!-- Actual Products Grid/Slider -->
                <div id="spc-products-grid" class="spc-products-container" style="display: none;">
                    <div class="spc-layout-toggle">
                        <button id="spc-grid-view" class="spc-view-btn active" data-view="grid">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M3 3h7v7H3V3zm0 11h7v7H3v-7zm11-11h7v7h-7V3zm0 11h7v7h-7v-7z" />
                            </svg>
                            Grid
                        </button>
                        <button id="spc-slider-view" class="spc-view-btn" data-view="slider">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M2 6h20v3H2V6zm0 5h20v3H2v-3zm0 5h20v3H2v-3z" />
                            </svg>
                            Slider
                        </button>
                    </div>
                    <?php echo SinglePageCheckout::load_template('products-grid', compact('atts')); ?>
                </div>
            </div>

            <!-- Mobile Cart Section (hidden on desktop) -->
            <div class="spc-mobile-cart">
                <div class="spc-card">
                    <div class="spc-card-header">
                        <h3 class="spc-card-title">
                            <span>🛒</span> Your Cart
                            <span id="spc-mobile-cart-count" class="spc-cart-count">0</span>
                        </h3>
                    </div>
                    <div class="spc-card-body">
                        <div id="spc-mobile-cart-items" class="spc-cart-items">
                            <?php echo SinglePageCheckout::get_cart_content(); ?>
                        </div>
                        <div class="spc-mobile-cart-total">
                            <?php echo SinglePageCheckout::get_order_review(); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Checkout Form Section -->
            <div id="spc-checkout-section" class="spc-checkout-section"
                style="display: <?php echo (WC()->cart && !WC()->cart->is_empty()) ? 'block' : 'none'; ?>;">
                <div class="spc-card">
                    <div class="spc-card-header">
                        <h3 class="spc-card-title">
                            <span>📋</span> Checkout Details
                        </h3>
                    </div>
                    <div class="spc-card-body">
                        <div id="spc-checkout-content">
                            <?php echo SinglePageCheckout::get_checkout_content(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Sidebar: Desktop Cart (sticky) -->
        <div class="spc-sidebar-cart">
            <div class="spc-card spc-sticky-cart">
                <div class="spc-card-header">
                    <h3 class="spc-card-title">
                        <span>🛒</span> Your Cart
                        <span id="spc-cart-count" class="spc-cart-count">0</span>
                    </h3>
                </div>
                <div class="spc-card-body">
                    <div id="spc-cart-items" class="spc-cart-items">
                        <?php echo SinglePageCheckout::get_cart_content(); ?>
                    </div>
                    <div class="spc-cart-total">
                        <?php echo SinglePageCheckout::get_order_review(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php 
    // Load JavaScript with modern toast functionality
    echo SinglePageCheckout::load_template('javascript');
    ?>
</div>