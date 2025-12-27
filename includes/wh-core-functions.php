<?php
/**
 * Core helper functions
 *
 * @package    Wholesale_Powerhouse
 * @subpackage Wholesale_Powerhouse/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Get plugin settings
 *
 * @return array
 */
function wholpo_get_settings() {
	$defaults = array(
		'roles'                 => array(),
		'private_store'         => false,
		'min_cart_value'        => 150.00,
		'disable_coupons'       => false,
		'registration_approval' => false,
	);

	$settings = get_option( 'wholpo_settings', $defaults );

	return wp_parse_args( $settings, $defaults );
}

/**
 * Get a specific setting value
 *
 * @param string $key     Setting key
 * @param mixed  $default Default value
 * @return mixed
 */
function wholpo_get_setting( $key, $default = null ) {
	$settings = wholpo_get_settings();

	return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
}

/**
 * Update plugin settings
 *
 * @param array $new_settings New settings to merge
 * @return bool
 */
function wholpo_update_settings( $new_settings ) {
	$current_settings = wholpo_get_settings();
	$updated_settings = array_merge( $current_settings, $new_settings );

	return update_option( 'wholpo_settings', $updated_settings );
}

/**
 * Check if current user is a wholesale customer
 *
 * @return bool
 */
function wholpo_is_wholesale_customer() {
	return WHOLPO_Roles::is_wholesale_customer();
}

/**
 * Get current user's wholesale role
 *
 * @return string|false
 */
function wholpo_get_user_wholesale_role() {
	return WHOLPO_Roles::get_user_wholesale_role();
}

/**
 * Get wholesale price for a product
 *
 * @param WC_Product $product Product object
 * @param string     $role    Wholesale role key
 * @return float|false
 */
function wholpo_get_product_wholesale_price( $product, $role = '' ) {
	if ( ! $role ) {
		$role = wholpo_get_user_wholesale_role();
	}

	if ( ! $role ) {
		return false;
	}

	$product_id = $product->get_id();

	// Check for fixed wholesale price
	$fixed_price = get_post_meta( $product_id, '_wh_price_' . $role, true );
	
	if ( $fixed_price !== '' && $fixed_price !== false ) {
		return floatval( $fixed_price );
	}

	// Get global discount for role
	$settings = wholpo_get_settings();
	$roles    = isset( $settings['roles'] ) ? $settings['roles'] : array();
	
	if ( ! isset( $roles[ $role ] ) || ! isset( $roles[ $role ]['discount'] ) ) {
		return false;
	}

	$discount_percent = floatval( $roles[ $role ]['discount'] );
	$regular_price    = floatval( $product->get_regular_price() );

	if ( $regular_price <= 0 ) {
		return false;
	}

	// Calculate discounted price
	$wholesale_price = $regular_price - ( $regular_price * ( $discount_percent / 100 ) );

	return $wholesale_price;
}

/**
 * Format price for display
 *
 * @param float $price Price value
 * @return string
 */
function wholpo_format_price( $price ) {
	return wc_price( $price );
}

/**
 * Log debug message (if WP_DEBUG is enabled)
 *
 * @param string $message Message to log
 */
function wholpo_log( $message ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && function_exists( 'wc_get_logger' ) ) {
		$logger = wc_get_logger();
		$logger->debug( $message, array( 'source' => 'wholesale-powerhouse' ) );
	}
}
