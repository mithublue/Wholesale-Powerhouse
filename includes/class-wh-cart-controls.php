<?php
/**
 * Cart Controls Module
 *
 * @package    Wholesale_Powerhouse
 * @subpackage Wholesale_Powerhouse/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class WH_Cart_Controls
 * Handles minimum order value and coupon restrictions
 */
class WH_Cart_Controls {

	/**
	 * Initialize cart controls
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Register hooks
	 */
	private function init_hooks() {
		// Minimum order value
		add_action( 'woocommerce_check_cart_items', array( $this, 'check_minimum_order_value' ) );

		// Disable coupons for wholesale customers
		add_filter( 'woocommerce_coupons_enabled', array( $this, 'disable_coupons_for_wholesale' ) );
		add_filter( 'woocommerce_coupon_is_valid', array( $this, 'validate_coupon_for_wholesale' ), 10, 2 );
	}

	/**
	 * Check minimum order value for wholesale customers
	 */
	public function check_minimum_order_value() {
		// Only for wholesale customers
		if ( ! WH_Roles::is_wholesale_customer() ) {
			return;
		}

		// Get minimum cart value setting
		$settings       = wh_get_settings();
		$min_cart_value = isset( $settings['min_cart_value'] ) ? floatval( $settings['min_cart_value'] ) : 0;

		// If no minimum is set, allow
		if ( $min_cart_value <= 0 ) {
			return;
		}

		// Get cart subtotal
		$cart_total = WC()->cart->get_subtotal();

		// Check if cart total meets minimum
		if ( $cart_total < $min_cart_value ) {
			wc_add_notice(
				sprintf(
					/* translators: 1: minimum order value, 2: cart total. */
					__( 'Wholesale customers must have a minimum order value of %1$s. Your current cart total is %2$s.', 'wholesale-powerhouse' ),
					wc_price( $min_cart_value ),
					wc_price( $cart_total )
				),
				'error'
			);
		}
	}

	/**
	 * Disable coupons for wholesale customers if setting is enabled
	 *
	 * @param bool $enabled Whether coupons are enabled
	 * @return bool
	 */
	public function disable_coupons_for_wholesale( $enabled ) {
		// Only affect wholesale customers
		if ( ! WH_Roles::is_wholesale_customer() ) {
			return $enabled;
		}

		// Check if coupons should be disabled
		$settings        = wh_get_settings();
		$disable_coupons = isset( $settings['disable_coupons'] ) ? $settings['disable_coupons'] : false;

		if ( $disable_coupons ) {
			return false;
		}

		return $enabled;
	}

	/**
	 * Validate coupon for wholesale customers
	 *
	 * @param bool      $valid  Whether coupon is valid
	 * @param WC_Coupon $coupon Coupon object
	 * @return bool
	 */
	public function validate_coupon_for_wholesale( $valid, $coupon ) {
		// Only affect wholesale customers
		if ( ! WH_Roles::is_wholesale_customer() ) {
			return $valid;
		}

		// Check if coupons should be disabled
		$settings        = wh_get_settings();
		$disable_coupons = isset( $settings['disable_coupons'] ) ? $settings['disable_coupons'] : false;

		if ( $disable_coupons ) {
			throw new Exception( esc_html__( 'Coupons are not available for wholesale customers.', 'wholesale-powerhouse' ) );
		}

		return $valid;
	}
}
