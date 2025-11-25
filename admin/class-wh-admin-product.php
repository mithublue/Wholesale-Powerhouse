<?php
/**
 * Admin Product Meta Fields
 *
 * @package    Wholesale_Powerhouse
 * @subpackage Wholesale_Powerhouse/admin
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class WH_Admin_Product
 * Adds wholesale pricing fields to product edit page
 */
class WH_Admin_Product {

	/**
	 * Initialize product admin
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Register hooks
	 */
	private function init_hooks() {
		// Add wholesale tab to product data
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_wholesale_product_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( $this, 'add_wholesale_product_fields' ) );

		// Save product meta
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_wholesale_product_fields' ) );

		// Add wholesale column to products list
		add_filter( 'manage_product_posts_columns', array( $this, 'add_wholesale_column' ) );
		add_action( 'manage_product_posts_custom_column', array( $this, 'render_wholesale_column' ), 10, 2 );
	}

	/**
	 * Add Wholesale tab to product data metabox
	 *
	 * @param array $tabs Existing tabs
	 * @return array Modified tabs
	 */
	public function add_wholesale_product_tab( $tabs ) {
		$tabs['wholesale'] = array(
			'label'    => __( 'Wholesale', 'wholesale-powerhouse' ),
			'target'   => 'wholesale_product_data',
			'class'    => array( 'show_if_simple', 'show_if_variable' ),
			'priority' => 25,
		);

		return $tabs;
	}

	/**
	 * Add wholesale fields to product data panel
	 */
	public function add_wholesale_product_fields() {
		global $post;

		echo '<div id="wholesale_product_data" class="panel woocommerce_options_panel">';

		// Nonce for wholesale product data
		wp_nonce_field( 'wh_save_wholesale_product', '_wh_wholesale_nonce' );

		echo '<div class="options_group">';

		// Get wholesale roles
		$settings = wh_get_settings();
		$roles    = isset( $settings['roles'] ) ? $settings['roles'] : array();

		echo '<h3 style="padding: 10px;">' . esc_html__( 'Fixed Wholesale Prices', 'wholesale-powerhouse' ) . '</h3>';
		echo '<p style="padding: 0 10px;">' . esc_html__( 'Set fixed prices for each wholesale role. Leave empty to use global discount.', 'wholesale-powerhouse' ) . '</p>';

		// Add price fields for each role
		foreach ( $roles as $role_key => $role_data ) {
			$role_label = isset( $role_data['label'] ) ? $role_data['label'] : ucfirst( str_replace( 'wh_', '', $role_key ) );
			
			/* translators: %s: Wholesale role label. */
			$fixed_price_desc = sprintf( __( 'Fixed price for %s customers', 'wholesale-powerhouse' ), $role_label );
			woocommerce_wp_text_input(
				array(
					'id'          => '_wh_price_' . $role_key,
					'label'       => $role_label . ' ' . __( 'Price', 'wholesale-powerhouse' ),
					'placeholder' => __( 'Leave empty for global discount', 'wholesale-powerhouse' ),
					'desc_tip'    => true,
					'description' => $fixed_price_desc,
					'type'        => 'number',
					'custom_attributes' => array(
						'step' => '0.01',
						'min'  => '0',
					),
				)
			);
		}

		echo '</div>';

		echo '<div class="options_group">';

		echo '<h3 style="padding: 10px;">' . esc_html__( 'Tiered Pricing Lite', 'wholesale-powerhouse' ) . '</h3>';

		// Get tiered pricing data
		$tier_lite = get_post_meta( $post->ID, '_wh_tier_lite', true );
		if ( ! is_array( $tier_lite ) ) {
			$tier_lite = array( 'min_qty' => '', 'discount_percent' => '' );
		} else {
			// Sanitize array values from database
			$tier_lite = array_map( 'sanitize_text_field', $tier_lite );
		}

		woocommerce_wp_text_input(
			array(
				'id'          => '_wh_tier_min_qty',
				'label'       => __( 'Minimum Quantity', 'wholesale-powerhouse' ),
				'placeholder' => '10',
				'desc_tip'    => true,
				'description' => __( 'Minimum quantity to trigger tier discount', 'wholesale-powerhouse' ),
				'type'        => 'number',
				'value'       => isset( $tier_lite['min_qty'] ) ? $tier_lite['min_qty'] : '',
				'custom_attributes' => array(
					'step' => '1',
					'min'  => '1',
				),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'          => '_wh_tier_discount_percent',
				'label'       => __( 'Discount Percentage', 'wholesale-powerhouse' ),
				'placeholder' => '15',
				'desc_tip'    => true,
				'description' => __( 'Additional discount % when minimum quantity is reached', 'wholesale-powerhouse' ),
				'type'        => 'number',
				'value'       => isset( $tier_lite['discount_percent'] ) ? $tier_lite['discount_percent'] : '',
				'custom_attributes' => array(
					'step' => '0.01',
					'min'  => '0',
					'max'  => '100',
				),
			)
		);

		echo '</div>';

		echo '<div class="options_group">';

		echo '<h3 style="padding: 10px;">' . esc_html__( 'Visibility', 'wholesale-powerhouse' ) . '</h3>';

		woocommerce_wp_checkbox(
			array(
				'id'          => '_wh_hide_from_retail',
				'label'       => __( 'Hide from Retail Customers', 'wholesale-powerhouse' ),
				'description' => __( 'Make this product visible only to wholesale customers', 'wholesale-powerhouse' ),
			)
		);

		echo '</div>';

		echo '</div>';
	}

	/**
	 * Save wholesale product fields
	 *
	 * @param int $post_id Product ID
	 */
	public function save_wholesale_product_fields( $post_id ) {
		if ( ! isset( $_POST['_wh_wholesale_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wh_wholesale_nonce'] ) ), 'wh_save_wholesale_product' ) ) {
			return;
		}


		// Get wholesale roles
		$settings = wh_get_settings();
		$roles    = isset( $settings['roles'] ) ? $settings['roles'] : array();

		// Save fixed prices for each role
		foreach ( $roles as $role_key => $role_data ) {
			$field_key = '_wh_price_' . $role_key;
			
			if ( isset( $_POST[ $field_key ] ) ) {
				$value_raw          = wp_unslash( $_POST[ $field_key ] );
				$value_raw_sanitized = sanitize_text_field( $value_raw );
				if ( '' !== trim( $value_raw_sanitized ) ) {
					$value = wc_format_decimal( $value_raw_sanitized );
					update_post_meta( $post_id, $field_key, $value );
				} else {
					delete_post_meta( $post_id, $field_key );
				}
			} else {
				delete_post_meta( $post_id, $field_key );
			}
		}

		// Save tiered pricing
		$tier_lite = array(
			'min_qty'          => isset( $_POST['_wh_tier_min_qty'] ) ? absint( wp_unslash( $_POST['_wh_tier_min_qty'] ) ) : 0,
			'discount_percent' => isset( $_POST['_wh_tier_discount_percent'] ) ? floatval( wp_unslash( $_POST['_wh_tier_discount_percent'] ) ) : 0,
		);

		// Only save if both values are set
		if ( $tier_lite['min_qty'] > 0 && $tier_lite['discount_percent'] > 0 ) {
			update_post_meta( $post_id, '_wh_tier_lite', $tier_lite );
		} else {
			delete_post_meta( $post_id, '_wh_tier_lite' );
		}

		// Save visibility setting
		$hide_from_retail = isset( $_POST['_wh_hide_from_retail'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_wh_hide_from_retail', $hide_from_retail === 'yes' ? '1' : '0' );
	}

	/**
	 * Add wholesale column to products list
	 *
	 * @param array $columns Existing columns
	 * @return array Modified columns
	 */
	public function add_wholesale_column( $columns ) {
		$new_columns = array();
		
		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;
			
			// Add wholesale column after price
			if ( $key === 'price' ) {
				$new_columns['wholesale'] = __( 'Wholesale', 'wholesale-powerhouse' );
			}
		}
		
		return $new_columns;
	}

	/**
	 * Render wholesale column content
	 *
	 * @param string $column  Column name
	 * @param int    $post_id Post ID
	 */
	public function render_wholesale_column( $column, $post_id ) {
		if ( $column !== 'wholesale' ) {
			return;
		}

		$product = wc_get_product( $post_id );
		if ( ! $product ) {
			return;
		}

		$output = array();

		// Check for fixed prices
		$settings = wh_get_settings();
		$roles    = isset( $settings['roles'] ) ? $settings['roles'] : array();

		foreach ( $roles as $role_key => $role_data ) {
			$fixed_price = sanitize_text_field( get_post_meta( $post_id, '_wh_price_' . $role_key, true ) );
			if ( '' !== $fixed_price && false !== $fixed_price ) {
				$role_label = isset( $role_data['label'] ) ? $role_data['label'] : ucfirst( str_replace( 'wh_', '', $role_key ) );
				$output[]   = sprintf( '<strong>%1$s:</strong> %2$s', esc_html( $role_label ), wc_price( $fixed_price ) );
			}
		}

		// Check for wholesale-only flag
		$hide_from_retail = sanitize_text_field( get_post_meta( $post_id, '_wh_hide_from_retail', true ) );
		if ( $hide_from_retail === '1' ) {
			$output[] = '<span class="dashicons dashicons-lock" title="' . esc_attr__( 'Wholesale Only', 'wholesale-powerhouse' ) . '"></span>';
		}

		echo ! empty( $output ) ? wp_kses_post( implode( '<br>', $output ) ) : esc_html__( '—', 'wholesale-powerhouse' );
	}
}
