<?php
/**
 * The core plugin class (The Conductor)
 *
 * @package    Wholesale_Powerhouse
 * @subpackage Wholesale_Powerhouse/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * class WHOLPO_Powerhouse
 * The main plugin orchestrator
 */
class WHOLPO_Powerhouse {

	/**
	 * Plugin modules
	 */
	protected $roles;
	protected $pricing;
	protected $tiered_pricing;
	protected $visibility;
	protected $cart_controls;
	protected $registration;
	protected $admin_settings;
	protected $admin_product;
	protected $admin_user;

	/**
	 * Initialize the plugin
	 */
	public function __construct() {
		$this->maybe_migrate_settings();
		$this->load_dependencies();
		$this->init_modules();
	}

	/**
	 * Migrate settings from old option name to new option name (one-time)
	 */
	private function maybe_migrate_settings() {
		$old_settings = get_option( 'wholesale_powerhouse_settings' );
		$new_settings = get_option( 'wholpo_settings' );
		
		// If old settings exist and new settings don't, migrate them
		if ( $old_settings && ! $new_settings ) {
			update_option( 'wholpo_settings', $old_settings );
		}
	}

	/**
	 * Load required dependencies
	 */
	private function load_dependencies() {
		// Core functions
		require_once WHOLPO_PLUGIN_PATH . 'includes/wh-core-functions.php';

		// Core classes
		require_once WHOLPO_PLUGIN_PATH . 'includes/class-wholpo-roles.php';
		require_once WHOLPO_PLUGIN_PATH . 'includes/class-wholpo-pricing.php';
		require_once WHOLPO_PLUGIN_PATH . 'includes/class-wholpo-tiered-pricing.php';
		require_once WHOLPO_PLUGIN_PATH . 'includes/class-wholpo-visibility.php';
		require_once WHOLPO_PLUGIN_PATH . 'includes/class-wholpo-cart-controls.php';
		require_once WHOLPO_PLUGIN_PATH . 'includes/class-wholpo-registration.php';

		// Admin classes
		if ( is_admin() ) {
			require_once WHOLPO_PLUGIN_PATH . 'admin/class-wholpo-admin-settings.php';
			require_once WHOLPO_PLUGIN_PATH . 'admin/class-wholpo-admin-product.php';
			require_once WHOLPO_PLUGIN_PATH . 'admin/class-wholpo-admin-user.php';
		}
	}

	/**
	 * Initialize all plugin modules
	 */
	private function init_modules() {
		// Frontend modules
		$this->pricing         = new WHOLPO_Pricing();
		$this->tiered_pricing  = new WHOLPO_Tiered_Pricing();
		$this->visibility      = new WHOLPO_Visibility();
		$this->cart_controls   = new WHOLPO_Cart_Controls();
		$this->registration    = new WHOLPO_Registration();

		// Admin modules
		if ( is_admin() ) {
			$this->admin_settings = new WHOLPO_Admin_Settings();
			$this->admin_product  = new WHOLPO_Admin_Product();
			$this->admin_user     = new WHOLPO_Admin_User();
		}
	}

	/**
	 * Run the plugin
	 */
	public function run() {
		// Load assets
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ) );
	}

	/**
	 * Enqueue admin assets
	 */
	public function enqueue_admin_assets( $hook ) {
		// Load on product edit pages and settings pages
		$load_on_pages = array( 'post.php', 'post-new.php', 'woocommerce_page_wc-settings' );
		
		if ( in_array( $hook, $load_on_pages ) || strpos( $hook, 'woocommerce' ) !== false ) {
			wp_enqueue_style(
				'wh-admin-css',
				WHOLPO_PLUGIN_URL . 'assets/css/wh-admin.css',
				array(),
				WHOLPO_VERSION
			);

			wp_enqueue_script(
				'wh-admin-js',
				WHOLPO_PLUGIN_URL . 'assets/js/wh-admin.js',
				array( 'jquery' ),
				WHOLPO_VERSION,
				true
			);
		}
	}

	/**
	 * Enqueue public assets
	 */
	public function enqueue_public_assets() {
		wp_enqueue_style(
			'wh-public-css',
			WHOLPO_PLUGIN_URL . 'assets/css/wh-public.css',
			array(),
			WHOLPO_VERSION
		);

		wp_enqueue_script(
			'wh-public-js',
			WHOLPO_PLUGIN_URL . 'assets/js/wh-public.js',
			array( 'jquery' ),
			WHOLPO_VERSION,
			true
		);
	}
}
