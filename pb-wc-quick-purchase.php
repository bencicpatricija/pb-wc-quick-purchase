<?php
/**
 * Plugin Name:       PB WC Quick Purchase
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
