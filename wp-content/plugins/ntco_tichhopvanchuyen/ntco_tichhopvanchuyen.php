<?php
/**
 * Plugin Name:     VietNam Shipping for WooCommerce
 * Plugin URI:
 * Description:     Support shipping couriers in Vietnam like GHN, GHTK, Viettel-Post.
 * Author:          Ntvco
 * Author URI:      https://Ntvco.com
 * Text Domain:     NTVCO_ghn_aff
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         NtvcoVnShipping
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NTVCO_VERSION', '0.2.0' );
define( 'NTVCO_DB_VERSION', '1.0.0' );
define( 'NTVCO_PLUGIN_FILE', __FILE__ );
define( 'NTVCO_PLUGIN_DIR_PATH', plugin_dir_path( NTVCO_PLUGIN_FILE ) );
define( 'NTVCO_PLUGIN_DIR_URL', plugin_dir_url( NTVCO_PLUGIN_FILE ) );
define( 'NTVCO_ASSETS_URL', NTVCO_PLUGIN_DIR_URL . 'dist' );
define( 'NTVCO_MINIMUM_PHP_VERSION', '7.1.3' );

require __DIR__ . '/third-party/vendor/scoper-autoload.php';
require __DIR__ . '/vendor/autoload.php';

require __DIR__ . '/inc/namespace.php';
/**
* Change the default state and country on the checkout page
*/

add_filter( 'default_checkout_billing_country', 'my_default_checkout_country' );
add_filter( 'default_checkout_billing_state', 'my_default_checkout_state' );

function my_default_checkout_country() {
return 'VN'; // country code
}

function my_default_checkout_state() {
return 'VN:79'; // state code
}
