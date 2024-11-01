<?php

namespace WooCommerceWholesaleManager;

defined( 'ABSPATH' ) || exit;

/**
 * Class Installer.
 *
 * @since   1.0.0
 * @package WooCommerceWholesaleManager
 */
class Installer {

	/**
	 * Update callbacks.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $updates = array();

	/**
	 * Installer constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'check_update' ), 5 );
	}

	/**
	 * Check the plugin version and run the updater if necessary.
	 *
	 * This check is done on all requests and runs if the versions do not match.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function check_update() {
		$db_version      = wc_wholesale_manager()->get_db_version();
		$current_version = wc_wholesale_manager()->get_version();
		$requires_update = version_compare( $db_version, $current_version, '<' );
		$can_install     = ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && ! defined( 'IFRAME_REQUEST' );
		if ( $can_install && $requires_update ) {
			static::install();

			$update_versions = array_keys( $this->updates );
			usort( $update_versions, 'version_compare' );
			if ( ! is_null( $db_version ) && version_compare( $db_version, end( $update_versions ), '<' ) ) {
				$this->update();
			} else {
				wc_wholesale_manager()->update_db_version( $current_version );
			}
		}
	}

	/**
	 * Update the plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function update() {
		$db_version = wc_wholesale_manager()->get_db_version();
		foreach ( $this->updates as $version => $callbacks ) {
			$callbacks = (array) $callbacks;
			if ( version_compare( $db_version, $version, '<' ) ) {
				foreach ( $callbacks as $callback ) {
					wc_wholesale_manager()->log( sprintf( 'Updating to %s from %s', $version, $db_version ) );
					// if the callback return false then we need to update the db version.
					$continue = call_user_func( array( $this, $callback ) );
					if ( ! $continue ) {
						wc_wholesale_manager()->update_db_version( $version );
						$notice = sprintf(
						/* translators: 1: plugin name 2: version number */
							__( '%1$s updated to version %2$s successfully.', 'wc-wholesale-manager' ),
							'<strong>' . wc_wholesale_manager()->get_name() . '</strong>',
							'<strong>' . $version . '</strong>'
						);
						wc_wholesale_manager()->add_notice( $notice, 'success' );
					}
				}
			}
		}
	}

	/**
	 * Install the plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function install() {
		if ( ! is_blog_installed() ) {
			return;
		}

		// Check if a page with shortcode [wc_b2b_registration_form] exists. if not, create one.
		$registration_page    = get_page_by_path( 'wholesale-registration', OBJECT, 'page' );
		$registration_page_id = get_option( 'wcwm_registration_page_id' );
		if ( ! $registration_page && ! $registration_page_id ) {
			$registration_page_id = wp_insert_post(
				array(
					'post_title'     => __( 'Wholesale Registration', 'wc-wholesale-manager' ),
					'post_content'   => '[wholesale_registration_form]',
					'post_status'    => 'publish',
					'post_type'      => 'page',
					'comment_status' => 'closed',
				)
			);
			update_option( 'wcwm_registration_page_id', $registration_page_id );
		}

		// create tables here.
		Admin\Settings::instance()->save_defaults();
		wc_wholesale_manager()->update_db_version( wc_wholesale_manager()->get_version(), false );
		add_option( 'wcwm_install_date', current_time( 'mysql' ) );
		set_transient( 'wcwm_activated', true, 30 );
		set_transient( 'wcwm_activation_redirect', true, 30 );
	}
}
