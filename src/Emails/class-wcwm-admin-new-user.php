<?php
/**
 *  Admin email class for new wholesale user.
 *
 * @package WooCommerceWholesaleManager
 */

use WooCommerceWholesaleManager\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * WCWM_Admin_New_User class.
 *
 * @extends \WC_Email
 */
class WCWM_Admin_New_User extends WCWM_Email {

	/**
	 * WCWM_Admin_New_User constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->id             = 'admin_new_wholesale_user';
		$this->title          = __( 'New Wholesale User', 'wc-wholesale-manager' );
		$this->description    = __( 'New wholesale user emails are sent to chosen recipient(s) when a new wholesale user is held for moderation.', 'wc-wholesale-manager' );
		$this->template_html  = 'emails/admin-new-wholesaler.php';
		$this->template_plain = 'emails/plain/admin-new-wholesaler.php';
		$this->placeholders   = array(
			'{edit_user_url}' => '',
			'{user_role}'     => '',
		);

		// Triggers for this email.
		add_action( 'wc_wholesale_manager_pending_wholesale_user', array( $this, 'trigger' ), 10 );

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
		return __( 'New wholesale user registration ({user_email}) - {site_title}', 'wc-wholesale-manager' );
	}

	/**
	 * Get email heading.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function get_default_heading() {
		return __( 'New wholesale user registration', 'wc-wholesale-manager' );
	}

	/**
	 * Trigger.
	 *
	 * @param int $user_id User ID.
	 */
	public function trigger( $user_id ) {
		$this->setup_locale();
		$user = get_userdata( $user_id );
		if ( $user ) {
			$b2b_role                              = Helper::get_default_wholesaler_role();
			$role                                  = $b2b_role ? $b2b_role->name : '';
			$this->object                          = $user;
			$this->placeholders['{user_email}']    = $this->object->user_email;
			$this->placeholders['{edit_user_url}'] = add_query_arg( 'user_id', $user->ID, self_admin_url( 'user-edit.php' ) );
			$this->placeholders['{user_role}']     = $role;
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
				'email_heading' => $this->get_heading(),
				'body_content'  => $this->get_body_content(),
				'blogname'      => $this->get_blogname(),
				'sent_to_admin' => true,
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
				'email_heading' => $this->get_heading(),
				'body_content'  => $this->get_body_content(),
				'blogname'      => $this->get_blogname(),
				'sent_to_admin' => true,
				'plain_text'    => true,
				'email'         => $this,
			),
			'',
			$this->template_base
		);
	}

	/**
	 * Get default content html.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function get_default_body_content() {
		$content  = __( 'Hi there,', 'wc-wholesale-manager' ) . "\n\n";
		$content .= __( 'A new wholesale user has registered on your site {site_title}.', 'wc-wholesale-manager' ) . "\n\n";
		$content .= __( 'Username: {user_email}', 'wc-wholesale-manager' ) . "\n\n";
		$content .= __( 'Email: {user_email}', 'wc-wholesale-manager' ) . "\n\n";
		$content .= __( 'Role: {user_role}', 'wc-wholesale-manager' ) . "\n\n";
		$content .= __( 'You can edit this user in the dashboard here: {edit_user_url}', 'wc-wholesale-manager' ) . "\n\n";
		$content .= __( 'If you approve the request, the user will receive ‘Wholesale user approved’ email containing their login details so that they can access the store.', 'wc-wholesale-manager' ) . "\n\n";
		$content .= __( 'If you do not approve the request, the user will receive ‘Wholesale user declined’ email and the account will be deleted.', 'wc-wholesale-manager' ) . "\n\n";

		return $content;
	}

	/**
	 * Initialise settings form fields.
	 */
	public function init_form_fields() {
		/* Translators: %s: list of placeholders */
		$placeholder_text  = sprintf( __( 'Available placeholders: %s', 'wc-wholesale-manager' ), '<code>' . implode( '</code>, <code>', array_keys( $this->placeholders ) ) . '</code>' );
		$this->form_fields = array(
			'enabled'      => array(
				'title'   => __( 'Enable/Disable', 'wc-wholesale-manager' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'wc-wholesale-manager' ),
				'default' => 'yes',
			),
			'recipient'    => array(
				'title'       => __( 'Recipient(s)', 'wc-wholesale-manager' ),
				'type'        => 'text',
				/* Translators: %s: admin email */
				'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'wc-wholesale-manager' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
				'placeholder' => '',
				'default'     => '',
				'desc_tip'    => true,
			),
			'subject'      => array(
				'title'       => __( 'Subject', 'wc-wholesale-manager' ),
				'type'        => 'text',
				/* Translators: %s: blog name */
				'description' => sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'wc-wholesale-manager' ), $this->get_default_subject() ),
				'placeholder' => '',
				'default'     => $this->get_default_subject(),
				'desc_tip'    => true,
			),
			'heading'      => array(
				'title'       => __( 'Email Heading', 'wc-wholesale-manager' ),
				'type'        => 'text',
				/* Translators: %s: blog name */
				'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'wc-wholesale-manager' ), $this->get_default_heading() ),
				'placeholder' => '',
				'default'     => $this->get_default_heading(),
				'desc_tip'    => true,
			),
			'body_content' => array(
				'title'       => __( 'Email content', 'wc-wholesale-manager' ),
				'description' => __( 'Text to appear as the main email content.', 'wc-wholesale-manager' ) . ' ' . $placeholder_text,
				'css'         => 'width:400px; height: 200px;',
				'placeholder' => __( 'N/A', 'wc-wholesale-manager' ),
				'type'        => 'textarea',
				'default'     => $this->get_default_body_content(),
				'desc_tip'    => true,
			),
			'email_type'   => array(
				'title'       => __( 'Email type', 'wc-wholesale-manager' ),
				'type'        => 'select',
				'description' => __( 'Choose which format of email to send.', 'wc-wholesale-manager' ),
				'default'     => 'html',
				'class'       => 'email_type wc-enhanced-select',
				'options'     => $this->get_email_type_options(),
				'desc_tip'    => true,
			),
		);
	}
}
