<?php
/**
 * Wholesale Pricing Engine (The Brain)
 *
 * @package    Wholesale_Powerhouse
 * @subpackage Wholesale_Powerhouse/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class WH_Pricing
 * Handles the dual pricing system (Fixed Price or Global Discount)
 */
class WH_Pricing {

	/**
	 * Flag to prevent infinite loops
	 *
	 * @var bool
	 */
	private static $processing = false;

	/**
	 * Initialize the pricing engine
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Register all pricing hooks
	 */
	private function init_hooks() {
		// Simple product price hooks
		add_filter( 'woocommerce_product_get_price', array( $this, 'get_wholesale_price' ), 10, 2 );
		add_filter( 'woocommerce_product_get_regular_price', array( $this, 'get_wholesale_price' ), 10, 2 );
		add_filter( 'woocommerce_product_get_sale_price', array( $this, 'get_wholesale_price' ), 10, 2 );

		// Variable product price hooks
		add_filter( 'woocommerce_product_variation_get_price', array( $this, 'get_wholesale_price' ), 10, 2 );
		add_filter( 'woocommerce_product_variation_get_regular_price', array( $this, 'get_wholesale_price' ), 10, 2 );
		add_filter( 'woocommerce_product_variation_get_sale_price', array( $this, 'get_wholesale_price' ), 10, 2 );

		// Variable product price range
		add_filter( 'woocommerce_variation_prices', array( $this, 'get_variation_prices' ), 10, 3 );

		// Price hash for caching
		add_filter( 'woocommerce_get_variation_prices_hash', array( $this, 'add_user_role_to_price_hash' ), 10, 3 );
	}

	/**
	 * Get wholesale price for a product
	 *
	 * @param float      $price   Original price
	 * @param WC_Product $product Product object
	 * @return float Modified price
	 */
	public function get_wholesale_price( $price, $product ) {
		// Prevent infinite loops
		if ( self::$processing ) {
			return $price;
		}

		// Check if user is a wholesale customer
		if ( ! WH_Roles::is_wholesale_customer() ) {
			return $price;
		}

		// Get user's wholesale role
		$user_role = WH_Roles::get_user_wholesale_role();
		if ( ! $user_role ) {
			return $price;
		}

		// Set processing flag
		self::$processing = true;

		// Get product ID (handle variations)
		$product_id = $product->get_id();

		// Check for fixed wholesale price
		$fixed_price = get_post_meta( $product_id, '_wh_price_' . $user_role, true );
		
		if ( $fixed_price !== '' && $fixed_price !== false && is_numeric( $fixed_price ) && floatval( $fixed_price ) > 0 ) {
			self::$processing = false;
			return floatval( $fixed_price );
		}

		// No fixed price, apply global discount
		$wholesale_price = $this->apply_global_discount( $price, $user_role, $product );
		
		// Reset processing flag
		self::$processing = false;

		return $wholesale_price !== false ? $wholesale_price : $price;
	}

	/**
	 * Apply global discount to price
	 *
	 * @param float      $price     Original price
	 * @param string     $user_role Wholesale role key
	 * @param WC_Product $product   Product object
	 * @return float|false Modified price or false
	 */
	private function apply_global_discount( $price, $user_role, $product ) {
		// Get settings
		$settings = wh_get_settings();
		$roles    = isset( $settings['roles'] ) ? $settings['roles'] : array();

		// Check if role has discount configured
		if ( ! isset( $roles[ $user_role ] ) || ! isset( $roles[ $user_role ]['discount'] ) ) {
			return false;
		}

		$discount_percent = floatval( $roles[ $user_role ]['discount'] );

		// If no discount or invalid price, return false
		if ( $discount_percent <= 0 || ! is_numeric( $price ) || floatval( $price ) <= 0 ) {
			return false;
		}

		// Use the current price (which could be regular or sale price)
		$base_price = floatval( $price );

		// Calculate discounted price
		$wholesale_price = $base_price - ( $base_price * ( $discount_percent / 100 ) );

		return $wholesale_price;
	}

	/**
	 * Modify variation prices for variable products
	 *
	 * @param array      $prices  Array of prices
	 * @param WC_Product $product Product object
	 * @param bool       $display Whether for display
	 * @return array Modified prices
	 */
	public function get_variation_prices( $prices, $product, $display ) {
		// Only modify for wholesale customers
		if ( ! WH_Roles::is_wholesale_customer() ) {
			return $prices;
		}

		$user_role = WH_Roles::get_user_wholesale_role();
		if ( ! $user_role ) {
			return $prices;
		}

		// Get all variations
		$variations = $product->get_available_variations();

		foreach ( $variations as $variation ) {
			$variation_id  = $variation['variation_id'];
			$variation_obj = wc_get_product( $variation_id );

			if ( ! $variation_obj ) {
				continue;
			}

			// Get wholesale price for this variation
			$original_price  = $variation_obj->get_price();
			$wholesale_price = $this->get_wholesale_price( $original_price, $variation_obj );

			// Update prices array
			if ( isset( $prices['price'][ $variation_id ] ) ) {
				$prices['price'][ $variation_id ] = $wholesale_price;
			}
			if ( isset( $prices['regular_price'][ $variation_id ] ) ) {
				$prices['regular_price'][ $variation_id ] = $wholesale_price;
			}
			if ( isset( $prices['sale_price'][ $variation_id ] ) ) {
				$prices['sale_price'][ $variation_id ] = $wholesale_price;
			}
		}

		return $prices;
	}

	/**
	 * Add user role to price hash for proper caching
	 *
	 * @param array      $hash    Price hash
	 * @param WC_Product $product Product object
	 * @param bool       $display Whether for display
	 * @return array Modified hash
	 */
	public function add_user_role_to_price_hash( $hash, $product, $display ) {
		$user_role = WH_Roles::get_user_wholesale_role();
		if ( $user_role ) {
			$hash[] = $user_role;
		}
		return $hash;
	}
}
