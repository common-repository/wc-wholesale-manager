<?php

namespace WooCommerceWholesaleManager;

defined( 'ABSPATH' ) || exit();

/**
 * Class Emails.
 *
 * Handles email related actions.
 *
 * @since  1.0.0
 * @package  WooCommerceWholesaleManager
 */
class Emails {

	/**
	 * Emails constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_filter( 'woocommerce_email_classes', array( $this, 'register_email_classes' ) );
		add_filter( 'woocommerce_email_actions', array( $this, 'register_email_actions' ) );
		add_action( 'current_screen', array( $this, 'init_wc_emails' ) );
	}

	/**
	 * Registers the wholesale emails with WooCommerce
	 *
	 * @param \WC_Email[] $email_classes The email classes.
	 *
	 * @since  1.0.0
	 * @return  \WC_Email[] $email_classes The email classes.
	 * @package  WooCommerceB2B
	 */
	public function register_email_classes( $email_classes ) {
		require_once __DIR__ . '/Emails/class-wcwm-email.php';
		require_once __DIR__ . '/Emails/class-wcwm-admin-new-user.php';
		require_once __DIR__ . '/Emails/class-wcwm-new-user.php';
		require_once __DIR__ . '/Emails/class-wcwm-wholesaler-approved.php';
		require_once __DIR__ . '/Emails/class-wcwm-wholesaler-declined.php';
		require_once __DIR__ . '/Emails/class-wcwm-wholesaler-pending.php';
		$email_classes['WCWM_Admin_New_User']      = new \WCWM_Admin_New_User();
		$email_classes['WCWM_Wholesaler_New_User'] = new \WCWM_Wholesaler_New_User();
		$email_classes['WCWM_Wholesaler_Approved'] = new \WCWM_Wholesaler_Approved();
		$email_classes['WCWM_Wholesaler_Declined'] = new \WCWM_Wholesaler_Declined();
		$email_classes['WCWM_Wholesaler_Pending']  = new \WCWM_Wholesaler_Pending();

		return $email_classes;
	}

	/**
	 * Register the actions which trigger the mails
	 *
	 * @param array $actions The actions.
	 *
	 * @since  1.0.0
	 * @return array $new_actions The new actions.
	 * @package  WooCommerceB2B
	 */
	public function register_email_actions( $actions ) {
		$new_actions = array(
			'wc_wholesale_manager_auto_approved_wholesale_user',
			'wc_wholesale_manager_pending_wholesale_user',
			'wc_wholesale_manager_approved_wholesale_user',
			'wc_wholesale_manager_declined_wholesale_user',
		);

		return array_merge( $actions, $new_actions );
	}

	/**
	 * Initialize WooCommerce Emails.
	 * This is required to make sure WooCommerce emails are loaded.
	 *
	 * @param \WP_Screen $current_screen The current screen.
	 *
	 * @since  1.0.0
	 */
	public function init_wc_emails( $current_screen ) {
		if ( 'user' !== $current_screen->base ) {
			return;
		}

		\WC_Emails::instance();
	}
}
