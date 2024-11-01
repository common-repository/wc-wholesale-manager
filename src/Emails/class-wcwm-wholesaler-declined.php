<?php
/**
 * Customer declined wholesale account email
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/wholesaler-declined.php.
 *
 * @see        https://docs.woocommerce.com/document/template-structure/
 * @since      1.0.0
 * @package WooCommerceWholesaleManager
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WCWM_Wholesaler_Declined.
 *
 * @since 1.0.0
 */
class WCWM_Wholesaler_Declined extends WCWM_Email {

	/**
	 * WCWM_Wholesaler_Declined constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->id             = 'wholesaler_declined_wholesale_user';
		$this->title          = __( 'Declined Wholesale User', 'wc-wholesale-manager' );
		$this->customer_email = true;
		$this->description    = __( 'Declined wholesale user emails are sent to the wholesale user when their wholesale account is declined.', 'wc-wholesale-manager' );
		$this->template_html  = 'emails/wholesaler-declined.php';
		$this->template_plain = 'emails/plain/wholesaler-declined.php';
		$this->placeholders   = array(
			'{user_email}' => '',
		);

		// Triggers for this email.
		add_action( 'wc_wholesale_manager_declined_wholesale_user', array( $this, 'trigger' ), 10, 2 );

		// Call parent constructor.
		parent::__construct();
	}

	/**
	 * Get email subject.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function get_default_subject() {
		return __( 'Your wholesale account has been declined', 'wc-wholesale-manager' );
	}

	/**
	 * Get email heading.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function get_default_heading() {
		return __( 'Your wholesale account has been declined', 'wc-wholesale-manager' );
	}

	/**
	 * Default email content.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_default_body_content() {
		return __( 'Thanks for applying for the wholesale store. Unfortunately your request has not been accepted. Please contact us for further details.', 'wc-wholesale-manager' );
	}

	/**
	 * Trigger the sending of this email.
	 *
	 * @param int $user_id The user ID.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function trigger( $user_id ) {

		$this->setup_locale();

		$user = get_user_by( 'id', $user_id );

		if ( $user ) {
			$this->object                       = $user;
			$this->placeholders['{user_email}'] = $this->object->user_email;
			$this->recipient                    = $this->object->user_email;

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
				'user'          => $this->object,
				'email_heading' => $this->get_heading(),
				'body_content'  => $this->get_body_content(),
				'sent_to_admin' => false,
				'plain_text'    => false,
				'email'         => $this,
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
				'user'          => $this->object,
				'email_heading' => $this->get_heading(),
				'body_content'  => $this->get_body_content(),
				'sent_to_admin' => false,
				'plain_text'    => true,
				'email'         => $this,
			),
			'',
			$this->template_base
		);
	}
}
