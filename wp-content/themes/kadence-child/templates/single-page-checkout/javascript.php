<?php
/**
 * JavaScript Template - Modern Clean Version
 * 
 * @package KadenceChild
 * @version 2.0.1
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Swiper.js CSS and JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
(function($) {
    'use strict';

    const SPC = {
        toastContainer: null,
        swiper: null,

        init: function() {
            this.toastContainer = document.getElementById('spc-toast-container');
            this.bindEvents();
            this.loadProducts();
            this.initializeCart();

            console.log('SPC Modern JavaScript v2.0.1 Loaded with Swiper.js');
        },

        bindEvents: function() {
            $(document).on('click', '.spc-product-item', this.toggleProduct);
            $(document).on('click', '.spc-remove-btn', this.removeFromCart);
            $(document).on('click', '.spc-quantity-btn', this.handleQuantity);
            $(document).on('click', '.spc-view-btn', this.switchLayout);
            $(document).on('click', '.spc-zoom-btn', this.openZoom);
            $(document).on('click', '.spc-zoom-close, .spc-zoom-backdrop', this.closeZoom);

            // Prevent product selection when clicking zoom button
            $(document).on('click', '.spc-zoom-btn', function(e) {
                e.stopPropagation();
            });
        },

        loadProducts: function() {
            // Initialize layout toggle - Default to slider
            const defaultView = 'slider';
            $(`.spc-view-btn[data-view="${defaultView}"]`).addClass('active');
            $(`.spc-products-${defaultView}`).addClass('active');

            // Show appropriate card loader - Default to slider
            $('.spc-card-loader-slider').show();
            $('.spc-card-loader-grid').hide();

            // Simulate loading delay for card loader
            setTimeout(() => {
                $('#spc-products-loading').fadeOut(300, function() {
                    $('#spc-products-grid').fadeIn(300);
                    // Initialize Swiper after products are loaded
                    SPC.initSwiper();
                    // After products are loaded, ensure cart content is displayed
                    SPC.loadCartContent();
                });
            }, 800);
        },

        initializeCart: function() {
            this.loadCartContent();
            this.updateCartCount();
        },

        loadCartContent: function() {
            // Load the current cart content on page load
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'spc_get_cart_content',
                    nonce: '<?php echo wp_create_nonce('spc_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $('#spc-cart-items, #spc-mobile-cart-items').html(response.data
                            .cart_html);
                        $('#spc-checkout-content').html(response.data.checkout_html);

                        // Always update order review - either with content or clear it
                        $('.spc-cart-total, .spc-mobile-cart-total').html(response.data
                            .order_review_html || '');

                        SPC.updateCartCount();

                        // Sync product selection states
                        SPC.syncProductSelectionStates();

                        // Update total amount
                        if (response.data.total_amount) {
                            $('#spc-total-amount').html(response.data.total_amount);
                        }
                    }
                },
                error: function() {
                    console.log('Failed to load cart content');
                }
            });
        },
        showToast: function(message, type = 'info', title = '') {
            if (!this.toastContainer) return;

            const toastId = 'toast-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);

            // Modern SVG icons
            const icons = {
                success: `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20,6 9,17 4,12"></polyline>
                </svg>`,
                error: `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>`,
                warning: `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                    <path d="M12 9v4"></path>
                    <path d="m12 17 .01 0"></path>
                </svg>`,
                info: `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="m12 16-4-4 4-4"></path>
                    <path d="m16 12H8"></path>
                </svg>`
            };

            // Create toast element
            const toast = document.createElement('div');
            toast.className = `spc-toast ${type}`;
            toast.id = toastId;

            toast.innerHTML = `
                <div class="spc-toast-icon">${icons[type] || icons.info}</div>
                <div class="spc-toast-content">
                    ${title ? `<div class="spc-toast-title">${title}</div>` : ''}
                    <div class="spc-toast-message">${message}</div>
                </div>
                <button class="spc-toast-close" onclick="SPC.hideToast('${toastId}')">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            `;

            // Add to container
            this.toastContainer.appendChild(toast);

            // Trigger show animation
            requestAnimationFrame(() => {
                toast.classList.add('show');
            });

            // Auto hide after 5 seconds
            setTimeout(() => {
                this.hideToast(toastId);
            }, 5000);

            // Add click to dismiss
            toast.addEventListener('click', (e) => {
                if (e.target === toast || e.target.classList.contains('spc-toast-content') ||
                    e.target.classList.contains('spc-toast-message') || e.target.classList.contains(
                        'spc-toast-title')) {
                    this.hideToast(toastId);
                }
            });
        },

        hideToast: function(toastId) {
            const toast = document.getElementById(toastId);
            if (toast && !toast.classList.contains('hiding')) {
                // Add hiding class for exit animation
                toast.classList.add('hiding');
                toast.classList.remove('show');

                // Remove from DOM after animation
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 250);
            }
        },

        toggleProduct: function(e) {
            e.preventDefault();

            const $product = $(this);
            const productId = $product.data('product-id');
            const isSelected = $product.hasClass('selected');

            // Get product title from data attribute or image alt text since we removed title elements
            const productTitle = $product.find('img').attr('alt') || `Product ${productId}`;

            if ($product.hasClass('spc-loading')) return;

            $product.addClass('spc-loading');

            if (isSelected) {
                SPC.removeProductFromCart(productId, $product, productTitle);
            } else {
                SPC.addProductToCart(productId, $product, productTitle);
            }
        },

        addProductToCart: function(productId, $product, productTitle) {
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'spc_add_to_cart',
                    product_id: productId,
                    nonce: '<?php echo wp_create_nonce('spc_nonce'); ?>'
                },
                success: function(response) {
                    $product.removeClass('spc-loading');

                    if (response.success) {
                        $product.addClass('selected');
                        SPC.updateCartContent(response.data.cart_html, response.data
                            .order_review_html);
                        SPC.updateCheckoutContent(response.data.checkout_html);
                        SPC.showToast(`${productTitle} added to cart!`, 'success',
                            'Product Added');
                    } else {
                        SPC.showToast(response.data || 'Failed to add product to cart', 'error',
                            'Error');
                    }
                },
                error: function() {
                    $product.removeClass('spc-loading');
                    SPC.showToast('Network error. Please try again.', 'error',
                        'Connection Error');
                }
            });
        },

        removeProductFromCart: function(productId, $product, productTitle) {
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'spc_get_cart_item_key',
                    product_id: productId,
                    nonce: '<?php echo wp_create_nonce('spc_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        SPC.removeFromCartByKey(response.data.cart_item_key, $product,
                            productTitle);
                    } else {
                        $product.removeClass('spc-loading');
                        SPC.showToast('Failed to remove product from cart', 'error', 'Error');
                    }
                },
                error: function() {
                    $product.removeClass('spc-loading');
                    SPC.showToast('Network error. Please try again.', 'error',
                        'Connection Error');
                }
            });
        },

        removeFromCartByKey: function(cartItemKey, $product, productTitle) {
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'spc_remove_from_cart',
                    cart_item_key: cartItemKey,
                    nonce: '<?php echo wp_create_nonce('spc_nonce'); ?>'
                },
                success: function(response) {
                    $product.removeClass('spc-loading');

                    if (response.success) {
                        $product.removeClass('selected');
                        SPC.updateCartContent(response.data.cart_html, response.data
                            .order_review_html);
                        SPC.updateCheckoutContent(response.data.checkout_html);
                        SPC.showToast(`${productTitle} removed from cart`, 'success',
                            'Product Removed');
                    } else {
                        SPC.showToast(response.data || 'Failed to remove product from cart',
                            'error', 'Error');
                    }
                },
                error: function() {
                    $product.removeClass('spc-loading');
                    SPC.showToast('Network error. Please try again.', 'error',
                        'Connection Error');
                }
            });
        },

        removeFromCart: function(e) {
            e.preventDefault();
            e.stopPropagation();

            const $button = $(this);
            const cartItemKey = $button.data('cart-item-key');
            const productId = $button.data('product-id');
            const productTitle = $button.closest('.spc-cart-item').find('.spc-cart-item-title').text();

            if ($button.closest('.spc-cart-item').hasClass('spc-loading')) return;

            $button.closest('.spc-cart-item').addClass('spc-loading');

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'spc_remove_from_cart',
                    cart_item_key: cartItemKey,
                    nonce: '<?php echo wp_create_nonce('spc_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $(`.spc-product-item[data-product-id="${productId}"]`).removeClass(
                            'selected');
                        SPC.updateCartContent(response.data.cart_html, response.data
                            .order_review_html);
                        SPC.updateCheckoutContent(response.data.checkout_html);
                        SPC.showToast(`${productTitle} removed from cart`, 'success',
                            'Product Removed');
                    } else {
                        SPC.showToast(response.data || 'Failed to remove product from cart',
                            'error', 'Error');
                    }
                },
                error: function() {
                    SPC.showToast('Network error. Please try again.', 'error',
                        'Connection Error');
                }
            });
        },

        handleQuantity: function(e) {
            e.preventDefault();
            e.stopPropagation();

            const $button = $(this);
            const action = $button.hasClass('spc-increase-qty') ? 'increase' : 'decrease';
            const cartItemKey = $button.data('cart-item-key');
            const $cartItem = $button.closest('.spc-cart-item');
            const productTitle = $cartItem.find('.spc-cart-item-title').text();

            if ($cartItem.hasClass('spc-loading')) return;

            $cartItem.addClass('spc-loading');

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'spc_update_quantity',
                    cart_item_key: cartItemKey,
                    quantity_action: action,
                    nonce: '<?php echo wp_create_nonce('spc_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        SPC.updateCartContent(response.data.cart_html, response.data
                            .order_review_html);
                        SPC.updateCheckoutContent(response.data.checkout_html);

                        const actionText = action === 'increase' ? 'increased' : 'decreased';
                        SPC.showToast(`${productTitle} quantity ${actionText}`, 'success');
                    } else {
                        SPC.showToast(response.data || 'Failed to update quantity', 'error',
                            'Error');
                    }
                },
                error: function() {
                    SPC.showToast('Network error. Please try again.', 'error',
                        'Connection Error');
                }
            });
        },

        updateCartContent: function(html, orderReviewHtml = null) {
            // Update both desktop and mobile cart sections
            $('#spc-cart-items, #spc-mobile-cart-items').html(html);

            // Always update order review - either with content or clear it
            if (orderReviewHtml !== null) {
                $('.spc-cart-total, .spc-mobile-cart-total').html(orderReviewHtml);
            }

            this.updateCartCount();

            // Sync product selection states after cart update
            this.syncProductSelectionStates();
        },
        updateCartCount: function() {
            const itemCount = $('#spc-cart-items .spc-cart-item').length;
            $('#spc-cart-count, #spc-mobile-cart-count').text(itemCount);

            if (itemCount > 0) {
                $('#spc-checkout-section').slideDown(300);
            } else {
                $('#spc-checkout-section').slideUp(300);
            }
        },

        updateCheckoutContent: function(html) {
            $('#spc-checkout-content').html(html);

            const $checkoutHtml = $(html);
            const $total = $checkoutHtml.find(
                '.order-total .woocommerce-Price-amount, .total .amount, .order-total .amount');

            if ($total.length) {
                $('#spc-total-amount').html($total.first().html());
            }
        },

        // Layout switching functionality
        switchLayout: function(e) {
            e.preventDefault();
            const $btn = $(this);
            const layout = $btn.data('view');

            // Update active button
            $('.spc-view-btn').removeClass('active');
            $btn.addClass('active');

            // Show loader and hide products during transition
            $('#spc-products-grid').fadeOut(150, function() {
                $('#spc-products-loading').fadeIn(150);

                // Show appropriate card loader based on target layout
                if (layout === 'grid') {
                    $('.spc-card-loader-slider').hide();
                    $('.spc-card-loader-grid').show();
                } else {
                    $('.spc-card-loader-grid').hide();
                    $('.spc-card-loader-slider').show();
                }

                // Simulate loading time and then switch layouts
                setTimeout(() => {
                    // Show/hide layouts
                    if (layout === 'grid') {
                        $('.spc-products-grid').show().addClass('active');
                        $('.spc-products-slider').hide().removeClass('active');

                        // Destroy swiper if exists
                        if (SPC.swiper) {
                            SPC.swiper.destroy(true, true);
                            SPC.swiper = null;
                        }
                    } else {
                        $('.spc-products-grid').hide().removeClass('active');
                        $('.spc-products-slider').show().addClass('active');

                        // Initialize swiper for slider layout
                        setTimeout(() => SPC.initSwiper(), 100);
                    }

                    // Hide loader and show products
                    $('#spc-products-loading').fadeOut(150, function() {
                        $('#spc-products-grid').fadeIn(150, function() {
                            // Initialize swiper for slider layout AFTER the fadeIn is complete
                            if (layout === 'slider') {
                                // Add extra delay to ensure DOM is fully rendered
                                setTimeout(() => SPC.initSwiper(), 300);
                            }
                            
                            // Sync selection states between layouts
                            SPC.syncProductSelectionStates();

                            SPC.showToast('Layout switched to ' + layout + ' view',
                                'success', 'View Changed');
                        });
                    });
                }, 600); // Loading simulation time
            });
        },

        // Initialize Swiper.js
        initSwiper: function() {
            // Only initialize if we're in slider mode and swiper doesn't exist
            if ($('.spc-products-slider').is(':visible') && !SPC.swiper) {
                SPC.swiper = new Swiper('.spc-slider-track', {
                    slidesPerView: 1,
                    spaceBetween: 0,
                    freeMode: true,
                    grabCursor: true,
                    navigation: {
                        nextEl: '.spc-slider-next',
                        prevEl: '.spc-slider-prev',
                    },
                    breakpoints: {
                        640: {
                            slidesPerView: 1,
                            spaceBetween: 15,
                        },
                        768: {
                            slidesPerView: 3,
                            spaceBetween: 20,
                        },
                        1024: {
                            slidesPerView: 3,
                            spaceBetween: 24,
                        }
                    }
                });
            }
        },

        // Sync product selection states between grid and slider
        syncProductSelectionStates: function() {
            // Get all selected products from cart
            const selectedProducts = [];
            $('#spc-cart-items .spc-cart-item').each(function() {
                const productId = $(this).data('product-id');
                if (productId) {
                    selectedProducts.push(productId.toString());
                }
            });

            // Update both grid and slider items
            $('.spc-product-item').each(function() {
                const $item = $(this);
                const productId = $item.data('product-id').toString();

                if (selectedProducts.includes(productId)) {
                    $item.addClass('selected');
                } else {
                    $item.removeClass('selected');
                }

                // Remove any loading state
                $item.removeClass('spc-loading');
            });
        }, // Zoom functionality
        openZoom: function(e) {
            e.preventDefault();
            e.stopPropagation();

            const fullImageUrl = $(this).data('full-image');
            if (!fullImageUrl) return;

            $('#spc-zoom-image').attr('src', fullImageUrl);
            $('#spc-zoom-modal').css('display', 'flex').hide().fadeIn(300);

            // Prevent body scroll
            $('body').css('overflow', 'hidden');
        },

        closeZoom: function(e) {
            e.preventDefault();

            $('#spc-zoom-modal').fadeOut(300);

            // Restore body scroll
            $('body').css('overflow', '');
        }
    };

    $(document).ready(function() {
        SPC.init();
    });

    window.SPC = SPC;

})(jQuery);
</script>

<style>
.spc-loading {
    position: relative;
    pointer-events: none;
    opacity: 0.7;
}

.spc-loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    border: 2px solid var(--spc-pink);
    border-top: 2px solid transparent;
    border-radius: 50%;
    animation: spc-spin 1s linear infinite;
    transform: translate(-50%, -50%);
    z-index: 10;
    background: rgba(255, 255, 255, 0.9);
}

@keyframes spc-spin {
    0% {
        transform: translate(-50%, -50%) rotate(0deg);
    }

    100% {
        transform: translate(-50%, -50%) rotate(360deg);
    }
}

.spc-quantity-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.spc-quantity-btn:disabled:hover {
    background: var(--spc-bg-card);
    color: var(--spc-text-primary);
    transform: none;
}
</style>