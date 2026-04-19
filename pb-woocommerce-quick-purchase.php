<?php
/**
 * Plugin Name:       PB WooCommerce Quick Purchase
 * Plugin URI:        https://github.com/bencicpatricija/pb-wc-quick-purchase
 * Description:       Quick purchase for WooCommerce - bypass the cart and go directly to the checkout.
 * Version:           1.0.0
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

// Output the button after the normal "Add to cart" button.
add_action(
  'woocommerce_after_add_to_cart_button',
  function () {
    global $product;

    if ( ! $product instanceof WC_Product ) {
      return;
    }

    // Only show for simple products.
    if ( ! $product->is_type( 'simple' ) ) {
      return;
    }

    // Respect the admin checkbox.
    if ( 'yes' !== get_post_meta( $product->get_id(), '_pb_wcqp_enabled', true ) ) {
      return;
    }

    // Build the URL: add the product to the cart and redirect straight to checkout.
    $checkout_url = add_query_arg(
      [
        'add-to-cart'    => $product->get_id(),
        'quantity'       => 1,
        'pb_wcqp_direct' => '1',
      ],
      wc_get_checkout_url()
    );

    // The button is a plain <a> so it works without JavaScript.
    printf(
      '<a href="%s" class="button pb-wcqp-button alt">%s</a>',
      esc_url( $checkout_url ),
      esc_html__( 'Quick purchase', 'pb-wc-quick-purchase' )
    );
  },
  25
);

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

    // Only wipe the cart when the product being added has Quick purchase enabled.
    if ( 'yes' !== get_post_meta( $product_id, '_pb_wcqp_enabled', true ) ) {
      return $passed;
    }

    WC()->cart->empty_cart();

    return $passed;
  },
  10,
  2
);
