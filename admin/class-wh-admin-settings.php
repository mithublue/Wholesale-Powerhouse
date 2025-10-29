<?php
/**
 * Admin Settings Page
 *
 * @package    Wholesale_Powerhouse
 * @subpackage Wholesale_Powerhouse/admin
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class WH_Admin_Settings
 * Adds WooCommerce settings tab for Wholesale configuration
 */
class WH_Admin_Settings {

	/**
	 * Initialize settings
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Register hooks
	 */
	private function init_hooks() {
		// Add settings tab
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 50 );
		add_action( 'woocommerce_settings_tabs_wholesale', array( $this, 'output_settings' ) );
		add_action( 'woocommerce_update_options_wholesale', array( $this, 'save_settings' ) );
	}

	/**
	 * Add Wholesale settings tab to WooCommerce
	 *
	 * @param array $tabs Existing tabs
	 * @return array Modified tabs
	 */
	public function add_settings_tab( $tabs ) {
		$tabs['wholesale'] = __( 'Wholesale', 'wholesale-powerhouse' );
		return $tabs;
	}

	/**
	 * Output settings page content
	 */
	public function output_settings() {
		global $current_section;
		$settings = $this->get_settings( $current_section );
		WC_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Save settings
	 */
	public function save_settings() {
		global $current_section;

		// Get settings
		$settings = wh_get_settings();

		// Update role settings
		$roles = array();
		foreach ( array( 'wh_bronze', 'wh_silver', 'wh_gold' ) as $role_key ) {
			$label    = isset( $_POST[ 'wh_role_label_' . $role_key ] ) ? sanitize_text_field( $_POST[ 'wh_role_label_' . $role_key ] ) : '';
			$discount = isset( $_POST[ 'wh_role_discount_' . $role_key ] ) ? floatval( $_POST[ 'wh_role_discount_' . $role_key ] ) : 0;
			
			$roles[ $role_key ] = array(
				'label'    => $label,
				'discount' => $discount,
			);
		}
		$settings['roles'] = $roles;

		// Update other settings
		$settings['private_store']         = isset( $_POST['wh_private_store'] ) ? true : false;
		$settings['min_cart_value']        = isset( $_POST['wh_min_cart_value'] ) ? floatval( $_POST['wh_min_cart_value'] ) : 0;
		$settings['disable_coupons']       = isset( $_POST['wh_disable_coupons'] ) ? true : false;
		$settings['registration_approval'] = isset( $_POST['wh_registration_approval'] ) ? true : false;
		$settings['registration_page_id']  = isset( $_POST['wh_registration_page_id'] ) ? intval( $_POST['wh_registration_page_id'] ) : 0;

		// Save settings
		update_option( 'wholesale_powerhouse_settings', $settings );

		// Show success message
		WC_Admin_Settings::add_message( __( 'Wholesale settings saved successfully.', 'wholesale-powerhouse' ) );
	}

	/**
	 * Get settings array
	 *
	 * @param string $current_section Current section
	 * @return array Settings
	 */
	public function get_settings( $current_section = '' ) {
		$current_settings = wh_get_settings();
		$roles            = isset( $current_settings['roles'] ) ? $current_settings['roles'] : array();

		$settings = array(
			array(
				'title' => __( 'Wholesale Powerhouse Settings', 'wholesale-powerhouse' ),
				'type'  => 'title',
				'desc'  => __( 'Configure wholesale pricing and user roles.', 'wholesale-powerhouse' ),
				'id'    => 'wh_general_options',
			),

			// Role Management Section
			array(
				'title' => __( 'Wholesale Roles', 'wholesale-powerhouse' ),
				'type'  => 'title',
				'desc'  => __( 'Configure wholesale roles and their global discount percentages.', 'wholesale-powerhouse' ),
				'id'    => 'wh_role_options',
			),
		);

		// Add role fields
		foreach ( array( 'wh_bronze', 'wh_silver', 'wh_gold' ) as $role_key ) {
			$role_label    = isset( $roles[ $role_key ]['label'] ) ? $roles[ $role_key ]['label'] : '';
			$role_discount = isset( $roles[ $role_key ]['discount'] ) ? $roles[ $role_key ]['discount'] : 0;

			$settings[] = array(
				'title'    => ucfirst( str_replace( 'wh_', '', $role_key ) ) . ' ' . __( 'Role', 'wholesale-powerhouse' ),
				'desc'     => __( 'Label', 'wholesale-powerhouse' ),
				'id'       => 'wh_role_label_' . $role_key,
				'type'     => 'text',
				'default'  => $role_label,
				'value'    => $role_label,
			);

			$settings[] = array(
				'desc'     => __( 'Global Discount (%)', 'wholesale-powerhouse' ),
				'id'       => 'wh_role_discount_' . $role_key,
				'type'     => 'number',
				'default'  => $role_discount,
				'value'    => $role_discount,
				'custom_attributes' => array(
					'min'  => '0',
					'max'  => '100',
					'step' => '0.01',
				),
			);
		}

		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'wh_role_options',
		);

		// Store Settings
		$settings[] = array(
			'title' => __( 'Store Settings', 'wholesale-powerhouse' ),
			'type'  => 'title',
			'desc'  => __( 'Configure store access and restrictions.', 'wholesale-powerhouse' ),
			'id'    => 'wh_store_options',
		);

		$settings[] = array(
			'title'   => __( 'Private Store', 'wholesale-powerhouse' ),
			'desc'    => __( 'Hide prices and purchase buttons from logged-out users', 'wholesale-powerhouse' ),
			'id'      => 'wh_private_store',
			'type'    => 'checkbox',
			'default' => 'no',
			'value'   => isset( $current_settings['private_store'] ) && $current_settings['private_store'] ? 'yes' : 'no',
		);

		$settings[] = array(
			'title'             => __( 'Minimum Cart Value', 'wholesale-powerhouse' ),
			'desc'              => __( 'Minimum cart value required for wholesale customers', 'wholesale-powerhouse' ),
			'id'                => 'wh_min_cart_value',
			'type'              => 'number',
			'default'           => '150',
			'value'             => isset( $current_settings['min_cart_value'] ) ? $current_settings['min_cart_value'] : 150,
			'custom_attributes' => array(
				'min'  => '0',
				'step' => '0.01',
			),
		);

		$settings[] = array(
			'title'   => __( 'Disable Coupons', 'wholesale-powerhouse' ),
			'desc'    => __( 'Disable coupon usage for wholesale customers', 'wholesale-powerhouse' ),
			'id'      => 'wh_disable_coupons',
			'type'    => 'checkbox',
			'default' => 'no',
			'value'   => isset( $current_settings['disable_coupons'] ) && $current_settings['disable_coupons'] ? 'yes' : 'no',
		);

		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'wh_store_options',
		);

		// Registration Settings
		$settings[] = array(
			'title' => __( 'Registration Settings', 'wholesale-powerhouse' ),
			'type'  => 'title',
			'desc'  => __( 'Configure wholesale customer registration.', 'wholesale-powerhouse' ),
			'id'    => 'wh_registration_options',
		);

		$settings[] = array(
			'title'   => __( 'Registration Approval', 'wholesale-powerhouse' ),
			'desc'    => __( 'Require admin approval for new wholesale registrations', 'wholesale-powerhouse' ),
			'id'      => 'wh_registration_approval',
			'type'    => 'checkbox',
			'default' => 'no',
			'value'   => isset( $current_settings['registration_approval'] ) && $current_settings['registration_approval'] ? 'yes' : 'no',
		);

		// Get all pages for dropdown
		$pages = get_pages( array( 'sort_column' => 'post_title' ) );
		$page_options = array( '' => __( 'Select a page...', 'wholesale-powerhouse' ) );
		foreach ( $pages as $page ) {
			$page_options[ $page->ID ] = $page->post_title;
		}

		$settings[] = array(
			'title'    => __( 'Registration Page', 'wholesale-powerhouse' ),
			'desc'     => __( 'Select the page that contains the [wholesale_registration_form] shortcode', 'wholesale-powerhouse' ),
			'id'       => 'wh_registration_page_id',
			'type'     => 'select',
			'options'  => $page_options,
			'default'  => '',
			'value'    => isset( $current_settings['registration_page_id'] ) ? $current_settings['registration_page_id'] : '',
			'class'    => 'wc-enhanced-select',
		);

		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'wh_registration_options',
		);

		return $settings;
	}
}
