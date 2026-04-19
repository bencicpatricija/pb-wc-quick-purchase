<?php
/**
 * Plugin Name:       PB WooCommerce Quick Purchase
 * Plugin URI:        https://github.com/bencicpatricija/pb-wc-quick-purchase
 * Description:       Quick purchase for WooCommerce - bypass the cart and go directly to the checkout.
 * Version:           1.2.0
 * Author:            Patricija Benčić
 * Author URI:        https://github.com/bencicpatricija
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       pb-wc-quick-purchase
 * Domain Path:       /languages
 * Requires at least: 5.0
 * Requires PHP:      7.0
 * Requires Plugins:  woocommerce
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

// Define plugin constants.
define( 'PB_WC_QUICK_PURCHASE_VERSION', '1.0.0' );
define( 'PB_WC_QUICK_PURCHASE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Load plugin scripts.
add_action(
  'wp_enqueue_scripts',
  function () {
    wp_enqueue_script(
      'pb-wc-quick-purchase',
      plugins_url( 'scripts.js', __FILE__ ),
      [],
      PB_WC_QUICK_PURCHASE_VERSION,
      true
    );

    $script_params = [
      'confirmMessage' => __( 'This will delete all items from your current shopping cart. Do you want to proceed?', 'pb-wc-quick-purchase' ),
    ];

    wp_localize_script( 'pb-wc-quick-purchase', 'pbWcQuickPurchaseParams', $script_params );
  }
);

// Add a checkbox to the product edit page to enable/disable quick purchase for each product.
add_action(
  'woocommerce_product_options_general_product_data',
  function () {
    woocommerce_wp_checkbox(
      [
        'id'          => '_pb_wcqp_enabled',
        'label'       => __( 'Quick purchase', 'pb-wc-quick-purchase' ),
        'description' => __( 'Display a "Quick purchase" button on the product page that skips the cart and goes straight to checkout.', 'pb-wc-quick-purchase' ),
      ]
    );
  }
);

// Save the checkbox value when the product is saved.
add_action(
  'woocommerce_process_product_meta',
  function ( $post_id ) {
    $enabled = isset( $_POST['_pb_wcqp_enabled'] ) ? 'yes' : 'no';

    update_post_meta( $post_id, '_pb_wcqp_enabled', $enabled );
  }
);

// Output the button after the "Add to cart" button on single product page.
add_action( 'woocommerce_after_add_to_cart_button', 'pb_wc_display_quick_purchase_button', 25 );

// Output the button after the "Add to cart" button on product archive pages.
add_action( 'woocommerce_after_shop_loop_item', 'pb_wc_display_quick_purchase_button', 15 );

// Display the "Quick purchase" button if enabled for the product.
function pb_wc_display_quick_purchase_button() {
  global $product;

  if ( ! $product instanceof WC_Product ) {
    return;
  }

  // Only show for simple products.
  if ( ! $product->is_type( 'simple' ) ) {
    return;
  }

  if ( 'yes' !== get_post_meta( $product->get_id(), '_pb_wcqp_enabled', true ) ) {
    return;
  }

  $checkout_url = add_query_arg(
    [
      'add-to-cart'    => $product->get_id(),
      'quantity'       => 1,
      'pb_wcqp_direct' => '1',
    ],
    wc_get_checkout_url()
  );

  return printf(
    '<a href="%s" class="button pb-wcqp-button alt">%s</a>',
    esc_url( $checkout_url ),
    esc_html__( 'Quick purchase', 'pb-wc-quick-purchase' )
  );
}

/**
 * Empty the cart BEFORE WooCommerce adds the new product.
 *
 * @param bool $passed      Current validation result from other validators.
 * @param int  $product_id  Product being added.
 * @return bool
 */
add_filter(
  'woocommerce_add_to_cart_validation',
  function ( $passed, $product_id ) {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if ( empty( $_REQUEST['pb_wcqp_direct'] ) ) {
      return $passed;
    }

    if ( 'yes' !== get_post_meta( $product_id, '_pb_wcqp_enabled', true ) ) {
      return $passed;
    }

    WC()->cart->empty_cart();

    return $passed;
  },
  10,
  2
);
