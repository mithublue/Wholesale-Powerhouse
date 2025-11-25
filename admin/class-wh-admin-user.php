<?php
/**
 * Admin User Management
 *
 * @package    Wholesale_Powerhouse
 * @subpackage Wholesale_Powerhouse/admin
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class WH_Admin_User
 * Adds wholesale role management to user profiles
 */
class WH_Admin_User {

	/**
	 * Initialize user admin
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Register hooks
	 */
	private function init_hooks() {
		// Add wholesale role fields to user profile
		add_action( 'show_user_profile', array( $this, 'add_wholesale_user_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'add_wholesale_user_fields' ) );

		// Save wholesale user fields
		add_action( 'personal_options_update', array( $this, 'save_wholesale_user_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_wholesale_user_fields' ) );

		// Add wholesale column to users list
		add_filter( 'manage_users_columns', array( $this, 'add_wholesale_user_column' ) );
		add_filter( 'manage_users_custom_column', array( $this, 'render_wholesale_user_column' ), 10, 3 );

		// Add wholesale role filter to users list
		add_action( 'restrict_manage_users', array( $this, 'add_wholesale_role_filter' ) );
		add_filter( 'pre_get_users', array( $this, 'filter_users_by_wholesale_role' ) );

		// Add bulk actions
		add_filter( 'bulk_actions-users', array( $this, 'add_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-users', array( $this, 'handle_bulk_actions' ), 10, 3 );
	}

	/**
	 * Add wholesale fields to user profile
	 *
	 * @param WP_User $user User object
	 */
	public function add_wholesale_user_fields( $user ) {
		// Only show to users who can edit users
		if ( ! current_user_can( 'edit_users' ) ) {
			return;
		}

		$pending_approval = (bool) get_user_meta( $user->ID, 'wh_pending_approval', true );
		$tax_id           = sanitize_text_field( get_user_meta( $user->ID, 'wh_tax_id', true ) );

		?>
		<h2><?php esc_html_e( 'Wholesale Information', 'wholesale-powerhouse' ); ?></h2>
		<table class="form-table">
			<?php wp_nonce_field( 'wh_save_user_fields', '_wh_user_nonce' ); ?>
			<tr>
				<th><label for="wh_tax_id"><?php esc_html_e( 'Tax/Business ID', 'wholesale-powerhouse' ); ?></label></th>
				<td>
					<input type="text" name="wh_tax_id" id="wh_tax_id" value="<?php echo esc_attr( $tax_id ); ?>" class="regular-text" />
					<p class="description"><?php esc_html_e( 'Business or tax identification number', 'wholesale-powerhouse' ); ?></p>
				</td>
			</tr>
			<?php if ( $pending_approval ) : ?>
			<tr>
				<th><?php esc_html_e( 'Approval Status', 'wholesale-powerhouse' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="wh_approve_customer" id="wh_approve_customer" value="1" />
						<?php esc_html_e( 'Approve this wholesale customer (assign Bronze role)', 'wholesale-powerhouse' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'This user is pending approval for wholesale access.', 'wholesale-powerhouse' ); ?></p>
				</td>
			</tr>
			<?php endif; ?>
		</table>
		<?php
	}

	/**
	 * Save wholesale user fields
	 *
	 * @param int $user_id User ID
	 */
	public function save_wholesale_user_fields( $user_id ) {
		// Check permissions
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		if ( ! isset( $_POST['_wh_user_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wh_user_nonce'] ) ), 'wh_save_user_fields' ) ) {
			return false;
		}

		// Save tax ID
		if ( isset( $_POST['wh_tax_id'] ) ) {
			update_user_meta( $user_id, 'wh_tax_id', sanitize_text_field( wp_unslash( $_POST['wh_tax_id'] ) ) );
		}

		// Handle approval
		if ( isset( $_POST['wh_approve_customer'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['wh_approve_customer'] ) ) ) {
			// Force the role dropdown to bronze so WordPress core saves it correctly
			$_POST['role'] = 'wh_bronze';
			$this->approve_wholesale_customer( $user_id );
		}
	}

	/**
	 * Approve a pending wholesale customer
	 *
	 * @param int $user_id User ID
	 */
	private function approve_wholesale_customer( $user_id ) {
		$user = new WP_User( $user_id );

		// Assign Bronze wholesale role exclusively
		$user->set_role( 'wh_bronze' );

		// Remove pending flag
		delete_user_meta( $user_id, 'wh_pending_approval' );

		// Send approval email to customer
		$user_email = $user->user_email;
		$subject = __( 'Your Wholesale Account Has Been Approved', 'wholesale-powerhouse' );
		/* translators: 1: customer display name, 2: login URL. */
		$message = sprintf(
			__( "Hello %1\$s,\n\nYour wholesale account has been approved! You now have Bronze wholesale access.\n\nYou can log in here: %2\$s", 'wholesale-powerhouse' ),
			$user->display_name,
			wp_login_url()
		);

		wp_mail( $user_email, $subject, $message );
	}

	/**
	 * Add wholesale column to users list
	 *
	 * @param array $columns Existing columns
	 * @return array Modified columns
	 */
	public function add_wholesale_user_column( $columns ) {
		$columns['wholesale'] = __( 'Wholesale', 'wholesale-powerhouse' );
		return $columns;
	}

	/**
	 * Render wholesale column content
	 *
	 * @param string $output      Custom column output
	 * @param string $column_name Column name
	 * @param int    $user_id     User ID
	 * @return string
	 */
	public function render_wholesale_user_column( $output, $column_name, $user_id ) {
		if ( $column_name !== 'wholesale' ) {
			return $output;
		}

		$user             = get_userdata( $user_id );
		$wholesale_role   = WH_Roles::get_user_wholesale_role( $user_id );
		$pending_approval = (bool) get_user_meta( $user_id, 'wh_pending_approval', true );

		if ( $pending_approval ) {
			return '<span class="wh-pending" style="color: orange;">' . esc_html__( 'Pending Approval', 'wholesale-powerhouse' ) . '</span>';
		}

		if ( $wholesale_role ) {
			$settings = wh_get_settings();
			$roles    = isset( $settings['roles'] ) ? $settings['roles'] : array();
			$label    = isset( $roles[ $wholesale_role ]['label'] ) ? $roles[ $wholesale_role ]['label'] : ucfirst( str_replace( 'wh_', '', $wholesale_role ) );
			
			return '<strong>' . esc_html( $label ) . '</strong>';
		}

		return esc_html__( '—', 'wholesale-powerhouse' );
	}

	/**
	 * Add wholesale role filter to users list
	 */
	public function add_wholesale_role_filter() {
		$current_role = isset( $_GET['wh_role'] ) ? sanitize_text_field( wp_unslash( $_GET['wh_role'] ) ) : '';

		echo '<select name="wh_role" id="wh_role" style="float: none;">';
		echo '<option value="">' . esc_html__( 'All Wholesale Roles', 'wholesale-powerhouse' ) . '</option>';

		$settings = wh_get_settings();
		$roles    = isset( $settings['roles'] ) ? $settings['roles'] : array();

		foreach ( $roles as $role_key => $role_data ) {
			$label = isset( $role_data['label'] ) ? $role_data['label'] : ucfirst( str_replace( 'wh_', '', $role_key ) );
			printf(
				'<option value="%1$s"%2$s>%3$s</option>',
				esc_attr( $role_key ),
				selected( $current_role, $role_key, false ),
				esc_html( $label )
			);
		}

		printf(
			'<option value="pending"%1$s>%2$s</option>',
			selected( $current_role, 'pending', false ),
			esc_html__( 'Pending Approval', 'wholesale-powerhouse' )
		);
		echo '</select>';

		// Nonce to accompany the users filter submission
		wp_nonce_field( 'wh_users_filter', '_wh_users_filter_nonce' );
	}

	/**
	 * Filter users by wholesale role
	 *
	 * @param WP_User_Query $query User query object
	 */
	public function filter_users_by_wholesale_role( $query ) {
		global $pagenow;

		$requested_role = isset( $_GET['wh_role'] ) ? sanitize_text_field( wp_unslash( $_GET['wh_role'] ) ) : '';
        $nonce_value    = isset( $_GET['_wh_users_filter_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wh_users_filter_nonce'] ) ) : '';

        if ( $pagenow !== 'users.php' || empty( $requested_role ) ) {
            return;
        }

        // Verify nonce from the users filter form; bail if missing/invalid.
        if ( ! $nonce_value || ! wp_verify_nonce( $nonce_value, 'wh_users_filter' ) ) {
            return;
        }

        $wh_role = $requested_role;

		if ( $wh_role === 'pending' ) {
			// Filter by pending approval
			$query->set( 'meta_key', 'wh_pending_approval' );
			$query->set( 'meta_value', '1' );
		} else {
			// Filter by wholesale role
			$query->set( 'role', $wh_role );
		}
	}

	/**
	 * Add bulk actions for wholesale roles
	 *
	 * @param array $actions Existing actions
	 * @return array Modified actions
	 */
	public function add_bulk_actions( $actions ) {
		$settings = wh_get_settings();
		$roles    = isset( $settings['roles'] ) ? $settings['roles'] : array();

		foreach ( $roles as $role_key => $role_data ) {
			$label = isset( $role_data['label'] ) ? $role_data['label'] : ucfirst( str_replace( 'wh_', '', $role_key ) );
			/* translators: %s: Wholesale role label. */
			$actions[ 'wh_assign_' . $role_key ] = sprintf( __( 'Assign %s Role', 'wholesale-powerhouse' ), $label );
		}

		return $actions;
	}

	/**
	 * Handle bulk actions for wholesale roles
	 *
	 * @param string $redirect_url Redirect URL
	 * @param string $action       Action name
	 * @param array  $user_ids     User IDs
	 * @return string Modified redirect URL
	 */
	public function handle_bulk_actions( $redirect_url, $action, $user_ids ) {
		// Check if this is a wholesale assign action
		if ( strpos( $action, 'wh_assign_' ) !== 0 ) {
			return $redirect_url;
		}

		$role_key = str_replace( 'wh_assign_', '', $action );

		// Validate role
		$wholesale_roles = WH_Roles::get_wholesale_role_keys();
		if ( ! in_array( $role_key, $wholesale_roles ) ) {
			return $redirect_url;
		}

		// Assign role to each user
		foreach ( $user_ids as $user_id ) {
			$user = new WP_User( $user_id );
			
			// Remove other wholesale roles first
			foreach ( $wholesale_roles as $old_role ) {
				$user->remove_role( $old_role );
			}
			
			// Add new role
			$user->add_role( $role_key );

			// Remove pending approval flag
			delete_user_meta( $user_id, 'wh_pending_approval' );
		}

		// Add success message to redirect URL
		$redirect_url = add_query_arg( 'wh_bulk_assigned', count( $user_ids ), $redirect_url );

		return $redirect_url;
	}
}
