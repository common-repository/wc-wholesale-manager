<?php

namespace WooCommerceWholesaleManager\Admin;

use WooCommerceWholesaleManager\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Admin class.
 *
 * @since 1.0.0
 * @package WooCommerceWholesaleManager
 */
class Admin {

	/**
	 * Admin constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ), 1 );
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 55 );
		add_filter( 'woocommerce_screen_ids', array( $this, 'screen_ids' ) );
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), PHP_INT_MAX );
		add_filter( 'update_footer', array( $this, 'update_footer' ), PHP_INT_MAX );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'wc_wholesale_manager_settings_fields', array( __CLASS__, 'render_registration_fields' ) );
		add_action( 'woocommerce_admin_field_wcwm_email_settings', array( $this, 'render_email_settings' ) );
		add_action( 'wc_wholesale_manager_settings_roles', array( __CLASS__, 'render_roles_tab' ) );
		add_action( 'woocommerce_product_options_pricing', array( $this, 'product_fields' ), 1, 0 );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_products_data' ), 10, 1 );
		add_action( 'product_cat_add_form_fields', array( $this, 'add_category_fields' ), 20 );
		add_action( 'product_cat_edit_form_fields', array( $this, 'edit_category_fields' ), 20, 1 );
		add_action( 'created_product_cat', array( $this, 'save_category_fields' ), 10, 1 );
		add_action( 'edited_product_cat', array( $this, 'save_category_fields' ), 10, 1 );
	}

	/**
	 * Init.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		wc_wholesale_manager()->services->add( Settings::instance() );
	}

	/**
	 * Add the plugin menu.
	 *
	 * @since 1.0.0
	 */
	public function admin_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'Wholesale', 'wc-wholesale-manager' ),
			__( 'Wholesale', 'wc-wholesale-manager' ),
			'manage_options',
			'wc-wholesale-manager',
			array( Settings::class, 'output' )
		);
	}

	/**
	 * Add the plugin screens to the WooCommerce screens.
	 * This will load the WooCommerce admin styles and scripts.
	 *
	 * @param array $ids Screen ids.
	 *
	 * @return array
	 */
	public function screen_ids( $ids ) {
		return array_merge( $ids, self::get_screen_ids() );
	}

	/**
	 * Admin footer text.
	 *
	 * @param string $footer_text Footer text.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function admin_footer_text( $footer_text ) {
		if ( wc_wholesale_manager()->get_review_url() && in_array( get_current_screen()->id, self::get_screen_ids(), true ) ) {
			$footer_text = sprintf(
			/* translators: 1: Plugin name 2: WordPress */
				__( 'Thank you for using %1$s. If you like it, please leave us a %2$s rating. A huge thank you from PluginEver in advance!', 'wc-wholesale-manager' ),
				'<strong>' . esc_html( wc_wholesale_manager()->get_name() ) . '</strong>',
				'<a href="' . esc_url( wc_wholesale_manager()->get_review_url() ) . '" target="_blank" class="wc-wholesale-manager-rating-link" data-rated="' . esc_attr__( 'Thanks :)', 'wc-wholesale-manager' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
			);
		}

		return $footer_text;
	}

	/**
	 * Update footer.
	 *
	 * @param string $footer_text Footer text.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function update_footer( $footer_text ) {
		if ( in_array( get_current_screen()->id, self::get_screen_ids(), true ) ) {
			/* translators: 1: Plugin version */
			$footer_text = sprintf( esc_html__( 'Version %s', 'wc-wholesale-manager' ), wc_wholesale_manager()->get_version() );
		}

		return $footer_text;
	}

	/**
	 * Get screen ids.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function get_screen_ids() {
		$screen_ids = array(
			'woocommerce_page_wc-wholesale-manager',
		);

		return apply_filters( 'wc_wholesale_manager_screen_ids', $screen_ids );
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @param string $hook Hook name.
	 *
	 * @since 1.0.0
	 */
	public function admin_scripts( $hook ) {
		$screen_ids = self::get_screen_ids();
		wc_wholesale_manager()->register_style( 'wcwm-admin', 'css/wcwm-admin.css' );
		wc_wholesale_manager()->enqueue_style( 'wcwn-halloween', 'css/wcwm-halloween.css' );

		if ( in_array( $hook, $screen_ids, true ) ) {
			wp_enqueue_style( 'wcwm-admin' );
			wp_enqueue_script( 'wc-enhanced-select' );
			wp_enqueue_style( 'woocommerce_admin_styles' );
		}

		if ( 'term.php' === $hook ) {
			// Add inline style.
			wp_add_inline_style( 'common', '.wcwm-role-based-discounts-table th {padding:8px 10px !important; ;}' );
		}
	}

	/**
	 * Render roles tab.
	 *
	 * @throws \Exception Throws exception if the request is invalid.
	 * @since 1.0.0
	 * @return void
	 */
	public static function render_roles_tab() {
		if ( isset( $_GET['edit'] ) ) {
			$id   = filter_input( INPUT_GET, 'edit', FILTER_SANITIZE_NUMBER_INT );
			$role = Helper::get_wholesaler_role( $id );
			if ( empty( $id ) || empty( $role->id ) ) {
				wp_safe_redirect( remove_query_arg( 'edit' ) );
				exit();
			}

			try {
				if ( ! empty( $_POST ) && ! check_admin_referer( 'wcwm_save_role' ) ) {
					throw new \Exception( __( 'Error - please try again', 'wc-wholesale-manager' ) );
				}

				if ( isset( $_POST['submit'] ) ) {
					$name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
					if ( empty( $name ) ) {
						throw new \Exception( __( 'Please enter role name.', 'wc-wholesale-manager' ) );
					}

					$metas = array(
						'discount'                  => filter_input( INPUT_POST, 'discount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ),
						'discount_type'             => isset( $_POST['discount_type'] ) ? sanitize_text_field( wp_unslash( $_POST['discount_type'] ) ) : null,
						'auto_approval'             => isset( $_POST['auto_approval'] ) ? sanitize_text_field( wp_unslash( $_POST['auto_approval'] ) ) : null,
						'order_minimum'             => filter_input( INPUT_POST, 'order_minimum', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ),
						'product_pricing'           => isset( $_POST['product_pricing'] ) ? sanitize_text_field( wp_unslash( $_POST['product_pricing'] ) ) : null,
						'category_pricing'          => isset( $_POST['category_pricing'] ) ? sanitize_text_field( wp_unslash( $_POST['category_pricing'] ) ) : null,
						'tax_status'                => isset( $_POST['tax_status'] ) ? sanitize_text_field( wp_unslash( $_POST['tax_status'] ) ) : 'inherited',
						'tax_display'               => isset( $_POST['tax_display'] ) ? sanitize_text_field( wp_unslash( $_POST['tax_display'] ) ) : 'inherited',
						'disabled_gateways'         => isset( $_POST['disabled_gateways'] ) ? array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['disabled_gateways'] ) ) : array(),
						'disabled_shipping_methods' => isset( $_POST['disabled_shipping_methods'] ) ? array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['disabled_shipping_methods'] ) ) : array(),
					);

					if ( empty( $metas['discount_type'] ) || ! in_array( $metas['discount_type'], array( 'percentage', 'fixed' ), true ) ) {
						throw new \Exception( __( 'Please select valid discount type.', 'wc-wholesale-manager' ) );
					}

					if ( ! in_array( $metas['tax_status'], array( 'inherit', 'enable', 'disable' ), true ) ) {
						$metas['tax_status'] = 'inherit';
					}

					if ( ! in_array( $metas['tax_display'], array( 'inherit', 'excl', 'incl' ), true ) ) {
						$metas['tax_display'] = 'inherit';
					}

					$redirect_to = admin_url( 'admin.php?page=wc-wholesale-manager&tab=roles' );
					$updated     = wp_update_term(
						$role->id,
						'wcwm_role',
						array(
							'name'        => $name,
							'description' => '',
						)
					);
					if ( is_wp_error( $updated ) ) {
						throw new \Exception( $updated->get_error_message() );
					}

					// Update metas.
					foreach ( $metas as $meta_key => $meta_value ) {
						update_term_meta( $role->id, "_$meta_key", $meta_value );
					}

					wc_wholesale_manager()->add_notice( __( 'Role updated successfully.', 'wc-wholesale-manager' ), 'success' );

					wp_safe_redirect(
						add_query_arg(
							array(
								'edit' => $role->id,
							),
							$redirect_to
						)
					);
					exit();
				}
			} catch ( \Exception $e ) {
				wc_wholesale_manager()->add_notice( $e->getMessage(), 'error' );
			}

			include __DIR__ . '/views/edit-role.php';

			return;
		}

		include __DIR__ . '/views/role.php';
	}

	/**
	 * Render emails tab.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_email_settings() {
		$mailer           = WC()->mailer();
		$email_templates  = $mailer->get_emails();
		$wholesale_emails = array_filter(
			$email_templates,
			function ( $email ) {
				// Check if class name contains 'WCWM'.
				return false !== strpos( get_class( $email ), 'WCWM_' );
			}
		);

		?>
		<tr valign="top">
			<td class="wc_emails_wrapper" colspan="2">
				<table class="wc_emails widefat" cellspacing="0">
					<thead>
					<tr>
						<?php
						$columns = apply_filters(
							'woocommerce_email_setting_columns',
							array(
								'status'     => '',
								'name'       => __( 'Email', 'wc-wholesale-manager' ),
								'email_type' => __( 'Content type', 'wc-wholesale-manager' ),
								'recipient'  => __( 'Recipient(s)', 'wc-wholesale-manager' ),
								'actions'    => '',
							)
						);
						foreach ( $columns as $key => $column ) {
							echo '<th class="wc-email-settings-table-' . esc_attr( $key ) . '">' . esc_html( $column ) . '</th>';
						}
						?>
					</tr>
					<tbody>
					<?php

					foreach ( $wholesale_emails as $email_key => $email ) {
						echo '<tr>';

						$manage_url = add_query_arg(
							array(
								'section'   => strtolower( $email_key ),
								'wholesale' => 'true',
							),
							admin_url( 'admin.php?page=wc-settings&tab=email' )
						);

						foreach ( $columns as $key => $column ) {

							switch ( $key ) {
								case 'name':
									echo '<td class="wc-email-settings-table-' . esc_attr( $key ) . '">
										<a href="' . esc_url( $manage_url ) . '">' . esc_html( $email->get_title() ) . '</a>' . wp_kses_post( wc_help_tip( $email->get_description() ) ) . '</td>';
									break;
								case 'recipient':
									echo '<td class="wc-email-settings-table-' . esc_attr( $key ) . '">' . esc_html( $email->is_customer_email() ? __( 'Customer', 'wc-wholesale-manager' ) : $email->get_recipient() ) . '</td>';
									break;
								case 'status':
									echo '<td class="wc-email-settings-table-' . esc_attr( $key ) . '">';

									if ( $email->is_manual() ) {
										echo '<span class="status-manual tips" data-tip="' . esc_attr__( 'Manually sent', 'wc-wholesale-manager' ) . '">' . esc_html__( 'Manual', 'wc-wholesale-manager' ) . '</span>';
									} elseif ( $email->is_enabled() ) {
										echo '<span class="status-enabled tips" data-tip="' . esc_attr__( 'Enabled', 'wc-wholesale-manager' ) . '">' . esc_html__( 'Yes', 'wc-wholesale-manager' ) . '</span>';
									} else {
										echo '<span class="status-disabled tips" data-tip="' . esc_attr__( 'Disabled', 'wc-wholesale-manager' ) . '">-</span>';
									}

									echo '</td>';
									break;
								case 'email_type':
									echo '<td class="wc-email-settings-table-' . esc_attr( $key ) . '">
											' . esc_html( $email->get_content_type() ) . '
										</td>';
									break;
								case 'actions':
									echo '<td class="wc-email-settings-table-' . esc_attr( $key ) . '">
											<a class="button alignright" href="' . esc_url( $manage_url ) . '">' . esc_html__( 'Manage', 'wc-wholesale-manager' ) . '</a>
										</td>';
									break;
								default:
									do_action( 'woocommerce_email_setting_column_' . $key, $email );
									break;
							}
						}

						echo '</tr>';
					}
					?>
					</tbody>
				</table>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render fields tab.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function render_registration_fields() {
		?>
		<div class="wcwm-registration-fields-promo-banner">
			<div class="wcwm-registration-fields-promo-banner__content">
				<h3><?php esc_html_e( 'Available in Pro Version', 'wc-wholesale-manager' ); ?></h3>
				<a href="https://pluginever.com/plugins/wc-wholesale-manager-pro/?utm_source=import-tab&utm_medium=link&utm_campaign=upgrade&utm_id=wc-wholesale-manager" target="_blank" class="button-primary"><?php esc_html_e( 'Upgrade to Pro Now', 'wc-wholesale-manager' ); ?></a>
			</div>
			<img src="<?php echo esc_url( WCWM_ASSETS_URL . 'images/registration-fields.png' ); ?>" alt="<?php esc_attr_e( 'Registration Fields', 'wc-wholesale-manager' ); ?>"/>
		</div>
		<?php
	}

	/**
	 * Add fields to product general tab.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function product_fields() {
		$product = wc_get_product( get_the_ID() );
		// If not simple product, return.
		if ( ! $product || ! $product->is_type( 'simple' ) ) {
			return;
		}
		$visibilities = Helper::get_visibility_options();
		$discounts    = get_post_meta( get_the_ID(), '_wcwm_discounts', true );
		$value        = get_post_meta( get_the_ID(), '_wcwm_visibility', true );
		$discounts    = is_array( $discounts ) ? $discounts : array();
		$roles        = Helper::get_wholesaler_roles();
		$roles        = wp_list_filter( $roles, array( 'product_pricing' => 'yes' ) );
		wp_nonce_field( 'wcwm_save_product_fields', 'wcwm_save_product_fields_nonce' );
		woocommerce_wp_radio(
			array(
				'id'          => '_wcwm_visibility',
				'description' => __( 'Select the visibility of the product public will be visible to all users, public only will be visible to public users only, wholesale only will be visible to wholesale users only.', 'wc-wholesale-manager' ),
				'desc_tip'    => true,
				'label'       => __( 'Visibility', 'wc-wholesale-manager' ),
				'options'     => $visibilities,
				'value'       => empty( $value ) ? 'public' : $value,
			)
		);
		if ( ! empty( $roles ) ) :
			?>
			<fieldset class="form-field _wcwm_discounts_field">
				<label for="_wcwm_discounts"><?php esc_html_e( 'Role based discount', 'wc-wholesale-manager' ); ?></label>
				<?php echo wp_kses_post( wc_help_tip( esc_html__( 'If if you set a discount for a role, it will override the category wise & global discount.', 'wc-wholesale-manager' ) ) ); ?>
				<?php include __DIR__ . '/views/discounts-table.php'; ?>
			</fieldset>
			<?php
		endif;
	}

	/**
	 * Save product data.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function save_products_data( $post_id ) {
		$product = wc_get_product( $post_id );
		// If not simple product, return.
		if ( ! $product || ! $product->is_type( 'simple' ) ) {
			return;
		}
		check_admin_referer( 'wcwm_save_product_fields', 'wcwm_save_product_fields_nonce' );
		$discounts  = isset( $_POST['_wcwm_discounts'] ) ? map_deep( wp_unslash( $_POST['_wcwm_discounts'] ), 'sanitize_text_field' ) : array();
		$visibility = isset( $_POST['_wcwm_visibility'] ) ? sanitize_text_field( wp_unslash( $_POST['_wcwm_visibility'] ) ) : null;

		if ( ! empty( $discounts ) ) {
			$roles = Helper::get_wholesaler_roles();
			$roles = wp_list_filter( $roles, array( 'product_pricing' => 'yes' ) );
			$roles = wp_list_pluck( $roles, 'slug' );
			// Remove discount those are empty.
			foreach ( $discounts as $key => $discount ) {
				if ( ! in_array( $key, $roles, true ) || empty( $discount['discount'] ) ) {
					unset( $discounts[ $key ] );
				}
				// Sanitize discount.
				$discounts[ $key ]['discount'] = wc_format_decimal( $discount['discount'] );
				// Sanitize discount type.
				$discounts[ $key ]['type']    = sanitize_text_field( $discount['type'] );
				$discounts[ $key ]['enabled'] = isset( $discount['enabled'] ) ? 'yes' : 'no';
				$discounts[ $key ]['role']    = $key;
			}
		}

		if ( ! empty( $discounts ) ) {
			update_post_meta( $post_id, '_wcwm_discounts', $discounts );
		} else {
			delete_post_meta( $post_id, '_wcwm_discounts' );
		}

		if ( ! empty( $visibility ) ) {
			update_post_meta( $post_id, '_wcwm_visibility', $visibility );
		} else {
			delete_post_meta( $post_id, '_wcwm_visibility' );
		}
	}

	/**
	 * Add fields to category edit page.
	 *
	 * @since 1.0.0
	 */
	public function add_category_fields() {
		$roles        = Helper::get_wholesaler_roles();
		$roles        = wp_list_filter( $roles, array( 'category_pricing' => 'yes' ) );
		$visibility   = 'public';
		$discounts    = array();
		$visibilities = Helper::get_visibility_options();
		?>
		<h2><?php esc_html_e( 'Wholesale Options', 'wc-wholesale-manager' ); ?></h2>
		<?php
		woocommerce_wp_radio(
			array(
				'id'          => '_wcwm_visibility',
				'description' => __( 'Select the visibility of the product public will be visible to all users, public only will be visible to public users only, wholesale only will be visible to wholesale users only.', 'wc-wholesale-manager' ),
				'label'       => __( 'Visibility', 'wc-wholesale-manager' ),
				'options'     => $visibilities,
				'value'       => 'public',
			)
		);
		if ( ! empty( $roles ) ) :
			?>
			<fieldset class="form-field _wcwm_discounts_field">
				<label for="_wcwm_discounts"><?php esc_html_e( 'Role based discount', 'wc-wholesale-manager' ); ?></label>
				<?php include __DIR__ . '/views/discounts-table.php'; ?>
				<p class="description"><?php echo esc_html__( 'If if you set a discount for a role, it will override the global discount for this role.', 'wc-wholesale-manager' ); ?></p>
			</fieldset>
			<?php
		endif;
	}

	/**
	 * Edit fields to category edit page.
	 *
	 * @param object $term Term object.
	 *
	 * @since 1.0.0
	 */
	public function edit_category_fields( $term ) {
		$roles        = Helper::get_wholesaler_roles();
		$roles        = wp_list_filter( $roles, array( 'category_pricing' => 'yes' ) );
		$visibility   = get_term_meta( $term->term_id, '_wcwm_visibility', true );
		$discounts    = get_term_meta( $term->term_id, '_wcwm_discounts', true );
		$visibilities = Helper::get_visibility_options();
		$discounts    = is_array( $discounts ) ? $discounts : array();
		$visibility   = empty( $visibility ) ? 'public' : $visibility;
		?>
		<?php wp_nonce_field( 'wcwm_save_category', 'wcwm_save_category_nonce' ); ?>
		<tr class="form-field">
			<th scope="row" valign="top"><label><?php esc_html_e( 'Visibility', 'wc-wholesale-manager' ); ?></label></th>
			<td>
				<?php
				woocommerce_wp_radio(
					array(
						'id'          => '_wcwm_visibility',
						'description' => __( 'Select the visibility of the product public will be visible to all users, public only will be visible to public users only, wholesale only will be visible to wholesale users only.', 'wc-wholesale-manager' ),
						'label'       => '',
						'options'     => $visibilities,
						'value'       => $visibility,
					)
				);
				?>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top"><label><?php esc_html_e( 'Role based discount', 'wc-wholesale-manager' ); ?></label></th>
			<td>
				<?php include __DIR__ . '/views/discounts-table.php'; ?>
				<p class="description"><?php echo esc_html__( 'If if you set a discount for a role, it will override the global discount for this role.', 'wc-wholesale-manager' ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Save category fields.
	 *
	 * @param int $term_id Term ID.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function save_category_fields( $term_id ) {
		check_admin_referer( 'wcwm_save_category', 'wcwm_save_category_nonce' );
		$discounts  = isset( $_POST['_wcwm_discounts'] ) ? map_deep( wp_unslash( $_POST['_wcwm_discounts'] ), 'sanitize_text_field' ) : array();
		$visibility = isset( $_POST['_wcwm_visibility'] ) ? sanitize_text_field( wp_unslash( $_POST['_wcwm_visibility'] ) ) : null;

		if ( ! empty( $discounts ) ) {
			$roles = Helper::get_wholesaler_roles();
			$roles = wp_list_filter( $roles, array( 'product_pricing' => 'yes' ) );
			$roles = wp_list_pluck( $roles, 'slug' );
			// Remove discount those are empty.
			foreach ( $discounts as $key => $discount ) {
				if ( ! in_array( $key, $roles, true ) || empty( $discount['discount'] ) ) {
					unset( $discounts[ $key ] );
				}
				// Sanitize discount.
				$discounts[ $key ]['discount'] = wc_format_decimal( $discount['discount'] );
				// Sanitize discount type.
				$discounts[ $key ]['type']    = sanitize_text_field( $discount['type'] );
				$discounts[ $key ]['enabled'] = isset( $discount['enabled'] ) ? 'yes' : 'no';
				$discounts[ $key ]['role']    = $key;
			}
		}

		if ( ! empty( $discounts ) ) {
			update_term_meta( $term_id, '_wcwm_discounts', $discounts );
		} else {
			delete_term_meta( $term_id, '_wcwm_discounts' );
		}

		if ( ! empty( $visibility ) ) {
			update_term_meta( $term_id, '_wcwm_visibility', $visibility );
		} else {
			delete_term_meta( $term_id, '_wcwm_visibility' );
		}
	}
}
