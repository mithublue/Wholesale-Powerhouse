<?php
/**
 * Product Visibility & Access Control
 *
 * @package    Wholesale_Powerhouse
 * @subpackage Wholesale_Powerhouse/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * class WHOLPO_Visibility
 * Manages private store mode and wholesale-only products
 */
class WHOLPO_Visibility {

	/**
	 * Initialize visibility controls
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Register hooks
	 */
	private function init_hooks() {
		// Private store mode
		add_filter( 'woocommerce_is_purchasable', array( $this, 'hide_purchase_for_guests' ), 10, 2 );
		add_filter( 'woocommerce_get_price_html', array( $this, 'hide_price_for_guests' ), 10, 2 );

		// Wholesale-only products
		add_action( 'pre_get_posts', array( $this, 'hide_wholesale_only_products' ) );
		add_filter( 'woocommerce_product_is_visible', array( $this, 'filter_wholesale_only_visibility' ), 10, 2 );
	}

	/**
	 * Hide purchase ability for guests if private store is enabled
	 *
	 * @param bool       $purchasable Whether product is purchasable
	 * @param WC_Product $product     Product object
	 * @return bool
	 */
	public function hide_purchase_for_guests( $purchasable, $product ) {
		$settings = wholpo_get_settings();
		$private_store = isset( $settings['private_store'] ) ? $settings['private_store'] : false;

		// If private store is enabled and user is not logged in
		if ( $private_store && ! is_user_logged_in() ) {
			return false;
		}

		return $purchasable;
	}

	/**
	 * Hide price for guests if private store is enabled
	 *
	 * @param string     $price_html Price HTML
	 * @param WC_Product $product    Product object
	 * @return string
	 */
	public function hide_price_for_guests( $price_html, $product ) {
		$settings = wholpo_get_settings();
		$private_store = isset( $settings['private_store'] ) ? $settings['private_store'] : false;

		// If private store is enabled and user is not logged in
		if ( $private_store && ! is_user_logged_in() ) {
			return '<span class="wh-login-required">' . 
				   esc_html__( 'Please login to see prices', 'wholesale-powerhouse' ) . 
				   '</span>';
		}

		return $price_html;
	}

	/**
	 * Hide wholesale-only products from retail customers
	 *
	 * @param WP_Query $query Query object
	 */
	public function hide_wholesale_only_products( $query ) {
		// Only on product queries
		if ( ! $query->is_main_query() || is_admin() ) {
			return;
		}

		// Check if this is a product query
		$post_type = $query->get( 'post_type' );
		if ( $post_type !== 'product' && ! is_shop() && ! is_product_taxonomy() ) {
			return;
		}

		// If user is wholesale customer, show all products
		if ( WHOLPO_Roles::is_wholesale_customer() ) {
			return;
		}

		// Hide products marked as wholesale-only
		$meta_query = $query->get( 'meta_query' );
		if ( ! is_array( $meta_query ) ) {
			$meta_query = array();
		}

		$meta_query[] = array(
			'relation' => 'OR',
			array(
				'key'     => '_wh_hide_from_retail',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_wh_hide_from_retail',
				'value'   => '1',
				'compare' => '!=',
			),
		);

		$query->set( 'meta_query', $meta_query );
	}

	/**
	 * Filter product visibility for wholesale-only products
	 *
	 * @param bool    $visible Whether product is visible
	 * @param int     $product_id Product ID
	 * @return bool
	 */
	public function filter_wholesale_only_visibility( $visible, $product_id ) {
		// If user is wholesale customer, show product
		if ( WHOLPO_Roles::is_wholesale_customer() ) {
			return $visible;
		}

		// Check if product is wholesale-only
		$hide_from_retail = get_post_meta( $product_id, '_wh_hide_from_retail', true );
		
		if ( $hide_from_retail === '1' || $hide_from_retail === 1 || $hide_from_retail === true ) {
			return false;
		}

		return $visible;
	}
}
