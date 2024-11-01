<?php

namespace WooCommerceWholesaleManager\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Settings.
 *
 * @since   1.0.0
 * @package WooCommerceWholesaleManager\Admin
 */
class Settings extends \WooCommerceWholesaleManager\Lib\Settings {

	/**
	 * Get settings tabs.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_tabs() {
		$tabs = array(
			'general'  => __( 'General', 'wc-wholesale-manager' ),
			'roles'    => __( 'Roles', 'wc-wholesale-manager' ),
			'emails'   => __( 'Emails', 'wc-wholesale-manager' ),
			'fields'   => __( 'Registration Fields', 'wc-wholesale-manager' ),
			'advanced' => __( 'Advanced', 'wc-wholesale-manager' ),
		);

		return apply_filters( 'wc_wholesale_manager_settings_tabs', $tabs );
	}

	/**
	 * Get settings.
	 *
	 * @param string $tab Current tab.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_settings( $tab ) {
		$settings = array();

		switch ( $tab ) {
			case 'general':
				$settings = array(
					array(
						'title' => __( 'General Settings', 'wc-wholesale-manager' ),
						'type'  => 'title',
						'desc'  => __( 'The following options are used to configure the plugin.', 'wc-wholesale-manager' ),
						'id'    => 'general_options',
					),
					// Registration page.
					array(
						'title'    => __( 'Registration Page', 'wc-wholesale-manager' ),
						'desc'     => __( 'Select the page where you have placed the [wholesale_registration_form] shortcode.', 'wc-wholesale-manager' ),
						'desc_tip' => __( 'Wholesale registration page', 'wc-wholesale-manager' ),
						'id'       => 'wcwm_registration_page_id',
						'type'     => 'single_select_page',
						'class'    => 'wc-enhanced-select',
						'css'      => 'min-width:300px;',
					),
					// Disable auto approval.
					array(
						'title'    => __( 'Disable Auto Approval', 'wc-wholesale-manager' ),
						'desc'     => __( 'Disable auto approval of wholesale user registration.', 'wc-wholesale-manager' ),
						'desc_tip' => __( 'If enabled, the admin will have to manually approve the wholesale user registration.', 'wc-wholesale-manager' ),
						'id'       => 'wcwm_disable_auto_approval',
						'type'     => 'checkbox',
						'default'  => 'no',
					),
					// Disable tax.
					array(
						'title'    => __( 'Disable Tax', 'wc-wholesale-manager' ),
						'desc'     => __( 'Disable tax collection for wholesale users.', 'wc-wholesale-manager' ),
						'desc_tip' => __( 'You can individually enable/disable tax collection for each wholesale role.', 'wc-wholesale-manager' ),
						'id'       => 'wcwm_disable_tax',
						'type'     => 'checkbox',
						'default'  => 'no',
					),
					// Disable coupon.
					array(
						'title'    => __( 'Disable Coupon', 'wc-wholesale-manager' ),
						'desc'     => __( 'Disable coupon for wholesale users.', 'wc-wholesale-manager' ),
						'desc_tip' => __( 'Whole sale users will not be able to use coupons.', 'wc-wholesale-manager' ),
						'id'       => 'wcwm_disable_coupon',
						'type'     => 'checkbox',
						'default'  => 'no',
					),
					// Wholesaler only store.
					array(
						'title'    => __( 'Wholesale Only Store', 'wc-wholesale-manager' ),
						'desc'     => __( 'Enable wholesale only store.', 'wc-wholesale-manager' ),
						'desc_tip' => __( 'If enabled, only logged in wholesale users will be able to access the store.', 'wc-wholesale-manager' ),
						'id'       => 'wcwm_wholesale_only_store',
						'type'     => 'checkbox',
						'default'  => 'no',
					),
					// Admin as wholesale user.
					array(
						'title'             => __( 'Admin as Wholesale User', 'wc-wholesale-manager' ),
						'desc'              => __( 'Enable admin as wholesale user.', 'wc-wholesale-manager' ),
						'desc_tip'          => __( 'If enabled, admin will be treated as a wholesale user.', 'wc-wholesale-manager' ),
						'id'                => 'wcwm_admin_is_wholesaler',
						'type'              => 'checkbox',
						'default'           => 'no',
						'custom_attributes' => array(
							'data-cond-id'    => 'wcwm_wholesale_only_store',
							'data-cond-value' => 'checked',
						),
					),
					// End of general options.
					array(
						'type' => 'sectionend',
						'id'   => 'general_options',
					),
				);
				break;
			case 'emails':
				$settings = array(
					array(
						'title' => __( 'Email Settings', 'wc-wholesale-manager' ),
						'type'  => 'title',
						'desc'  => __( 'Email notifications sent from WooCommerce Wholesale Manager are listed below. You can customize the subject line and/or message body of each email.', 'wc-wholesale-manager' ),
						'id'    => 'email_options',
					),

					array(
						'id'   => 'wcwm_email_settings',
						'type' => 'wcwm_email_settings',
					),
					// End of email options.
					array(
						'type' => 'sectionend',
						'id'   => 'email_options',
					),
				);
				break;
			case 'advanced':
				$settings = array(
					array(
						'title' => __( 'Advanced Settings', 'wc-wholesale-manager' ),
						'type'  => 'title',
						'desc'  => __( 'The following options are the plugin advanced settings.', 'wc-wholesale-manager' ),
						'id'    => 'advanced_options',
					),
					array(
						'title'    => __( 'Delete plugin data', 'wc-wholesale-manager' ),
						'desc'     => __( 'Delete plugin data.', 'wc-wholesale-manager' ),
						'desc_tip' => __( 'Enabling this will delete all the data while uninstalling the plugin.', 'wc-wholesale-manager' ),
						'id'       => 'wcwm_delete_data',
						'default'  => 'no',
						'type'     => 'checkbox',
					),
					array(
						'type' => 'sectionend',
						'id'   => 'advanced_options',
					),
				);
				break;
		}

		return apply_filters( 'wc_wholesale_manager_get_settings_' . $tab, $settings );
	}

	/**
	 * Output settings form.
	 *
	 * @param array $settings Settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function output_form( $settings ) {
		$current_tab = $this->get_current_tab();
		/**
		 * Action hook to output settings form.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wc_wholesale_manager_settings_' . $current_tab );

		parent::output_form( $settings );
	}

	/**
	 * Output premium widget.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function output_premium_widget() {
		if ( wc_wholesale_manager()->is_premium_active() ) {
			return;
		}
		$features = array(
			__( 'Wholesale registration field customization with the different types of input field types.', 'wc-wholesale-manager' ),
			__( 'Unlimited wholesale roles.', 'wc-wholesale-manager' ),
			__( 'And many more ...', 'wc-wholesale-manager' ),
		);
		?>
		<div class="pev-panel promo-panel">
			<h3><?php esc_html_e( 'Want More?', 'wc-wholesale-manager' ); ?></h3>
			<p><?php esc_attr_e( 'This plugin offers a premium version which comes with the following features:', 'wc-wholesale-manager' ); ?></p>
			<ul>
				<?php foreach ( $features as $feature ) : ?>
					<li>- <?php echo esc_html( $feature ); ?></li>
				<?php endforeach; ?>
			</ul>
			<a href="https://pluginever.com/plugins/wc-wholesale-manager/?utm_source=plugin-settings&utm_medium=banner&utm_campaign=upgrade&utm_id=wc-wholesale-manager" class="button" target="_blank">
				<?php esc_html_e( 'Upgrade to PRO', 'wc-wholesale-manager' ); ?>
			</a>
		</div>
		<?php
	}

	/**
	 * Output tabs.
	 *
	 * @param array $tabs Tabs.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function output_tabs( $tabs ) {
		parent::output_tabs( $tabs );
		if ( wc_wholesale_manager()->get_docs_url() ) {
			printf( '<a href="%s" class="nav-tab" target="_blank">%s</a>', esc_url( wc_wholesale_manager()->get_docs_url() ), esc_html__( 'Documentation', 'wc-wholesale-manager' ) );
		}
	}
}
