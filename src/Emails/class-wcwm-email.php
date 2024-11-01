<?php
/**
 * Class Base email.
 *
 * @extends \WC_Email
 * @since 1.0.0
 * @package WooCommerceWholesaleManager
 */

use WooCommerceWholesaleManager\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Class WCWM_Email.
 *
 * @extends \WC_Email
 */
class WCWM_Email extends \WC_Email {

	/**
	 * Template path.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $default_path;

	/**
	 * WCWM_Email constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->template_base = trailingslashit( Plugin::instance()->get_template_path() );
		$this->placeholders  = array(
			'{site_title}' => $this->get_blogname(),
		);
		parent::__construct();
	}

	/**
	 * Default email content
	 *
	 * @return string
	 */
	public function get_default_body_content() {
		return '';
	}

	/**
	 * Return content from the body_content field.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_body_content() {
		$content = $this->get_option( 'body_content', '' );
		if ( empty( $content ) ) {
			$content = $this->get_default_body_content();
		}

		return apply_filters( 'wc_wholesale_manager_email_body_content_' . $this->id, $this->format_string( $content ), $this->object, $this );
	}

	/**
	 * Initialise settings form fields.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init_form_fields() {
		/* translators: %s: list of placeholders */
		$placeholder_text  = sprintf( __( 'Available placeholders: %s', 'wc-wholesale-manager' ), '<code>' . \implode( '</code>, <code>', \array_keys( $this->placeholders ) ) . '</code>' );
		$this->form_fields = array(
			'enabled'      => array(
				'title'   => __( 'Enable/Disable', 'wc-wholesale-manager' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'wc-wholesale-manager' ),
				'default' => 'yes',
			),
			'subject'      => array(
				'title'       => __( 'Subject', 'wc-wholesale-manager' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => $placeholder_text,
				'placeholder' => $this->get_default_subject(),
				'default'     => $this->get_default_subject(),
			),
			'heading'      => array(
				'title'       => __( 'Email heading', 'wc-wholesale-manager' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => $placeholder_text,
				'placeholder' => $this->get_default_heading(),
				'default'     => $this->get_default_heading(),
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
