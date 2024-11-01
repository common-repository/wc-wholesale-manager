<?php
/**
 * Customer approved email.
 *
 * @package WooCommerceWholesaleManager
 */

use WooCommerceWholesaleManager\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Class WCWM_Wholesaler_Approved.
 *
 * @since 1.0.0
 */
class WCWM_Wholesaler_Approved extends WCWM_Email {

	/**
	 * WCWM_Wholesaler_Approved constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->id             = 'wholesaler_approved_wholesale_user';
		$this->title          = __( 'Wholesale User Approved', 'wc-wholesale-manager' );
		$this->customer_email = true;
		$this->description    = __( 'Wholesale user approved emails are sent to the wholesale user when their account is approved.', 'wc-wholesale-manager' );
		$this->template_html  = 'emails/wholesaler-approved.php';
		$this->template_plain = 'emails/plain/wholesaler-approved.php';
		$this->placeholders   = array(
			'{user_pass}'        => '',
			'{user_login}'       => '',
			'{login_url}'        => Helper::get_login_url(),
			'{set_password_url}' => '',
		);

		// Triggers for this email.
		add_action( 'wc_wholesale_manager_approved_wholesale_user', array( $this, 'trigger' ), 10, 2 );

		// Call parent constructor.
		parent::__construct();

		// Other settings.
		$this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
	}

	/**
	 * Get email subject.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function get_default_subject() {
		return __( 'Your wholesale account has been approved', 'wc-wholesale-manager' );
	}

	/**
	 * Get email heading.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function get_default_heading() {
		return __( 'Your wholesale account has been approved', 'wc-wholesale-manager' );
	}

	/**
	 * Default email body content
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_default_body_content() {
		$content  = __( 'Hi there,', 'wc-wholesale-manager' ) . "\n\n";
		$content .= __( 'Thanks for registering for the wholesale account.', 'wc-wholesale-manager' ) . "\n\n";
		$content .= __( 'Your wholesale account has been approved. Your account details are as follows:', 'wc-wholesale-manager' ) . "\n\n";
		$content .= __( 'Your username is {user_login}', 'wc-wholesale-manager' ) . "\n\n";
		$content .= sprintf( '<a href="{set_password_url}">%s</a>', __( 'Click here to set your password.', 'wc-wholesale-manager' ) );

		return $content;
	}

	/**
	 * Trigger the sending of this email.
	 *
	 * @param int    $user_id User ID.
	 * @param string $user_pass User Password.
	 *
	 * @since 1.0.0
	 */
	public function trigger( $user_id, $user_pass ) {

		$this->setup_locale();

		$user = get_user_by( 'id', $user_id );

		if ( $user ) {

			$this->object                             = $user;
			$this->user_pass                          = $user_pass;
			$this->set_password_url                   = $this->generate_set_password_url();
			$this->user_login                         = stripslashes( $this->object->user_login );
			$this->user_email                         = stripslashes( $this->object->user_email );
			$this->recipient                          = $this->user_email;
			$this->placeholders['{user_pass}']        = $user->user_pass;
			$this->placeholders['{user_login}']       = $user->user_login;
			$this->placeholders['{set_password_url}'] = $this->set_password_url;

			if ( $this->is_enabled() && $this->get_recipient() ) {
				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			}

			$this->restore_locale();
		}
	}

	/**
	 * Generate set password URL link for a new user.
	 *
	 * @return string
	 */
	protected function generate_set_password_url() {
		// Generate a magic link so user can set initial password.
		$key = get_password_reset_key( $this->object );
		if ( ! is_wp_error( $key ) && $key ) {
			$action = 'newaccount';

			return wc_get_account_endpoint_url( 'lost-password' ) . "?action=$action&key=$key&login=" . rawurlencode( $this->object->user_login );
		}

		// Something went wrong while getting the key for new password URL, send customer to the generic password reset.
		return wc_get_account_endpoint_url( 'lost-password' );
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
}
