<?php

namespace WooCommerceWholesaleManager;

defined( 'ABSPATH' ) || exit;

/**
 * Class Roles.
 *
 * Handles the roles.
 *
 * @since 1.0.0
 * @package WooCommerceWholesaleManager
 */
class Roles {

	/**
	 * Roles constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$wp_roles = new \WP_Roles();
		// Roles related hooks.
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ) );
		add_action( 'created_wcwm_role', array( __CLASS__, 'created_role' ), 10, 1 );
		add_action( 'delete_wcwm_role', array( __CLASS__, 'deleted_role' ), 10, 3 );
		add_filter( 'pre_update_option_' . $wp_roles->role_key, array( __CLASS__, 'handle_role_deletion' ), 10, 2 );
	}


	/**
	 * Register taxonomies.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function register_taxonomies() {
		// Register a hidden taxonomy for wholesale roles.
		$args = array(
			'public'             => false,
			'show_ui'            => false,
			'show_in_nav_menus'  => false,
			'show_in_menu'       => false,
			'show_in_quick_edit' => false,
			'show_admin_column'  => false,
			'show_in_rest'       => false,
			'hierarchical'       => false,
			'query_var'          => false,
			'rewrite'            => false,
			'capabilities'       => array(
				'manage_terms' => 'manage_woocommerce',
				'edit_terms'   => 'manage_woocommerce',
				'delete_terms' => 'manage_woocommerce',
				'assign_terms' => 'edit_products',
			),
		);
		register_taxonomy( 'wcwm_role', 'user', $args );
		if ( ! Helper::get_default_wholesaler_role() ) {
			$w_term = wp_insert_term( 'Wholesaler', 'wcwm_role', array( 'slug' => 'wcwm_role_default' ) );
			if ( ! is_wp_error( $w_term ) ) {
				update_term_meta( $w_term['term_id'], '_product_pricing', 'yes' );
				update_term_meta( $w_term['term_id'], '_category_pricing', 'yes' );
			}
		}

		// Pending wholesale role.
		if ( ! Helper::get_pending_wholesaler_role() ) {
			wp_insert_term( 'Pending', 'wcwm_role', array( 'slug' => 'wcwm_role_pending' ) );
		}

		$default_role = Helper::get_default_wholesaler_role();
		$pending_role = Helper::get_pending_wholesaler_role();

		// Now check if we have these roles in the user roles otherise add them.
		$wp_roles = wp_roles();
		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new \WP_Roles();
		}

		if ( $default_role && ! isset( $wp_roles->roles[ $default_role->slug ] ) ) {
			add_role(
				$default_role->slug,
				'Wholesaler - Wholesaler',
				array(
					'read'    => true,
					'level_0' => true,
				)
			);
		}

		if ( $pending_role && ! isset( $wp_roles->roles[ $pending_role->slug ] ) ) {
			add_role(
				$pending_role->slug,
				'Pending - Wholesaler',
				array(
					'read' => false,
				)
			);
		}
	}

	/**
	 * Created a new wholesale role.
	 *
	 * @param int $term_id Term ID.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function created_role( $term_id ) {
		$term = get_term( $term_id, 'wcwm_role' );
		if ( ! wp_roles()->is_role( $term->slug ) ) {
			add_role(
				$term->slug,
				$term->name . esc_html__( ' - Wholesaler', 'wc-wholesale-manager' ),
				array(
					'read'    => true,
					'level_0' => true,
				)
			);
		}
	}

	/**
	 * Delete a wholesale role.
	 *
	 * @param int      $term_id Term ID.
	 * @param int      $tt_id Term taxonomy ID.
	 * @param \WP_Term $term Copy of the already-deleted term.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function deleted_role( $term_id, $tt_id, $term ) {
		if ( wp_roles()->is_role( $term->slug ) ) {
			remove_role( $term->slug );
		}
	}

	/**
	 * Monitors if a role has been deleted.
	 * There is no WP filter/hook for this, so we need to check pre_update_option
	 *
	 * @param array $value The new, serialized option value.
	 * @param array $old_value The old option value.
	 *
	 * @return  array $value The new, unserialized option value.
	 */
	public static function handle_role_deletion( $value, $old_value ) {
		// find the keys that was in old values but not in value.
		$deleted_roles = array_diff_key( $old_value, $value );
		if ( empty( $deleted_roles ) ) {
			return $value;
		}

		$role_terms = get_terms(
			array(
				'taxonomy'   => 'wcwm_role',
				'hide_empty' => false,
			)
		);

		foreach ( $deleted_roles as $role => $role_data ) {
			foreach ( $role_terms as $role_term ) {
				if ( $role_term->slug === $role ) {
					wp_delete_term( $role_term->term_id, 'wcwm_role' );
				}
			}
		}

		return $value;
	}
}
