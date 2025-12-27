<?php
/**
 * Plugin Name: Wholesale Powerhouse
 * Plugin URI:
 * Description: A powerful, flexible wholesale pricing and user role system for WooCommerce. No custom tables - uses WP/WC meta exclusively.
 * Version: 1.0.0
 * Author: Mithu A Quayium
 * Author URI: https://profiles.wordpress.org/mithublue/
 * Text Domain: wholesale-powerhouse
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.5
 * Requires Plugins: woocommerce
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'WHOLPO_VERSION', '1.0.0' );
define( 'WHOLPO_PLUGIN_FILE', __FILE__ );
define( 'WHOLPO_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WHOLPO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WHOLPO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Check if WooCommerce is active
 */
function wholpo_check_woocommerce() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'wholpo_woocommerce_missing_notice' );
		return false;
	}
	return true;
}

/**
 * Display admin notice if WooCommerce is not active
 */
function wholpo_woocommerce_missing_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			echo esc_html__( 'Wholesale Powerhouse requires WooCommerce to be installed and active.', 'wholesale-powerhouse' );
			?>
		</p>
	</div>
	<?php
}

/**
 * The code that runs during plugin activation.
 */
function wholpo_activate() {
	require_once WHOLPO_PLUGIN_PATH . 'includes/class-wholpo-install.php';
	WHOLPO_Install::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function wholpo_deactivate() {
	require_once WHOLPO_PLUGIN_PATH . 'includes/class-wholpo-install.php';
	WHOLPO_Install::deactivate();
}

register_activation_hook( __FILE__, 'wholpo_activate' );
register_deactivation_hook( __FILE__, 'wholpo_deactivate' );

/**
 * Begin execution of the plugin.
 */
function wholpo_run() {
	// Check if WooCommerce is active
	if ( ! wholpo_check_woocommerce() ) {
		return;
	}

	// Include the main plugin class
	require_once WHOLPO_PLUGIN_PATH . 'includes/class-wholpo-powerhouse.php';

	// Run the plugin
	$plugin = new WHOLPO_Powerhouse();
	$plugin->run();
}
add_action( 'plugins_loaded', 'wholpo_run', 20 );

/**
 * Declare compatibility with WooCommerce HPOS (High-Performance Order Storage)
 */
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );
