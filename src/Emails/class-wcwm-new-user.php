<?php
/**
 * The email sent to the wholesale user when they sign up for a wholesale account.
 *
 * @package WooCommerceWholesaleManager
 */

use WooCommerceWholesaleManager\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Class WCWM_Wholesaler_New_User.
 *
 * @extends \WC_Email
 */
class WCWM_Wholesaler_New_User extends WCWM_Email {

	/**
	 * WCWM_Wholesaler_New_User constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->id             = 'wholesaler_new_wholesale_user';
		$this->title          = __( 'New Wholesale User', 'wc-wholesale-manager' );
		$this->customer_email = true;
		$this->description    = __( 'New wholesale user emails are sent to the wholesale wholesale account is created manually by an administrator.', 'wc-wholesale-manager' );
		$this->template_html  = 'emails/wholesaler-new-user.php';
		$this->template_plain = 'emails/plain/wholesaler-new-user.php';
		$this->placeholders   = array(
			'{user_pass}'        => '',
			'{user_login}'       => '',
			'{login_url}'        => Helper::get_login_url(),
			'{set_password_url}' => '',
		);

		add_filter( 'wp_new_user_notification_email', array( $this, 'override_new_user_notification_email' ), 999, 3 );
		// Triggers for this email.
		add_action( 'wc_wholesale_manager_auto_approved_wholesale_user', array( $this, 'trigger' ), 10, 2 );

		// Call parent constructor.
		parent::__construct();
	}

	/**
	 * Override the default new user email.
	 *
	 * @param array    $email The email data.
	 * @param \WP_User $user The user object.
	 * @param string   $blogname The blog name.
	 *
	 * @return array The email data.
	 */
	public function override_new_user_notification_email( $email, $user, $blogname ) {
		if ( ! Helper::is_wholesaler( $user ) ) {
			return $email;
		}

		// If the user role is not wholesale, return the default email.
		if ( get_user_meta( $user->ID, 'wcwm_allow_default_wp_email', true ) ) {
			delete_user_meta( $user->ID, 'wcwm_allow_default_wp_email' );
			return $email;
		}

		$this->object  = $user;
		$this->user_id = $this->object->ID;

		// The password is regenerated here, so it can be sent in the email without being stored.
		$password = wp_generate_password();
		wp_set_password( $password, $this->user_id );

		$this->user_pass = $password;
		$this->recipient = $this->object->user_email;

		$this->placeholders['{user_pass}']        = $password;
		$this->placeholders['{user_login}']       = $this->object->user_email;
		$this->placeholders['{set_password_url}'] = $this->generate_set_password_url();

		$email = array(
			'to'          => $this->recipient,
			'subject'     => $this->get_subject(),
			'message'     => $this->style_inline( $this->get_content() ),
			'headers'     => $this->get_headers(),
			'attachments' => $this->get_attachments(),
		);

		return $email;
	}


	/**
	 * Get email subject.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function get_default_subject() {
		return __( 'Your wholesale account has been created', 'wc-wholesale-manager' );
	}

	/**
	 * Get email heading.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function get_default_heading() {
		return __( 'Welcome to {site_title}', 'wc-wholesale-manager' );
	}

	/**
	 * Default email body content
	 *
	 * @return string
	 */
	public function get_default_body_content() {
		$content  = __( 'Thanks for registering for the wholesale store. You can login as follows:', 'wc-wholesale-manager' );
		$content .= "\n\n";
		$content .= sprintf( '%s {user_login}', __( 'Username:', 'wc-wholesale-manager' ) );
		$content .= "\n\n";
		$content .= sprintf( '<a href="{set_password_url}">%s</a>', __( 'Click here to set your password.', 'wc-wholesale-manager' ) );

		return $content;
	}

	/**
	 * Trigger the sending of this email.
	 *
	 * @param int    $user_id The user ID.
	 * @param string $password The user password.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function trigger( $user_id, $password ) {
		$this->setup_locale();

		$user = get_user_by( 'id', $user_id );

		if ( $user ) {
			$this->object           = $user;
			$this->user_pass        = $password;
			$this->set_password_url = $this->generate_set_password_url();
			$this->user_login       = stripslashes( $this->object->user_login );
			$this->user_email       = stripslashes( $this->object->user_email );
			$this->recipient        = $this->user_email;

			$this->placeholders['{user_pass}']        = $this->user_pass;
			$this->placeholders['{user_login}']       = $this->user_email;
			$this->placeholders['{set_password_url}'] = $this->set_password_url;
		}

		if ( $this->is_enabled() && $this->get_recipient() ) {
			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}

		$this->restore_locale();
	}

	/**
	 * Get content html.
	 *
	 * @return string
	 */
	public function get_content_html() {
		return wc_get_template_html(
			$this->template_html,
			array(
				'email_heading'    => $this->get_heading(),
				'body_content'     => $this->get_body_content(),
				'user_login'       => $this->user_login,
				'user_pass'        => $this->user_pass,
				'set_password_url' => $this->set_password_url,
				'blogname'         => $this->get_blogname(),
				'sent_to_admin'    => false,
				'plain_text'       => false,
				'email'            => $this,
			),
			'',
			$this->template_base
		);
	}

	/**
	 * Get content plain.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		return wc_get_template_html(
			$this->template_plain,
			array(
				'email_heading'    => $this->get_heading(),
				'body_content'     => $this->get_body_content(),
				'user_login'       => $this->user_login,
				'user_pass'        => $this->user_pass,
				'set_password_url' => $this->set_password_url,
				'blogname'         => $this->get_blogname(),
				'sent_to_admin'    => false,
				'plain_text'       => true,
				'email'            => $this,
			),
			'',
			$this->template_base
		);
	}

	/**
	 * Generate set password URL link for a new user.
	 *
	 * @return string
	 */
	protected function generate_set_password_url() {
		// Generate a magic link so user can set initial password.
		$key = get_password_reset_key( $this->object );
		if ( ! is_wp_error( $key ) ) {
			$action = 'newaccount';

			return wc_get_account_endpoint_url( 'lost-password' ) . "?action=$action&key=$key&login=" . rawurlencode( $this->object->user_login );
		}

		// Something went wrong while getting the key for new password URL, send wholesaler to the generic password reset.
		return wc_get_account_endpoint_url( 'lost-password' );
	}
}
