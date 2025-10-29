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
 * Class WH_Powerhouse
 * The main plugin orchestrator
 */
class WH_Powerhouse {

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
		$this->load_dependencies();
		$this->init_modules();
	}

	/**
	 * Load required dependencies
	 */
	private function load_dependencies() {
		// Core functions
		require_once WH_POWERHOUSE_PLUGIN_PATH . 'includes/wh-core-functions.php';

		// Core classes
		require_once WH_POWERHOUSE_PLUGIN_PATH . 'includes/class-wh-roles.php';
		require_once WH_POWERHOUSE_PLUGIN_PATH . 'includes/class-wh-pricing.php';
		require_once WH_POWERHOUSE_PLUGIN_PATH . 'includes/class-wh-tiered-pricing.php';
		require_once WH_POWERHOUSE_PLUGIN_PATH . 'includes/class-wh-visibility.php';
		require_once WH_POWERHOUSE_PLUGIN_PATH . 'includes/class-wh-cart-controls.php';
		require_once WH_POWERHOUSE_PLUGIN_PATH . 'includes/class-wh-registration.php';

		// Admin classes
		if ( is_admin() ) {
			require_once WH_POWERHOUSE_PLUGIN_PATH . 'admin/class-wh-admin-settings.php';
			require_once WH_POWERHOUSE_PLUGIN_PATH . 'admin/class-wh-admin-product.php';
			require_once WH_POWERHOUSE_PLUGIN_PATH . 'admin/class-wh-admin-user.php';
		}
	}

	/**
	 * Initialize all plugin modules
	 */
	private function init_modules() {
		// Frontend modules
		$this->pricing         = new WH_Pricing();
		$this->tiered_pricing  = new WH_Tiered_Pricing();
		$this->visibility      = new WH_Visibility();
		$this->cart_controls   = new WH_Cart_Controls();
		$this->registration    = new WH_Registration();

		// Admin modules
		if ( is_admin() ) {
			$this->admin_settings = new WH_Admin_Settings();
			$this->admin_product  = new WH_Admin_Product();
			$this->admin_user     = new WH_Admin_User();
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
				WH_POWERHOUSE_PLUGIN_URL . 'assets/css/wh-admin.css',
				array(),
				WH_POWERHOUSE_VERSION
			);

			wp_enqueue_script(
				'wh-admin-js',
				WH_POWERHOUSE_PLUGIN_URL . 'assets/js/wh-admin.js',
				array( 'jquery' ),
				WH_POWERHOUSE_VERSION,
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
			WH_POWERHOUSE_PLUGIN_URL . 'assets/css/wh-public.css',
			array(),
			WH_POWERHOUSE_VERSION
		);

		wp_enqueue_script(
			'wh-public-js',
			WH_POWERHOUSE_PLUGIN_URL . 'assets/js/wh-public.js',
			array( 'jquery' ),
			WH_POWERHOUSE_VERSION,
			true
		);
	}
}
