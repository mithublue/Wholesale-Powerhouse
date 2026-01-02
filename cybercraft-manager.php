<?php
/**
 * CyberCraft Plugin Manager - Reusable Module
 *
 * A decoupled plugin manager that can be integrated with any WordPress plugin.
 * It checks if already registered before adding the menu.
 *
 * @package CyberCraft
 * @version 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Prevent multiple inclusions.
if ( defined( 'CYBERCRAFT_MANAGER_LOADED' ) ) {
	return;
}
define( 'CYBERCRAFT_MANAGER_LOADED', true );

/**
 * CyberCraft_Manager class
 *
 * Handles the CyberCraft Plugin Manager functionality.
 */
class CyberCraft_Manager {

	/**
	 * Instance of the class.
	 *
	 * @var CyberCraft_Manager
	 */
	private static $instance = null;

	/**
	 * Menu slug.
	 *
	 * @var string
	 */
	const MENU_SLUG = 'cybercraft-manager';

	/**
	 * Get the singleton instance.
	 *
	 * @return CyberCraft_Manager
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ), 99 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		// Register AJAX handlers.
		add_action( 'wp_ajax_cc_fetch_plugins', array( $this, 'ajax_fetch_plugins' ) );
		add_action( 'wp_ajax_cc_install_plugin', array( $this, 'ajax_install_plugin' ) );
		add_action( 'wp_ajax_cc_activate_plugin', array( $this, 'ajax_activate_plugin' ) );
		add_action( 'wp_ajax_cc_deactivate_plugin', array( $this, 'ajax_deactivate_plugin' ) );
		add_action( 'wp_ajax_cc_delete_plugin', array( $this, 'ajax_delete_plugin' ) );
	}

	/**
	 * Register admin menu if not already registered.
	 */
	public function register_menu() {
		global $menu;

		// Check if CyberCraft menu is already registered.
		$menu_exists = false;
		if ( is_array( $menu ) ) {
			foreach ( $menu as $item ) {
				if ( isset( $item[2] ) && $item[2] === self::MENU_SLUG ) {
					$menu_exists = true;
					break;
				}
			}
		}

		// Only add menu if it doesn't exist.
		if ( ! $menu_exists ) {
			add_menu_page(
				__( 'CyberCraft', 'cybercraft-manager' ),
				__( 'CyberCraft', 'cybercraft-manager' ),
				'manage_options',
				self::MENU_SLUG,
				array( $this, 'render_page' ),
				'dashicons-admin-plugins',
				30
			);
		}
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_assets( $hook ) {
		if ( 'toplevel_page_' . self::MENU_SLUG !== $hook ) {
			return;
		}

		// Get the URL of this file's directory.
		$plugin_url = $this->get_module_url();

		// Enqueue JavaScript.
		wp_enqueue_script(
			'cybercraft-manager-js',
			$plugin_url . 'assets/js/cybercraft.js',
			array( 'jquery' ),
			'1.0.0',
			true
		);

		wp_localize_script(
			'cybercraft-manager-js',
			'cybercraftAjax',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'cybercraft_nonce' ),
			)
		);
	}

	/**
	 * Get the URL of this module's directory.
	 *
	 * @return string Module URL.
	 */
	private function get_module_url() {
		$file_path = wp_normalize_path( __FILE__ );
		$plugins_path = wp_normalize_path( WP_PLUGIN_DIR );
		$relative_path = str_replace( $plugins_path, '', dirname( $file_path ) );
		return plugins_url( $relative_path ) . '/';
	}

	/**
	 * Render the admin page.
	 */
	public function render_page() {
		?>
		<style>
			/* CyberCraft Manager Styles */
			.cc-container {
				max-width: 1400px;
				margin: 20px auto;
				padding: 0 20px;
			}
			
			.cc-header {
				background: linear-gradient(135deg, #2563eb 0%, #0891b2 100%);
				padding: 40px;
				border-radius: 16px;
				margin-bottom: 30px;
				box-shadow: 0 10px 40px rgba(37, 99, 235, 0.3);
			}
			
			.cc-header h1 {
				color: white;
				font-size: 2.5rem;
				font-weight: 700;
				margin: 0 0 10px 0;
				display: flex;
				align-items: center;
				gap: 15px;
			}
			
			.cc-header p {
				color: rgba(255, 255, 255, 0.9);
				font-size: 1.1rem;
				margin: 0;
			}
			
			.cc-tabs {
				display: flex;
				gap: 10px;
				margin-bottom: 30px;
				border-bottom: 2px solid #e5e7eb;
			}
			
			.cc-tab {
				padding: 12px 24px;
				background: transparent;
				border: none;
				border-bottom: 3px solid transparent;
				font-weight: 600;
				font-size: 1rem;
				cursor: pointer;
				transition: all 0.3s ease;
				color: #6b7280;
			}
			
			.cc-tab.active {
				color: #2563eb;
				border-bottom-color: #2563eb;
			}
			
			.cc-tab:hover {
				color: #2563eb;
			}
			
			.cc-grid {
				display: grid;
				grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
				gap: 25px;
				margin-bottom: 30px;
			}
			
			.cc-plugin-card {
				background: white;
				border-radius: 12px;
				padding: 25px;
				box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
				transition: all 0.3s ease;
				border: 2px solid transparent;
			}
			
			.cc-plugin-card:hover {
				box-shadow: 0 8px 24px rgba(37, 99, 235, 0.15);
				border-color: #2563eb;
				transform: translateY(-2px);
			}
			
			.cc-plugin-header {
				display: flex;
				align-items: start;
				gap: 15px;
				margin-bottom: 15px;
			}
			
			.cc-plugin-icon {
				width: 60px;
				height: 60px;
				border-radius: 10px;
				background: linear-gradient(135deg, #2563eb 0%, #0891b2 100%);
				display: flex;
				align-items: center;
				justify-content: center;
				color: white;
				font-size: 1.5rem;
				font-weight: 700;
				flex-shrink: 0;
			}
			
			.cc-plugin-info {
				flex: 1;
			}
			
			.cc-plugin-name {
				font-size: 1.2rem;
				font-weight: 700;
				color: #1f2937;
				margin: 0 0 5px 0;
			}
			
			.cc-plugin-author {
				font-size: 0.85rem;
				color: #6b7280;
				margin: 0;
			}
			
			.cc-plugin-description {
				color: #374151;
				font-size: 0.95rem;
				line-height: 1.6;
				margin-bottom: 15px;
			}
			
			.cc-plugin-meta {
				display: flex;
				gap: 15px;
				margin-bottom: 15px;
				flex-wrap: wrap;
			}
			
			.cc-meta-item {
				display: flex;
				align-items: center;
				gap: 5px;
				font-size: 0.85rem;
				color: #6b7280;
			}
			
			.cc-plugin-actions {
				display: flex;
				gap: 10px;
				flex-wrap: wrap;
			}
			
			.cc-btn {
				padding: 10px 20px;
				border: none;
				border-radius: 8px;
				font-weight: 600;
				font-size: 0.9rem;
				cursor: pointer;
				transition: all 0.3s ease;
				display: inline-flex;
				align-items: center;
				gap: 8px;
			}
			
			.cc-btn-primary {
				background: linear-gradient(135deg, #2563eb 0%, #0891b2 100%);
				color: white;
			}
			
			.cc-btn-primary:hover {
				transform: translateY(-2px);
				box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
			}
			
			.cc-btn-success {
				background: #10b981;
				color: white;
			}
			
			.cc-btn-success:hover {
				background: #059669;
			}
			
			.cc-btn-danger {
				background: #ef4444;
				color: white;
			}
			
			.cc-btn-danger:hover {
				background: #dc2626;
			}
			
			.cc-btn-secondary {
				background: #6b7280;
				color: white;
			}
			
			.cc-btn-secondary:hover {
				background: #4b5563;
			}
			
			.cc-btn:disabled {
				opacity: 0.5;
				cursor: not-allowed;
			}
			
			.cc-loading {
				text-align: center;
				padding: 60px 20px;
				color: #6b7280;
			}
			
			.cc-spinner {
				display: inline-block;
				width: 40px;
				height: 40px;
				border: 4px solid #e5e7eb;
				border-top-color: #2563eb;
				border-radius: 50%;
				animation: cc-spin 1s linear infinite;
			}
			
			@keyframes cc-spin {
				to { transform: rotate(360deg); }
			}
			
			.cc-message {
				padding: 16px 20px;
				border-radius: 10px;
				margin-bottom: 20px;
				font-weight: 500;
			}
			
			.cc-message-success {
				background: #d1fae5;
				color: #065f46;
				border-left: 4px solid #10b981;
			}
			
			.cc-message-error {
				background: #fee2e2;
				color: #991b1b;
				border-left: 4px solid #ef4444;
			}
			
			.cc-hidden {
				display: none !important;
			}
			
			.cc-badge {
				display: inline-block;
				padding: 4px 10px;
				border-radius: 6px;
				font-size: 0.75rem;
				font-weight: 600;
				text-transform: uppercase;
			}
			
			.cc-badge-installed {
				background: #d1fae5;
				color: #065f46;
			}
			
			.cc-badge-active {
				background: #dbeafe;
				color: #1e40af;
			}
		</style>

		<div class="wrap">
			<div class="cc-container">
				<!-- Header -->
				<div class="cc-header">
					<h1>
						<span class="dashicons dashicons-admin-plugins" style="font-size: 2.5rem;"></span>
						<?php esc_html_e( 'CyberCraft Plugin Manager', 'cybercraft-manager' ); ?>
					</h1>
					<p><?php esc_html_e( 'Discover and manage plugins from CyberCraftIT and MithuBlue', 'cybercraft-manager' ); ?></p>
				</div>

				<!-- Messages -->
				<div id="cc-messages"></div>

				<!-- Tabs -->
				<div class="cc-tabs">
					<button class="cc-tab active" data-source="cybercraftit">
						<span class="dashicons dashicons-wordpress" style="margin-right: 5px;"></span>
						CyberCraftIT (WordPress.org)
					</button>
					<button class="cc-tab" data-source="mithublue">
						<span class="dashicons dashicons-admin-users" style="margin-right: 5px;"></span>
						MithuBlue
					</button>
				</div>

				<!-- Loading -->
				<div id="cc-loading" class="cc-loading">
					<div class="cc-spinner"></div>
					<p><?php esc_html_e( 'Loading plugins...', 'cybercraft-manager' ); ?></p>
				</div>

				<!-- Plugins Grid -->
				<div id="cc-plugins-grid" class="cc-grid cc-hidden">
					<!-- Plugin cards will be inserted here -->
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * AJAX: Fetch plugins from WordPress.org or custom source.
	 */
	public function ajax_fetch_plugins() {
		check_ajax_referer( 'cybercraft_nonce', 'nonce' );

		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$source  = isset( $_POST['source'] ) ? sanitize_text_field( wp_unslash( $_POST['source'] ) ) : 'cybercraftit';
		$plugins = array();

		if ( 'cybercraftit' === $source ) {
			$response = wp_remote_get( 'https://api.wordpress.org/plugins/info/1.2/?action=query_plugins&request[author]=cybercraftit&request[per_page]=100' );

			if ( ! is_wp_error( $response ) ) {
				$body = json_decode( wp_remote_retrieve_body( $response ), true );
				if ( isset( $body['plugins'] ) ) {
					foreach ( $body['plugins'] as $plugin ) {
						$plugins[] = array(
							'name'         => $plugin['name'],
							'slug'         => $plugin['slug'],
							'author'       => $plugin['author'],
							'description'  => wp_trim_words( $plugin['short_description'], 20 ),
							'version'      => $plugin['version'],
							'downloads'    => number_format( $plugin['downloaded'] ),
							'rating'       => round( $plugin['rating'] / 20, 1 ),
							'download_url' => $plugin['download_link'],
							'installed'    => $this->is_plugin_installed( $plugin['slug'] ),
							'active'       => $this->is_plugin_active( $plugin['slug'] ),
						);
					}
				}
			}
		} elseif ( 'mithublue' === $source ) {
			$response = wp_remote_get( 'https://api.wordpress.org/plugins/info/1.2/?action=query_plugins&request[author]=mithublue&request[per_page]=100' );

			if ( ! is_wp_error( $response ) ) {
				$body = json_decode( wp_remote_retrieve_body( $response ), true );
				if ( isset( $body['plugins'] ) ) {
					foreach ( $body['plugins'] as $plugin ) {
						$plugins[] = array(
							'name'         => $plugin['name'],
							'slug'         => $plugin['slug'],
							'author'       => $plugin['author'],
							'description'  => wp_trim_words( $plugin['short_description'], 20 ),
							'version'      => $plugin['version'],
							'downloads'    => number_format( $plugin['downloaded'] ),
							'rating'       => round( $plugin['rating'] / 20, 1 ),
							'download_url' => $plugin['download_link'],
							'installed'    => $this->is_plugin_installed( $plugin['slug'] ),
							'active'       => $this->is_plugin_active( $plugin['slug'] ),
						);
					}
				}
			}
		}

		wp_send_json_success( $plugins );
	}

	/**
	 * AJAX: Install a plugin.
	 */
	public function ajax_install_plugin() {
		check_ajax_referer( 'cybercraft_nonce', 'nonce' );

		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$slug         = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';
		$download_url = isset( $_POST['download_url'] ) ? esc_url_raw( wp_unslash( $_POST['download_url'] ) ) : '';

		if ( empty( $slug ) || empty( $download_url ) ) {
			wp_send_json_error( 'Missing plugin information' );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
		$result   = $upgrader->install( $download_url );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		if ( true === $result ) {
			wp_send_json_success( 'Plugin installed successfully' );
		} else {
			wp_send_json_error( 'Installation failed' );
		}
	}

	/**
	 * AJAX: Activate a plugin.
	 */
	public function ajax_activate_plugin() {
		check_ajax_referer( 'cybercraft_nonce', 'nonce' );

		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$slug = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';

		if ( empty( $slug ) ) {
			wp_send_json_error( 'Missing plugin slug' );
		}

		$plugin_file = $this->get_plugin_file( $slug );

		if ( ! $plugin_file ) {
			wp_send_json_error( 'Plugin file not found' );
		}

		$result = activate_plugin( $plugin_file );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		wp_send_json_success( 'Plugin activated successfully' );
	}

	/**
	 * AJAX: Deactivate a plugin.
	 */
	public function ajax_deactivate_plugin() {
		check_ajax_referer( 'cybercraft_nonce', 'nonce' );

		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$slug = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';

		if ( empty( $slug ) ) {
			wp_send_json_error( 'Missing plugin slug' );
		}

		$plugin_file = $this->get_plugin_file( $slug );

		if ( ! $plugin_file ) {
			wp_send_json_error( 'Plugin file not found' );
		}

		deactivate_plugins( $plugin_file );
		wp_send_json_success( 'Plugin deactivated successfully' );
	}

	/**
	 * AJAX: Delete a plugin.
	 */
	public function ajax_delete_plugin() {
		check_ajax_referer( 'cybercraft_nonce', 'nonce' );

		if ( ! current_user_can( 'delete_plugins' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$slug = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';

		if ( empty( $slug ) ) {
			wp_send_json_error( 'Missing plugin slug' );
		}

		$plugin_file = $this->get_plugin_file( $slug );

		if ( ! $plugin_file ) {
			wp_send_json_error( 'Plugin file not found' );
		}

		// Deactivate first if active.
		if ( is_plugin_active( $plugin_file ) ) {
			deactivate_plugins( $plugin_file );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$result = delete_plugins( array( $plugin_file ) );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		wp_send_json_success( 'Plugin deleted successfully' );
	}

	/**
	 * Check if plugin is installed.
	 *
	 * @param string $slug Plugin slug.
	 * @return bool
	 */
	private function is_plugin_installed( $slug ) {
		$plugin_file = $this->get_plugin_file( $slug );
		return false !== $plugin_file;
	}

	/**
	 * Check if plugin is active.
	 *
	 * @param string $slug Plugin slug.
	 * @return bool
	 */
	private function is_plugin_active( $slug ) {
		$plugin_file = $this->get_plugin_file( $slug );
		return $plugin_file && is_plugin_active( $plugin_file );
	}

	/**
	 * Get plugin file path.
	 *
	 * @param string $slug Plugin slug.
	 * @return string|false Plugin file path or false.
	 */
	private function get_plugin_file( $slug ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$all_plugins = get_plugins();

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			if ( strpos( $plugin_file, $slug . '/' ) === 0 || $plugin_file === $slug . '.php' ) {
				return $plugin_file;
			}
		}

		return false;
	}
}

// Initialize the CyberCraft Manager.
CyberCraft_Manager::get_instance();
