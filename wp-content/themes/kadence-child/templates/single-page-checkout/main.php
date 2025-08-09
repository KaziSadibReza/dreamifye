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
        <svg width="36" height="36" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg"
            xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="iconify iconify--twemoji"
            preserveAspectRatio="xMidYMid meet" fill="#000000">

            <g id="SVGRepo_bgCarrier" stroke-width="0" />

            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round" />

            <g id="SVGRepo_iconCarrier">

                <path fill="#8c2765"
                    d="M28 22a1 1 0 0 1-1-1v-6c0-6.065-4.037-11-9-11c-4.962 0-9 4.935-9 11v6a1 1 0 1 1-2 0v-6C7 7.832 11.935 2 18 2s11 5.832 11 13v6a1 1 0 0 1-1 1z" />

                <path fill="#d46eac"
                    d="M33.386 12.972C33.126 10.788 31.114 9 28.914 9H7.086c-2.2 0-4.212 1.788-4.472 3.972L.472 31.028C.212 33.213 1.8 35 4 35h28c2.2 0 3.788-1.787 3.528-3.972l-2.142-18.056z" />

                <path fill="#d12e92"
                    d="M28 20a1 1 0 0 1-1-1v-6c0-6.065-4.037-11-9-11c-4.962 0-9 4.935-9 11v6a1 1 0 1 1-2 0v-6C7 5.832 11.935 0 18 0s11 5.832 11 13v6a1 1 0 0 1-1 1z" />

            </g>

        </svg> Select Your Products
    </h1>

    <div class="spc-main-grid">
        <!-- Left Column: Products + Checkout -->
        <div class="spc-main-content">
            <!-- Products Section -->
            <div class="spc-products-section">
                <!-- Products Grid with Card Loader -->
                <div id="spc-products-loading" class="spc-products-container">
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
                    <!-- Card Loaders for Slider (Default) -->
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

                <!-- Actual Products Grid/Slider -->
                <div id="spc-products-grid" class="spc-products-container" style="display: none;">
                    <?php echo SinglePageCheckout::load_template('products-grid', compact('atts')); ?>
                </div>
            </div>

            <!-- Mobile Cart Section (hidden on desktop) -->
            <div class="spc-mobile-cart">
                <div class="spc-card">
                    <div class="spc-card-header">
                        <h3 class="spc-card-title">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M7.5 18C8.32843 18 9 18.6716 9 19.5C9 20.3284 8.32843 21 7.5 21C6.67157 21 6 20.3284 6 19.5C6 18.6716 6.67157 18 7.5 18Z"
                                    stroke="#1C274C" stroke-width="1.5" />
                                <path
                                    d="M16.5 18.0001C17.3284 18.0001 18 18.6716 18 19.5001C18 20.3285 17.3284 21.0001 16.5 21.0001C15.6716 21.0001 15 20.3285 15 19.5001C15 18.6716 15.6716 18.0001 16.5 18.0001Z"
                                    stroke="#1C274C" stroke-width="1.5" />
                                <path d="M11 10.8L12.1429 12L15 9" stroke="#1C274C" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M2 3L2.26121 3.09184C3.5628 3.54945 4.2136 3.77826 4.58584 4.32298C4.95808 4.86771 4.95808 5.59126 4.95808 7.03836V9.76C4.95808 12.7016 5.02132 13.6723 5.88772 14.5862C6.75412 15.5 8.14857 15.5 10.9375 15.5H12M16.2404 15.5C17.8014 15.5 18.5819 15.5 19.1336 15.0504C19.6853 14.6008 19.8429 13.8364 20.158 12.3075L20.6578 9.88275C21.0049 8.14369 21.1784 7.27417 20.7345 6.69708C20.2906 6.12 18.7738 6.12 17.0888 6.12H11.0235M4.95808 6.12H7"
                                    stroke="#1C274C" stroke-width="1.5" stroke-linecap="round" />
                            </svg> Your Cart
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
                        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M7.5 18C8.32843 18 9 18.6716 9 19.5C9 20.3284 8.32843 21 7.5 21C6.67157 21 6 20.3284 6 19.5C6 18.6716 6.67157 18 7.5 18Z"
                                stroke="#1C274C" stroke-width="1.5" />
                            <path
                                d="M16.5 18.0001C17.3284 18.0001 18 18.6716 18 19.5001C18 20.3285 17.3284 21.0001 16.5 21.0001C15.6716 21.0001 15 20.3285 15 19.5001C15 18.6716 15.6716 18.0001 16.5 18.0001Z"
                                stroke="#1C274C" stroke-width="1.5" />
                            <path d="M11 10.8L12.1429 12L15 9" stroke="#1C274C" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round" />
                            <path
                                d="M2 3L2.26121 3.09184C3.5628 3.54945 4.2136 3.77826 4.58584 4.32298C4.95808 4.86771 4.95808 5.59126 4.95808 7.03836V9.76C4.95808 12.7016 5.02132 13.6723 5.88772 14.5862C6.75412 15.5 8.14857 15.5 10.9375 15.5H12M16.2404 15.5C17.8014 15.5 18.5819 15.5 19.1336 15.0504C19.6853 14.6008 19.8429 13.8364 20.158 12.3075L20.6578 9.88275C21.0049 8.14369 21.1784 7.27417 20.7345 6.69708C20.2906 6.12 18.7738 6.12 17.0888 6.12H11.0235M4.95808 6.12H7"
                                stroke="#1C274C" stroke-width="1.5" stroke-linecap="round" />
                        </svg> Your Cart
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