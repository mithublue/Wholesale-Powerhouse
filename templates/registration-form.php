<?php
/**
 * Wholesale Registration Form Template
 *
 * @package    Wholesale_Powerhouse
 * @subpackage Wholesale_Powerhouse/templates
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
?>

<div class="wh-registration-form-wrapper">
	<h2><?php esc_html_e( 'Wholesale Registration', 'wholesale-powerhouse' ); ?></h2>
	
	<p><?php esc_html_e( 'Apply for wholesale access to get special pricing on bulk orders.', 'wholesale-powerhouse' ); ?></p>

	<form method="post" class="wh-registration-form" id="wh-registration-form">
		<?php wp_nonce_field( 'wh_registration_form', 'wh_register_nonce' ); ?>

		<div class="wh-form-section">
			<h3><?php esc_html_e( 'Account Information', 'wholesale-powerhouse' ); ?></h3>

			<p class="wh-form-row">
				<label for="wh_username">
					<?php esc_html_e( 'Username', 'wholesale-powerhouse' ); ?>
					<span class="required">*</span>
				</label>
				<input type="text" name="wh_username" id="wh_username" required 
					   value="<?php echo isset( $_POST['wh_username'] ) ? esc_attr( $_POST['wh_username'] ) : ''; ?>" />
			</p>

			<p class="wh-form-row">
				<label for="wh_email">
					<?php esc_html_e( 'Email Address', 'wholesale-powerhouse' ); ?>
					<span class="required">*</span>
				</label>
				<input type="email" name="wh_email" id="wh_email" required 
					   value="<?php echo isset( $_POST['wh_email'] ) ? esc_attr( $_POST['wh_email'] ) : ''; ?>" />
			</p>

			<p class="wh-form-row">
				<label for="wh_password">
					<?php esc_html_e( 'Password', 'wholesale-powerhouse' ); ?>
					<span class="required">*</span>
				</label>
				<input type="password" name="wh_password" id="wh_password" required />
				<span class="wh-field-description"><?php esc_html_e( 'Minimum 6 characters', 'wholesale-powerhouse' ); ?></span>
			</p>
		</div>

		<div class="wh-form-section">
			<h3><?php esc_html_e( 'Personal Information', 'wholesale-powerhouse' ); ?></h3>

			<p class="wh-form-row">
				<label for="wh_first_name">
					<?php esc_html_e( 'First Name', 'wholesale-powerhouse' ); ?>
				</label>
				<input type="text" name="wh_first_name" id="wh_first_name" 
					   value="<?php echo isset( $_POST['wh_first_name'] ) ? esc_attr( $_POST['wh_first_name'] ) : ''; ?>" />
			</p>

			<p class="wh-form-row">
				<label for="wh_last_name">
					<?php esc_html_e( 'Last Name', 'wholesale-powerhouse' ); ?>
				</label>
				<input type="text" name="wh_last_name" id="wh_last_name" 
					   value="<?php echo isset( $_POST['wh_last_name'] ) ? esc_attr( $_POST['wh_last_name'] ) : ''; ?>" />
			</p>
		</div>

		<div class="wh-form-section">
			<h3><?php esc_html_e( 'Business Information', 'wholesale-powerhouse' ); ?></h3>

			<p class="wh-form-row">
				<label for="wh_company">
					<?php esc_html_e( 'Company Name', 'wholesale-powerhouse' ); ?>
				</label>
				<input type="text" name="wh_company" id="wh_company" 
					   value="<?php echo isset( $_POST['wh_company'] ) ? esc_attr( $_POST['wh_company'] ) : ''; ?>" />
			</p>

			<p class="wh-form-row">
				<label for="wh_tax_id">
					<?php esc_html_e( 'Business/Tax ID', 'wholesale-powerhouse' ); ?>
				</label>
				<input type="text" name="wh_tax_id" id="wh_tax_id" 
					   value="<?php echo isset( $_POST['wh_tax_id'] ) ? esc_attr( $_POST['wh_tax_id'] ) : ''; ?>" />
				<span class="wh-field-description"><?php esc_html_e( 'Optional: Your business or tax identification number', 'wholesale-powerhouse' ); ?></span>
			</p>
		</div>

		<p class="wh-form-row">
			<button type="submit" class="button wh-submit-btn">
				<?php esc_html_e( 'Register for Wholesale Access', 'wholesale-powerhouse' ); ?>
			</button>
		</p>

		<p class="wh-form-footer">
			<small>
				<?php
				$settings = wh_get_settings();
				$registration_approval = isset( $settings['registration_approval'] ) ? $settings['registration_approval'] : false;
				
				if ( $registration_approval ) {
					esc_html_e( 'Your registration will be reviewed and you will be notified once approved.', 'wholesale-powerhouse' );
				} else {
					esc_html_e( 'You will be automatically granted Bronze wholesale status upon registration.', 'wholesale-powerhouse' );
				}
				?>
			</small>
		</p>
	</form>
</div>
