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
 * Class WHOLPO_Install
 * Handles plugin activation and deactivation
 */
class WHOLPO_Install {

	/**
	 * Activate the plugin
	 */
	public static function activate() {
		// Add wholesale roles
		require_once WHOLPO_PLUGIN_PATH . 'includes/class-wholpo-roles.php';
		WHOLPO_Roles::add_roles();

		// Set default settings
		self::set_default_settings();

		// Create registration page
		self::create_registration_page();

		// Flush rewrite rules
		flush_rewrite_rules();
	}

	/**
	 * Deactivate the plugin
	 */
	public static function deactivate() {
		// Remove wholesale roles
		require_once WHOLPO_PLUGIN_PATH . 'includes/class-wholpo-roles.php';
		WHOLPO_Roles::remove_roles();

		// Flush rewrite rules
		flush_rewrite_rules();
	}

	/**
	 * Set default plugin settings
	 */
	private static function set_default_settings() {
		// First, check if we need to migrate from old option name
		$old_settings = get_option( 'wholesale_powerhouse_settings' );
		$new_settings = get_option( 'wholpo_settings' );
		
		// If old settings exist and new settings don't, migrate them
		if ( $old_settings && ! $new_settings ) {
			update_option( 'wholpo_settings', $old_settings );
			return; // Migration complete, no need to set defaults
		}
		
		// If new settings already exist, don't overwrite
		if ( $new_settings ) {
			return;
		}
		
		// Set default settings only if neither old nor new settings exist
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
			'registration_page_id'    => 0, // Will be set by create_registration_page()
		);

		update_option( 'wholpo_settings', $default_settings );
	}

	/**
	 * Create wholesale registration page
	 */
	private static function create_registration_page() {
		$settings = get_option( 'wholpo_settings', array() );
		
		// Check if page is already set in settings
		$existing_page_id = isset( $settings['registration_page_id'] ) ? intval( $settings['registration_page_id'] ) : 0;
		
		// If page exists and is published, skip creation
		if ( $existing_page_id > 0 && get_post_status( $existing_page_id ) === 'publish' ) {
			return;
		}

		// Check if a page with this title already exists
		$page_title = __( 'Wholesale Registration Form', 'wholesale-powerhouse' );
		$slug       = sanitize_title( $page_title );
		$page_id   = 0;

		$existing_by_path = get_page_by_path( $slug, OBJECT, 'page' );
		if ( $existing_by_path && 'publish' === $existing_by_path->post_status ) {
			$page_id = $existing_by_path->ID;
		} else {
			$page_query = new WP_Query(
				array(
					'post_type'      => 'page',
					'post_status'    => array( 'publish', 'pending', 'draft', 'future', 'private' ),
					'posts_per_page' => -1,
					'fields'         => 'ids',
					's'              => $page_title,
					'no_found_rows'  => true,
				)
			);

			if ( $page_query->have_posts() ) {
				foreach ( $page_query->posts as $maybe_page_id ) {
					if ( strtolower( $page_title ) === strtolower( get_the_title( $maybe_page_id ) ) ) {
						$page_id = $maybe_page_id;
						break;
					}
				}
			}

			wp_reset_postdata();
		}

		if ( $page_id ) {
			$settings['registration_page_id'] = $page_id;
			update_option( 'wholpo_settings', $settings );
			return;
		}

		// Create new page
		$page_content = '[wholpo_registration_form]';
		$page_data = array(
			'post_title'     => $page_title,
			'post_content'   => $page_content,
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'post_author'    => 1,
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		);

		$page_id = wp_insert_post( $page_data );

		if ( $page_id && ! is_wp_error( $page_id ) ) {
			// Update settings with new page ID
			$settings['registration_page_id'] = $page_id;
			update_option( 'wholpo_settings', $settings );
		}
	}
}
