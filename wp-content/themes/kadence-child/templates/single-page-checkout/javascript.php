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
        swipers: {}, // Store multiple swiper instances for different categories

        init: function() {
            this.toastContainer = document.getElementById('spc-toast-container');
            this.bindEvents();
            this.loadProducts();
            this.initializeCart();
            this.handleResponsiveChanges();

            console.log('SPC Modern JavaScript v3.1.0 Loaded with Category Support');
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

        handleResponsiveChanges: function() {
            let resizeTimer;
            $(window).on('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    SPC.adjustLayoutForScreenSize();
                }, 250); // Debounce resize events
            });
        },

        adjustLayoutForScreenSize: function() {
            const isMobile = window.innerWidth <= 768;
            const targetView = isMobile ? 'grid' : 'slider';
            
            $('.spc-category-section').each(function() {
                const categorySlug = $(this).data('category');
                const $currentActiveBtn = $(`.spc-view-btn[data-category="${categorySlug}"].active`);
                const currentView = $currentActiveBtn.data('view');
                
                // Only switch if the current view doesn't match the target view for this screen size
                if (currentView !== targetView) {
                    const $targetBtn = $(`.spc-view-btn[data-category="${categorySlug}"][data-view="${targetView}"]`);
                    if ($targetBtn.length) {
                        $targetBtn.trigger('click');
                    }
                }
            });
        },

        loadProducts: function() {
            // Show loader initially
            $('#spc-products-loading').show();
            $('#spc-products-grid').hide();

            // Detect if device is mobile (768px and below)
            const isMobile = window.innerWidth <= 768;
            const defaultView = isMobile ? 'grid' : 'slider';

            // Show appropriate card loader based on default view
            if (defaultView === 'grid') {
                $('.spc-card-loader-slider').hide();
                $('.spc-card-loader-grid').show();
            } else {
                $('.spc-card-loader-slider').show();
                $('.spc-card-loader-grid').hide();
            }

            // Simulate loading delay
            setTimeout(() => {
                $('#spc-products-loading').fadeOut(300, function() {
                    $('#spc-products-grid').fadeIn(300, function() {
                        // Initialize all category layouts - Default to grid for mobile, slider for desktop
                        $('.spc-category-section').each(function() {
                            const categorySlug = $(this).data('category');
                            
                            if (defaultView === 'grid') {
                                // Set grid as default for mobile
                                $(`.spc-view-btn[data-category="${categorySlug}"][data-view="grid"]`)
                                    .addClass('active');
                                $(`.spc-view-btn[data-category="${categorySlug}"][data-view="slider"]`)
                                    .removeClass('active');
                                $(`.spc-products-grid[data-category="${categorySlug}"]`)
                                    .addClass('active').show();
                                $(`.spc-products-slider[data-category="${categorySlug}"]`)
                                    .removeClass('active').hide();
                            } else {
                                // Set slider as default for desktop
                                $(`.spc-view-btn[data-category="${categorySlug}"][data-view="slider"]`)
                                    .addClass('active');
                                $(`.spc-view-btn[data-category="${categorySlug}"][data-view="grid"]`)
                                    .removeClass('active');
                                $(`.spc-products-slider[data-category="${categorySlug}"]`)
                                    .addClass('active').show();
                                $(`.spc-products-grid[data-category="${categorySlug}"]`)
                                    .removeClass('active').hide();

                                // Initialize swiper for this category (only for slider view)
                                SPC.initSwiper(categorySlug);
                            }
                        });

                        // Load cart content after products are displayed
                        SPC.loadCartContent();
                    });
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
                        SPC.showToast(`${productTitle} কার্টে যোগ করা হয়েছে!`, 'success',
                            'পণ্য যোগ করা হয়েছে');

                        // Trigger cart validation check
                        $(document).trigger('spc_cart_updated');
                    } else {
                        SPC.showToast(response.data || 'পণ্য কার্টে যোগ করতে ব্যর্থ', 'error',
                            'ত্রুটি');
                    }
                },
                error: function() {
                    $product.removeClass('spc-loading');
                    SPC.showToast('নেটওয়ার্ক ত্রুটি। আবার চেষ্টা করুন।', 'error',
                        'সংযোগ ত্রুটি');
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
                        SPC.showToast('কার্ট থেকে পণ্য সরাতে ব্যর্থ', 'error', 'ত্রুটি');
                    }
                },
                error: function() {
                    $product.removeClass('spc-loading');
                    SPC.showToast('নেটওয়ার্ক ত্রুটি। আবার চেষ্টা করুন।', 'error',
                        'সংযোগ ত্রুটি');
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
                        SPC.showToast(`${productTitle} কার্ট থেকে সরানো হয়েছে`, 'success',
                            'পণ্য সরানো হয়েছে');

                        // Trigger cart validation check
                        $(document).trigger('spc_cart_updated');
                    } else {
                        SPC.showToast(response.data || 'কার্ট থেকে পণ্য সরাতে ব্যর্থ',
                            'error', 'ত্রুটি');
                    }
                },
                error: function() {
                    $product.removeClass('spc-loading');
                    SPC.showToast('নেটওয়ার্ক ত্রুটি। আবার চেষ্টা করুন।', 'error',
                        'সংযোগ ত্রুটি');
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
                        SPC.showToast(`${productTitle} কার্ট থেকে সরানো হয়েছে`, 'success',
                            'পণ্য সরানো হয়েছে');

                        // Trigger cart validation check
                        $(document).trigger('spc_cart_updated');
                    } else {
                        SPC.showToast(response.data || 'কার্ট থেকে পণ্য সরাতে ব্যর্থ',
                            'error', 'ত্রুটি');
                    }
                },
                error: function() {
                    SPC.showToast('নেটওয়ার্ক ত্রুটি। আবার চেষ্টা করুন।', 'error',
                        'সংযোগ ত্রুটি');
                }
            });
        },

        handleQuantity: function(e) {
            e.preventDefault();
            e.stopPropagation();

            const $button = $(this);
            const action = $button.hasClass('spc-quantity-increase') ? 'increase' : 'decrease';
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

                        const actionText = action === 'increase' ? 'বৃদ্ধি করা হয়েছে' :
                            'কমানো হয়েছে';
                        SPC.showToast(`${productTitle} এর পরিমাণ ${actionText}`, 'success');

                        // Trigger cart validation check
                        $(document).trigger('spc_cart_updated');
                    } else {
                        SPC.showToast(response.data || 'পরিমাণ আপডেট করতে ব্যর্থ', 'error',
                            'ত্রুটি');
                    }
                },
                error: function() {
                    SPC.showToast('নেটওয়ার্ক ত্রুটি। আবার চেষ্টা করুন।', 'error',
                        'সংযোগ ত্রুটি');
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

        // Layout switching functionality for categories
        switchLayout: function(e) {
            e.preventDefault();
            const $btn = $(this);
            const layout = $btn.data('view');
            const category = $btn.data('category');

            // Don't proceed if already active
            if ($btn.hasClass('active')) return;

            // Update active button for this category only
            $(`.spc-view-btn[data-category="${category}"]`).removeClass('active');
            $btn.addClass('active');

            // Get category-specific elements
            const $categorySection = $(`.spc-category-section[data-category="${category}"]`);
            const $gridLayout = $categorySection.find(`.spc-products-grid[data-category="${category}"]`);
            const $sliderLayout = $categorySection.find(
                `.spc-products-slider[data-category="${category}"]`);
            const $categoryLoader = $(`.spc-category-loading[data-category="${category}"]`);

            // Show appropriate card loader for this category
            if (layout === 'grid') {
                $categoryLoader.find('.spc-card-loader-slider').hide();
                $categoryLoader.find('.spc-card-loader-grid').show();
            } else {
                $categoryLoader.find('.spc-card-loader-grid').hide();
                $categoryLoader.find('.spc-card-loader-slider').show();
            }

            // Hide current layouts and show category-specific loader
            $gridLayout.fadeOut(200);
            $sliderLayout.fadeOut(200, function() {
                $categoryLoader.fadeIn(200);

                // Switch layouts after loader is shown
                setTimeout(() => {
                    if (layout === 'grid') {
                        // Destroy swiper for this category if exists
                        if (SPC.swipers[category]) {
                            SPC.swipers[category].destroy(true, true);
                            delete SPC.swipers[category];
                        }

                        $sliderLayout.hide().removeClass('active');
                        $gridLayout.addClass('active').show();
                    } else {
                        $gridLayout.hide().removeClass('active');
                        $sliderLayout.addClass('active').show();

                        // Initialize swiper for this category
                        setTimeout(() => SPC.initSwiper(category), 100);
                    }

                    // Hide category loader and show updated layouts
                    $categoryLoader.fadeOut(200, function() {
                        if (layout === 'grid') {
                            $gridLayout.fadeIn(200);
                        } else {
                            $sliderLayout.fadeIn(200);
                        }

                        // Sync selection states and show toast
                        SPC.syncProductSelectionStates();
                        const layoutText = layout === 'grid' ? 'গ্রিড' : 'স্লাইডার';
                        SPC.showToast(
                            `${category.charAt(0).toUpperCase() + category.slice(1)} লেআউট ${layoutText} ভিউতে পরিবর্তিত হয়েছে`,
                            'success', 'ভিউ পরিবর্তিত হয়েছে');
                    });
                }, 600); // Simulate loading time
            });
        },

        // Initialize Swiper.js for specific category
        initSwiper: function(category = null) {
            if (category) {
                // Initialize swiper for specific category
                const sliderSelector = `.spc-slider-track[data-category="${category}"]`;
                const nextSelector = `.spc-slider-next[data-category="${category}"]`;
                const prevSelector = `.spc-slider-prev[data-category="${category}"]`;

                // Destroy existing swiper for this category if exists
                if (SPC.swipers[category]) {
                    SPC.swipers[category].destroy(true, true);
                    delete SPC.swipers[category];
                }

                // Only initialize if the slider container exists and is visible
                if ($(sliderSelector).length && $(`.spc-products-slider[data-category="${category}"]`).is(
                        ':visible')) {
                    try {
                        SPC.swipers[category] = new Swiper(sliderSelector, {
                            slidesPerView: 2,
                            spaceBetween: 10,
                            freeMode: true,
                            grabCursor: true,
                            observer: true,
                            observeParents: true,
                            autoplay: {
                                delay: 10000, // 1 minute = 60,000 milliseconds
                                disableOnInteraction: false,
                                pauseOnMouseEnter: true,
                            },
                            navigation: {
                                nextEl: nextSelector,
                                prevEl: prevSelector,
                            },
                            breakpoints: {
                                640: {
                                    slidesPerView: 2,
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
                            },
                            on: {
                                init: function() {
                                    console.log(
                                        `Swiper initialized for category: ${category} with autoplay (1 minute)`
                                    );
                                }
                            }
                        });
                    } catch (error) {
                        console.error(`Error initializing Swiper for category ${category}:`, error);
                    }
                }
            } else {
                // Initialize all category swipers that are in slider mode
                $('.spc-category-section').each(function() {
                    const categorySlug = $(this).data('category');
                    if ($(`.spc-products-slider[data-category="${categorySlug}"]`).is(':visible')) {
                        SPC.initSwiper(categorySlug);
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
/* Category Section Styles */
.spc-category-section {
    margin-bottom: 3rem;
    position: relative;
}

.spc-category-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--spc-border, #e5e7eb);
}

.spc-category-title {
    font-size: 1.75rem;
    font-weight: 700;
    margin: 0;
    color: var(--spc-text-primary, #1f2937);
    text-transform: capitalize;
}

.spc-category-controls {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.spc-view-toggle {
    display: flex;
    background: var(--spc-bg-card, #ffffff);
    border: 1px solid var(--spc-border, #e5e7eb);
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.spc-view-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    background: transparent;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--spc-text-secondary, #6b7280);
}

.spc-view-btn:hover {
    background: var(--spc-bg-hover, #f9fafb);
    color: var(--spc-text-primary, #1f2937);
}

.spc-view-btn.active {
    background: var(--spc-pink, #ec4899);
    color: white;
}

.spc-view-btn svg {
    width: 16px;
    height: 16px;
}

/* Grid Layout Styles for Categories */
.spc-products-grid[data-category] {
    display: none;
}

.spc-products-grid[data-category].active {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
}

/* Slider Layout Styles for Categories */
.spc-products-slider[data-category] {
    display: none;
}

.spc-products-slider[data-category].active {
    display: block;
}

/* Responsive Design */
@media (max-width: 768px) {
    .spc-category-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .spc-category-title {
        font-size: 1.5rem;
    }

    .spc-view-btn {
        padding: 0.625rem 0.875rem;
        font-size: 0.8rem;
    }

    .spc-products-grid[data-category].active {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1rem;
    }
}

@media (max-width: 480px) {
    .spc-products-grid[data-category].active {
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
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