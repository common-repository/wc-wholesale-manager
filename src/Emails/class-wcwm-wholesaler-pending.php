<?php
/**
 * Customer pending approval email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/wholesaler-pending-approval.php.
 *
 * @since 1.0.0
 * @package WooCommerceWholesaleManager
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WCWM_Wholesaler_Pending.
 *
 * @extends \WC_Email
 */
class WCWM_Wholesaler_Pending extends WCWM_Email {

	/**
	 * WCWM_Wholesaler_Pending constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->id             = 'customer_pending_wholesale_user';
		$this->title          = __( 'Pending Wholesale User', 'wc-wholesale-manager' );
		$this->customer_email = true;
		$this->description    = __( 'Pending wholesale user emails are sent to the wholesale user when their wholesale account is pending approval.', 'wc-wholesale-manager' );
		$this->template_html  = 'emails/wholesaler-pending.php';
		$this->template_plain = 'emails/plain/wholesaler-pending.php';
		$this->placeholders   = array(
			'{user_email}' => '',
		);

		// Triggers for this email.
		add_action( 'wc_wholesale_manager_pending_wholesale_user', array( $this, 'trigger' ), 10, 1 );

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
		return __( '[{site_title}] Your wholesale account is pending approval', 'wc-wholesale-manager' );
	}

	/**
	 * Get email heading.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function get_default_heading() {
		return __( 'Your wholesale account is pending approval', 'wc-wholesale-manager' );
	}

	/**
	 * Default email content.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_default_content() {
		return __( 'Hi there. Your wholesale account is pending approval. We will let you know once it has been approved.', 'wc-wholesale-manager' );
	}

	/**
	 * Trigger the sending of this email.
	 *
	 * @param int $user_id The user ID.
	 * @since  1.0.0
	 * @return void
	 */
	public function trigger( $user_id ) {

		$this->setup_locale();

		$this->object = get_user_by( 'id', $user_id );

		if ( $this->object ) {
			$this->recipient = $this->object->user_email;
		}

		if ( $this->is_enabled() && $this->get_recipient() ) {
			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}

		$this->restore_locale();
	}

	/**
	 * Get content html.
	 *
	 * @since  1.0.0
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
	 * @since  1.0.0
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
