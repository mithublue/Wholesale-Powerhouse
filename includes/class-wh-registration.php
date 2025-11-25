<?php
/**
 * Wholesale Registration Module
 *
 * @package    Wholesale_Powerhouse
 * @subpackage Wholesale_Powerhouse/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class WH_Registration
 * Handles wholesale customer registration
 */
class WH_Registration {

	/**
	 * Initialize registration module
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Register hooks
	 */
	private function init_hooks() {
		// Register shortcode
		add_shortcode( 'wholesale_registration_form', array( $this, 'render_registration_form' ) );

		// Handle form submission
		add_action( 'init', array( $this, 'process_registration' ) );
	}

	/**
	 * Render registration form shortcode
	 *
	 * @param array $atts Shortcode attributes
	 * @return string Form HTML
	 */
	public function render_registration_form( $atts ) {
		// Don't show form if already logged in
		if ( is_user_logged_in() ) {
			return '<p>' . esc_html__( 'You are already registered and logged in.', 'wholesale-powerhouse' ) . '</p>';
		}

		// Start output buffering
		ob_start();

		// Load template
		$template_path = WH_POWERHOUSE_PLUGIN_PATH . 'templates/registration-form.php';
		
		if ( file_exists( $template_path ) ) {
			include $template_path;
		} else {
			echo '<p>' . esc_html__( 'Registration form template not found.', 'wholesale-powerhouse' ) . '</p>';
		}

		return ob_get_clean();
	}

	/**
	 * Process registration form submission
	 */
	public function process_registration() {
		// Check if form was submitted
		if ( ! isset( $_POST['wh_register_nonce'] ) ) {
			return;
		}

		// Verify nonce
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wh_register_nonce'] ) ), 'wh_registration_form' ) ) {
			wc_add_notice( __( 'Security verification failed. Please try again.', 'wholesale-powerhouse' ), 'error' );
			return;
		}

		// Sanitize and validate inputs
		$username    = isset( $_POST['wh_username'] ) ? sanitize_user( wp_unslash( $_POST['wh_username'] ) ) : '';
		$email       = isset( $_POST['wh_email'] ) ? sanitize_email( wp_unslash( $_POST['wh_email'] ) ) : '';
		$password    = isset( $_POST['wh_password'] ) ? wp_unslash( $_POST['wh_password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Passwords should not be sanitized
		$first_name  = isset( $_POST['wh_first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['wh_first_name'] ) ) : '';
		$last_name   = isset( $_POST['wh_last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['wh_last_name'] ) ) : '';
		$company     = isset( $_POST['wh_company'] ) ? sanitize_text_field( wp_unslash( $_POST['wh_company'] ) ) : '';
		$tax_id      = isset( $_POST['wh_tax_id'] ) ? sanitize_text_field( wp_unslash( $_POST['wh_tax_id'] ) ) : '';

		// Validate required fields
		$errors = array();

		if ( empty( $username ) ) {
			$errors[] = __( 'Username is required.', 'wholesale-powerhouse' );
		}

		if ( empty( $email ) || ! is_email( $email ) ) {
			$errors[] = __( 'A valid email address is required.', 'wholesale-powerhouse' );
		}

		if ( empty( $password ) ) {
			$errors[] = __( 'Password is required.', 'wholesale-powerhouse' );
		}

		if ( username_exists( $username ) ) {
			$errors[] = __( 'Username already exists. Please choose another one.', 'wholesale-powerhouse' );
		}

		if ( email_exists( $email ) ) {
			$errors[] = __( 'Email address is already registered.', 'wholesale-powerhouse' );
		}

		// If there are errors, display them
		if ( ! empty( $errors ) ) {
			foreach ( $errors as $error ) {
				wc_add_notice( $error, 'error' );
			}
			return;
		}

		// Create new user
		$user_id = wp_create_user( $username, $password, $email );

		if ( is_wp_error( $user_id ) ) {
			wc_add_notice( $user_id->get_error_message(), 'error' );
			return;
		}

		// Update user meta
		if ( ! empty( $first_name ) ) {
			update_user_meta( $user_id, 'first_name', $first_name );
		}
		if ( ! empty( $last_name ) ) {
			update_user_meta( $user_id, 'last_name', $last_name );
		}
		if ( ! empty( $company ) ) {
			update_user_meta( $user_id, 'billing_company', $company );
		}
		if ( ! empty( $tax_id ) ) {
			update_user_meta( $user_id, 'wh_tax_id', $tax_id );
		}

		// Get settings
		$settings = wh_get_settings();
		$registration_approval = isset( $settings['registration_approval'] ) ? $settings['registration_approval'] : false;

		// Assign role based on approval setting
		$user = new WP_User( $user_id );

		if ( $registration_approval ) {
			// Pending approval - set to customer role and flag for approval
			$user->set_role( 'customer' );
			update_user_meta( $user_id, 'wh_pending_approval', true );
			
			wc_add_notice(
				__( 'Your registration has been received and is pending approval. You will be notified once approved.', 'wholesale-powerhouse' ),
				'success'
			);
		} else {
			// Auto-approve - assign Bronze role immediately
			$user->set_role( 'wh_bronze' );
			
			wc_add_notice(
				__( 'Registration successful! You have been assigned Bronze wholesale status.', 'wholesale-powerhouse' ),
				'success'
			);

			// Log user in automatically
			wp_set_current_user( $user_id );
			wp_set_auth_cookie( $user_id );
		}

		// Send notification email to admin
		$this->send_admin_notification( $user_id, $username, $email, $company );

		// Redirect to my account page
		wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) );
		exit;
	}

	/**
	 * Send email notification to admin about new registration
	 *
	 * @param int    $user_id  User ID
	 * @param string $username Username
	 * @param string $email    Email
	 * @param string $company  Company name
	 */
	private function send_admin_notification( $user_id, $username, $email, $company ) {
		$admin_email = get_option( 'admin_email' );
		/* translators: %s: wholesale username. */
		$subject = sprintf( __( 'New Wholesale Registration: %s', 'wholesale-powerhouse' ), $username );
		
		/* translators: 1: username, 2: email, 3: company, 4: edit user URL. */
		$message = sprintf(
			__( "A new wholesale customer has registered on your site.\n\nUsername: %1\$s\nEmail: %2\$s\nCompany: %3\$s\n\nEdit user: %4\$s", 'wholesale-powerhouse' ),
			$username,
			$email,
			$company,
			admin_url( 'user-edit.php?user_id=' . $user_id )
		);

		wp_mail( $admin_email, $subject, $message );
	}
}
