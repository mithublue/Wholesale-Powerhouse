<?php
/**
 * Tiered Pricing Lite Module
 *
 * @package    Wholesale_Powerhouse
 * @subpackage Wholesale_Powerhouse/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class WH_Tiered_Pricing
 * Handles quantity-based tiered pricing for wholesale customers
 */
class WH_Tiered_Pricing {

	/**
	 * Initialize tiered pricing
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Register hooks
	 */
	private function init_hooks() {
		// Apply tiered pricing in cart - use higher priority to run after wholesale pricing
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'apply_tiered_pricing_to_cart' ), 100 );

		// Display tiered pricing info on product pages (below the add to cart form)
		add_action( 'woocommerce_after_add_to_cart_form', array( $this, 'display_tiered_pricing_table' ), 5 );
	}

	/**
	 * Apply tiered pricing to cart items
	 *
	 * @param WC_Cart $cart Cart object
	 */
	public function apply_tiered_pricing_to_cart( $cart ) {
		// Only for wholesale customers
		if ( ! WH_Roles::is_wholesale_customer() ) {
			return;
		}

		// Avoid infinite loops
		if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 ) {
			return;
		}

		// Loop through cart items
		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			$product    = $cart_item['data'];
			$product_id = $product->get_id();
			
			// For variations, check parent product as well
			if ( $product->is_type( 'variation' ) ) {
				$parent_id = $product->get_parent_id();
				$tier_rules = get_post_meta( $parent_id, '_wh_tier_lite', true );
				
				// If no tier rules on parent, check variation itself
				if ( empty( $tier_rules ) || ! is_array( $tier_rules ) ) {
					$tier_rules = get_post_meta( $product_id, '_wh_tier_lite', true );
				}
			} else {
				$tier_rules = get_post_meta( $product_id, '_wh_tier_lite', true );
			}

			if ( empty( $tier_rules ) || ! is_array( $tier_rules ) ) {
				continue;
			}

			$quantity = $cart_item['quantity'];

			// Check if minimum quantity is met
			$min_qty          = isset( $tier_rules['min_qty'] ) ? intval( $tier_rules['min_qty'] ) : 0;
			$discount_percent = isset( $tier_rules['discount_percent'] ) ? floatval( $tier_rules['discount_percent'] ) : 0;

			if ( $min_qty <= 0 || $discount_percent <= 0 ) {
				continue;
			}

			// Apply tier discount if quantity threshold is met
			if ( $quantity >= $min_qty ) {
				// Get the current price (which should already be the wholesale price)
				$current_price = floatval( $product->get_price() );
				
				if ( $current_price > 0 ) {
					// Apply additional tier discount on top of wholesale price
					$tier_discount = $current_price * ( $discount_percent / 100 );
					$new_price     = $current_price - $tier_discount;

					// Set new price
					$product->set_price( $new_price );
				}
			}
		}
	}

	/**
	 * Display tiered pricing table on product pages
	 */
	public function display_tiered_pricing_table() {
		global $product;

		// Only for wholesale customers
		if ( ! WH_Roles::is_wholesale_customer() ) {
			return;
		}

		if ( ! $product ) {
			return;
		}

		$product_id = $product->get_id();
		$tier_rules = get_post_meta( $product_id, '_wh_tier_lite', true );

		if ( empty( $tier_rules ) || ! is_array( $tier_rules ) ) {
			return;
		}

		$min_qty          = isset( $tier_rules['min_qty'] ) ? intval( $tier_rules['min_qty'] ) : 0;
		$discount_percent = isset( $tier_rules['discount_percent'] ) ? floatval( $tier_rules['discount_percent'] ) : 0;

		if ( $min_qty <= 0 || $discount_percent <= 0 ) {
			return;
		}

		// Calculate tiered price
		$current_price = $product->get_price();
		$tiered_price  = $current_price - ( $current_price * ( $discount_percent / 100 ) );

		?>
		<div class="wh-tiered-pricing-info">
			<h4><?php esc_html_e( 'Quantity Discount Available', 'wholesale-powerhouse' ); ?></h4>
			<table class="wh-tier-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Quantity', 'wholesale-powerhouse' ); ?></th>
						<th><?php esc_html_e( 'Discount', 'wholesale-powerhouse' ); ?></th>
						<th><?php esc_html_e( 'Price per Unit', 'wholesale-powerhouse' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php echo esc_html( $min_qty ); ?>+</td>
						<td><?php echo esc_html( $discount_percent ); ?>%</td>
						<td><?php echo wp_kses_post( wc_price( $tiered_price ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}
}
