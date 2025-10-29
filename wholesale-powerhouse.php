<?php
/**
 * Plugin Name: Wholesale Powerhouse by CyberCraft
 * Plugin URI:
 * Description: A powerful, flexible wholesale pricing and user role system for WooCommerce. No custom tables - uses WP/WC meta exclusively.
 * Version: 1.0.0
 * Author: Mithu A Quayium
 * Author URI: https://cybercraftit.com/
 * Text Domain: wholesale-powerhouse
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.5
 * Requires Plugins: woocommerce
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'WH_POWERHOUSE_VERSION', '1.0.0' );
define( 'WH_POWERHOUSE_PLUGIN_FILE', __FILE__ );
define( 'WH_POWERHOUSE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WH_POWERHOUSE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WH_POWERHOUSE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Check if WooCommerce is active
 */
function wh_powerhouse_check_woocommerce() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'wh_powerhouse_woocommerce_missing_notice' );
		return false;
	}
	return true;
}

/**
 * Display admin notice if WooCommerce is not active
 */
function wh_powerhouse_woocommerce_missing_notice() {
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
function activate_wh_powerhouse() {
	require_once WH_POWERHOUSE_PLUGIN_PATH . 'includes/class-wh-install.php';
	WH_Install::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_wh_powerhouse() {
	require_once WH_POWERHOUSE_PLUGIN_PATH . 'includes/class-wh-install.php';
	WH_Install::deactivate();
}

register_activation_hook( __FILE__, 'activate_wh_powerhouse' );
register_deactivation_hook( __FILE__, 'deactivate_wh_powerhouse' );

/**
 * Load plugin text domain for translations
 */
function wh_powerhouse_load_textdomain() {
	load_plugin_textdomain( 'wholesale-powerhouse', false, dirname( WH_POWERHOUSE_PLUGIN_BASENAME ) . '/languages' );
}
add_action( 'plugins_loaded', 'wh_powerhouse_load_textdomain' );

/**
 * Begin execution of the plugin.
 */
function run_wh_powerhouse() {
	// Check if WooCommerce is active
	if ( ! wh_powerhouse_check_woocommerce() ) {
		return;
	}

	// Include the main plugin class
	require_once WH_POWERHOUSE_PLUGIN_PATH . 'includes/class-wh-powerhouse.php';

	// Run the plugin
	$plugin = new WH_Powerhouse();
	$plugin->run();
}
add_action( 'plugins_loaded', 'run_wh_powerhouse', 20 );

/**
 * Declare compatibility with WooCommerce HPOS (High-Performance Order Storage)
 */
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );
