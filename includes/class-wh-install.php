<?php
/**
 * Fired during plugin activation and deactivation
 *
 * @package    Wholesale_Powerhouse
 * @subpackage Wholesale_Powerhouse/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class WH_Install
 * Handles plugin activation and deactivation
 */
class WH_Install {

	/**
	 * Activate the plugin
	 */
	public static function activate() {
		// Add wholesale roles
		require_once WH_POWERHOUSE_PLUGIN_PATH . 'includes/class-wh-roles.php';
		WH_Roles::add_roles();

		// Set default settings
		self::set_default_settings();

		// Flush rewrite rules
		flush_rewrite_rules();
	}

	/**
	 * Deactivate the plugin
	 */
	public static function deactivate() {
		// Remove wholesale roles
		require_once WH_POWERHOUSE_PLUGIN_PATH . 'includes/class-wh-roles.php';
		WH_Roles::remove_roles();

		// Flush rewrite rules
		flush_rewrite_rules();
	}

	/**
	 * Set default plugin settings
	 */
	private static function set_default_settings() {
		$default_settings = array(
			'roles'                   => array(
				'wh_bronze' => array(
					'label'    => __( 'Bronze Wholesale', 'wholesale-powerhouse' ),
					'discount' => 10,
				),
				'wh_silver' => array(
					'label'    => __( 'Silver Wholesale', 'wholesale-powerhouse' ),
					'discount' => 20,
				),
				'wh_gold'   => array(
					'label'    => __( 'Gold Wholesale', 'wholesale-powerhouse' ),
					'discount' => 30,
				),
			),
			'private_store'           => false,
			'min_cart_value'          => 150.00,
			'disable_coupons'         => false,
			'registration_approval'   => false,
		);

		// Only set if not already exists
		if ( ! get_option( 'wholesale_powerhouse_settings' ) ) {
			update_option( 'wholesale_powerhouse_settings', $default_settings );
		}
	}
}
