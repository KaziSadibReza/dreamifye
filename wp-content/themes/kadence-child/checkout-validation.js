// Simple checkout validation
jQuery(document).ready(function ($) {
  function checkCart() {
    $.ajax({
      url: checkout_ajax.ajax_url,
      type: "POST",
      data: {
        action: "spc_get_cart_count",
        nonce: checkout_ajax.nonce,
      },
      success: function (response) {
        if (response.success) {
          var count = response.data.count;
          if (count >= 3) {
            $("#spc-checkout-section").show();
            $(".checkout-message").hide();
          } else {
            $("#spc-checkout-section").hide();
            $(".checkout-message").show();
          }
        }
      },
    });
  }

  // Check on page load
  checkCart();

  // Check every 3 seconds
  setInterval(checkCart, 3000);

  // Listen for cart updates from SPC
  $(document).on("spc_cart_updated", function () {
    checkCart();
  });

  // Listen for WooCommerce cart updates
  $(document.body).on(
    "updated_cart_totals updated_checkout added_to_cart removed_from_cart",
    function () {
      setTimeout(checkCart, 200); // Small delay to ensure cart is updated
    }
  );
});
