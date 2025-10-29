<?php
/**
 * Wholesale Roles Management
 *
 * @package    Wholesale_Powerhouse
 * @subpackage Wholesale_Powerhouse/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class WH_Roles
 * Manages wholesale user roles
 */
class WH_Roles {

	/**
	 * Get all wholesale role keys
	 *
	 * @return array
	 */
	public static function get_wholesale_role_keys() {
		return array( 'wh_bronze', 'wh_silver', 'wh_gold' );
	}

	/**
	 * Get wholesale roles with labels from settings
	 *
	 * @return array
	 */
	public static function get_wholesale_roles() {
		$settings = get_option( 'wholesale_powerhouse_settings', array() );
		$roles    = isset( $settings['roles'] ) ? $settings['roles'] : array();

		return $roles;
	}

	/**
	 * Add wholesale roles to WordPress
	 */
	public static function add_roles() {
		$customer_caps = get_role( 'customer' )->capabilities;

		// Add Bronze Wholesale role
		add_role(
			'wh_bronze',
			__( 'Bronze Wholesale', 'wholesale-powerhouse' ),
			$customer_caps
		);

		// Add Silver Wholesale role
		add_role(
			'wh_silver',
			__( 'Silver Wholesale', 'wholesale-powerhouse' ),
			$customer_caps
		);

		// Add Gold Wholesale role
		add_role(
			'wh_gold',
			__( 'Gold Wholesale', 'wholesale-powerhouse' ),
			$customer_caps
		);
	}

	/**
	 * Remove wholesale roles from WordPress
	 */
	public static function remove_roles() {
		remove_role( 'wh_bronze' );
		remove_role( 'wh_silver' );
		remove_role( 'wh_gold' );
	}

	/**
	 * Check if user has a wholesale role
	 *
	 * @param int $user_id User ID (0 for current user)
	 * @return bool
	 */
	public static function is_wholesale_customer( $user_id = 0 ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return false;
		}

		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return false;
		}

		$wholesale_roles = self::get_wholesale_role_keys();
		$user_roles      = (array) $user->roles;

		return ! empty( array_intersect( $wholesale_roles, $user_roles ) );
	}

	/**
	 * Get user's wholesale role
	 *
	 * @param int $user_id User ID (0 for current user)
	 * @return string|false Role key or false if not wholesale customer
	 */
	public static function get_user_wholesale_role( $user_id = 0 ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return false;
		}

		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return false;
		}

		$wholesale_roles = self::get_wholesale_role_keys();
		$user_roles      = (array) $user->roles;

		foreach ( $wholesale_roles as $role ) {
			if ( in_array( $role, $user_roles ) ) {
				return $role;
			}
		}

		return false;
	}
}
