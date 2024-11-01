<?php

namespace WooCommerceWholesaleManager;

defined( 'ABSPATH' ) || exit;

/**
 * Helpers class.
 *
 * @since 1.0.0
 */
class Helper {
	/**
	 * Check if the user is a wholesaler user.
	 *
	 * @param \WP_User|int $user User object.
	 *
	 * @since 1.0.0
	 * @return boolean
	 */
	public static function is_wholesaler( $user = null ) {
		if ( is_null( $user ) ) {
			$user = wp_get_current_user();
		}

		if ( is_numeric( $user ) ) {
			$user = get_user_by( 'id', absint( $user ) );
		}

		if ( empty( $user ) || empty( $user->roles ) ) {
			return false;
		}

		// get current user roles.
		$roles = self::get_wholesaler_roles( true );

		// check if the user has any wholesaler role.
		return apply_filters( 'wc_wholesale_manager_is_wholesaler', count( array_intersect( $user->roles, array_keys( $roles ) ) ) > 0, $user );
	}

	/**
	 * Get b2b roles.
	 *
	 * @param boolean $name_only Return only the name of the role.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public static function get_wholesaler_roles( $name_only = false ) {
		$pending_role = self::get_pending_wholesaler_role();
		$roles        = get_terms(
			array(
				'taxonomy'   => 'wcwm_role',
				'hide_empty' => false,
				'exclude'    => $pending_role ? array( $pending_role->id ) : array(),
			)
		);
		if ( is_wp_error( $roles ) ) {
			return array();
		}
		$roles = apply_filters( 'wc_wholesale_manager_roles', $roles );
		if ( $name_only ) {
			return wp_list_pluck( $roles, 'name', 'slug' );
		}

		$roles = array_map(
			function ( $role ) {
				return self::get_wholesaler_role( $role->term_id );
			},
			$roles
		);

		return $roles;
	}

	/**
	 * Get b2b role.
	 *
	 * @param int $role_id Role id.
	 *
	 * @since  1.0.0
	 * @return object {
	 * @type int $id
	 * @type string $name
	 * @type int $discount
	 * @type string $discount_type
	 * @type float $discount_amount
	 * @type int $order
	 * @type int $order_minimum
	 * @type int $order_maximum
	 * @type string $product_pricing
	 * @type string $category_pricing
	 * @type string $disable_tax
	 * @type string $disable_tax
	 * @type string $tax_status
	 * @type string $tax_display
	 * @type array $disabled_gateways
	 * @type array $disabled_shipping_methods
	 * }
	 */
	public static function get_wholesaler_role( $role_id ) {
		$defaults = array(
			'id'                        => 0,
			'name'                      => '',
			'slug'                      => '',
			'discount_type'             => 'percentage',
			'discount'                  => 0,
			'order'                     => 0,
			'order_minimum'             => 0,
			'product_pricing'           => 'no',
			'category_pricing'          => 'no',
			'tax_status'                => 'inherit',
			'tax_display'               => 'inherit',
			'disabled_gateways'         => array(),
			'disabled_shipping_methods' => array(),
		);
		$term     = get_term( $role_id, 'wcwm_role' );
		if ( is_wp_error( $term ) ) {
			return (object) $defaults;
		}

		$meta_props = array(
			'discount_type',
			'discount',
			'order',
			'order_minimum',
			'product_pricing',
			'category_pricing',
			'tax_status',
			'tax_display',
			'disabled_gateways',
			'disabled_shipping_methods',
		);
		$role       = new \stdClass();
		$role->id   = $term->term_id;
		$role->name = $term->name;
		$role->slug = $term->slug;
		foreach ( $meta_props as $prop ) {
			$meta        = get_term_meta( $term->term_id, "_$prop", true );
			$role->$prop = empty( $meta ) ? $defaults[ $prop ] : $meta;
		}

		return apply_filters( 'wc_wholesale_manager_role', $role );
	}

	/**
	 * Get default b2b role.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	public static function get_default_wholesaler_role() {
		$role = get_term_by( 'slug', 'wcwm_role_default', 'wcwm_role' );
		if ( ! $role ) {
			return null;
		}

		return self::get_wholesaler_role( $role->term_id );
	}

	/**
	 * Get pending b2b role.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	public static function get_pending_wholesaler_role() {
		$role = get_term_by( 'slug', 'wcwm_role_pending', 'wcwm_role' );
		if ( ! $role ) {
			return null;
		}

		return self::get_wholesaler_role( $role->term_id );
	}

	/**
	 * Get b2b role by user id.
	 *
	 * @param int $user_id User id.
	 *
	 * @since  1.0.0
	 * @return object|null
	 */
	public static function get_user_wholesaler_role( $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		if ( ! $user_id ) {
			return null;
		}
		if ( ! self::is_wholesaler( $user_id ) ) {
			return null;
		}
		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			return null;
		}
		$roles = $user->roles;
		if ( empty( $roles ) ) {
			return null;
		}
		$wholesaler_roles = self::get_wholesaler_roles();
		foreach ( $wholesaler_roles as $wholesaler_role ) {
			if ( in_array( $wholesaler_role->slug, $roles, true ) ) {
				return $wholesaler_role;
			}
		}

		return self::get_default_wholesaler_role();
	}

	/**
	 * Get protected categories.
	 *
	 * @param string $type Type of categories to get.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public static function get_protected_categories( $type = 'all' ) {
		$categories = Cache::get( 'protected_categories', 'product_cat' );
		if ( false === $categories ) {
			$category_ids = get_terms(
				array(
					'taxonomy'   => 'product_cat',
					'fields'     => 'ids',
					'hide_empty' => false,
					// Include children categories.
					'pad_counts' => true,
					'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						array(
							'key'     => '_wc_b2b_visibility',
							'compare' => 'IN',
							'value'   => array( 'public_only', 'wholesaler_only' ),
						),
					),
				)
			);
			// Add children categories.
			$categories = array();
			foreach ( $category_ids as $category_id ) {
				$categories[ $category_id ] = get_term_meta( $category_id, '_wc_b2b_visibility', true );
			}
			foreach ( $categories as $category_id => $visibility ) {
				$children = get_term_children( $category_id, 'product_cat' );
				foreach ( $children as $child_id ) {
					$categories[ $child_id ] = $visibility;
				}
			}
			Cache::set( 'protected_categories', $categories, 'product_cat' );
		}

		if ( 'all' !== $type ) {
			$categories = array_filter(
				$categories,
				function ( $visibility ) use ( $type ) {
					return $visibility === $type;
				}
			);

			$categories = array_keys( $categories );
			$categories = array_map( 'intval', $categories );
		}

		return $categories;
	}

	/**
	 * Get protected products.
	 *
	 * @param string $type Type of products to get.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public static function get_protected_products( $type = 'all' ) {
		$products = Cache::get( 'protected_products', 'products' );
		if ( false === $products ) {
			$products_ids = get_posts(
				array(
					'post_type'      => array( 'product' ),
					'posts_per_page' => - 1,
					'fields'         => 'ids',
					'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						array(
							'key'     => '_wcwm_visibility',
							'compare' => 'IN',
							'value'   => array( 'public_only', 'wholesaler_only' ),
						),
					),
				)
			);
			$products     = array();
			foreach ( $products_ids as $product_id ) {
				$products[ $product_id ] = get_post_meta( $product_id, '_wcwm_visibility', true );
			}

			Cache::set( 'protected_products', $products, 'products' );
		}

		if ( 'all' !== $type ) {
			$products = array_filter(
				$products,
				function ( $product ) use ( $type ) {
					return $product === $type;
				}
			);

			$products = array_keys( $products );
			$products = array_map( 'intval', $products );
		}

		return $products;
	}

	/**
	 * Get visibility options.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public static function get_visibility_options() {
		return apply_filters(
			'wc_wholesale_manager_visibility_options',
			array(
				'public'          => __( 'Public', 'wc-wholesale-manager' ),
				'public_only'     => __( 'Public Only', 'wc-wholesale-manager' ),
				'wholesaler_only' => __( 'Wholesaler Only', 'wc-wholesale-manager' ),
			)
		);
	}

	/**
	 * Get wholesale login page url.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public static function get_login_url() {
		return get_permalink( get_option( 'wcwm_login_page' ) );
	}

	/**
	 * Check if the product is visible to the user.
	 *
	 * @param int $product_id Product ID.
	 * @param int $user_id User ID.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public static function is_product_visible( $product_id, $user_id = 0 ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		$product_id = absint( $product_id );
		$product    = wc_get_product( $product_id );
		if ( ! $product ) {
			return false;
		}
		$is_wholesaler   = self::is_wholesaler( $user_id );
		$public_only     = self::get_protected_products( 'public_only' );
		$wholesaler_only = self::get_protected_products( 'wholesaler_only' );
		if ( $is_wholesaler && in_array( $product_id, $public_only, true ) ) {
			return false;
		}
		if ( ! $is_wholesaler && in_array( $product_id, $wholesaler_only, true ) ) {
			return false;
		}

		$public_only     = self::get_protected_categories( 'public_only' );
		$wholesaler_only = self::get_protected_categories( 'wholesaler_only' );
		$category_ids    = $product->get_category_ids();

		if ( $is_wholesaler && count( array_intersect( $category_ids, $public_only ) ) ) {
			return false;
		}

		if ( ! $is_wholesaler && count( array_intersect( $category_ids, $wholesaler_only ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Is category visible.
	 *
	 * @param int $category_id Category ID.
	 * @param int $user_id User ID.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public static function is_category_visible( $category_id, $user_id = 0 ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		$category_id          = absint( $category_id );
		$is_wholesaler        = self::is_wholesaler( $user_id );
		$pub_only_cats        = self::get_protected_categories( 'public_only' );
		$wholesaler_only_cats = self::get_protected_categories( 'wholesaler_only' );
		if ( $is_wholesaler && in_array( $category_id, $pub_only_cats, true ) ) {
			return false;
		}
		if ( ! $is_wholesaler && in_array( $category_id, $wholesaler_only_cats, true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if approval is required for a user.
	 *
	 * @param int $user_id User ID.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public static function is_approval_required( $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( $user_id instanceof \WP_User ) {
			$user_id = $user_id->ID;
		}

		return 'no' === get_user_meta( $user_id, '_wcwm_approved', true );
	}

	/**
	 * Get discount for a user.
	 *
	 * @param int $product_id Product ID.
	 * @param int $user_id User ID.
	 *
	 * @since 1.0.0
	 * @return float
	 */
	public static function get_b2b_price( $product_id, $user_id = 0 ) {
		$discount      = false;
		$discount_type = 'fixed';
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		if ( ! self::is_wholesaler( $user_id ) ) {
			return false;
		}
		$user_role = self::get_user_wholesaler_role( $user_id );
		if ( ! $user_role ) {
			return false;
		}
		$product = wc_get_product( $product_id );
		if ( ! $product || empty( $product->get_regular_price( 'edit' ) ) ) {
			return false;
		}

		// Get discount from product meta.
		if ( 'yes' === $user_role->product_pricing ) {
			$discounts = get_post_meta( $product_id, '_wcwm_discounts', true );
			if ( ! empty( $discounts ) ) {
				foreach ( $discounts as $discount_data ) {
					if ( ! isset( $discount_data['enabled'] ) || 'yes' !== $discount_data['enabled'] ) {
						continue;
					}

					if ( $discount_data['role'] === $user_role->slug && $discount_data['discount'] ) {
						$discount = (float) $discount_data['discount'];
						if ( isset( $discount_data['type'] ) ) {
							$discount_type = $discount_data['type'];
						}
						break;
					}
				}
			}
		}

		// Get discount from category meta.
		if ( ! $discount && 'yes' === $user_role->category_pricing ) {
			$categories = $product->get_category_ids();
			if ( $categories ) {
				foreach ( $categories as $category_id ) {
					$discounts = get_term_meta( $category_id, '_wcwm_discounts', true );
					if ( ! $discounts ) {
						continue;
					}
					foreach ( $discounts as $discount_data ) {
						if ( ! isset( $discount_data['enabled'] ) || 'yes' !== $discount_data['enabled'] ) {
							continue;
						}

						if ( $discount_data['role'] === $user_role->slug && $discount_data['discount'] ) {
							$discount = (float) $discount_data['discount'];
							if ( isset( $discount_data['type'] ) ) {
								$discount_type = $discount_data['type'];
							}
							break;
						}
					}
				}
			}
		}

		// Get discount from user role.
		if ( ! $discount ) {
			$discount      = (float) $user_role->discount;
			$discount_type = $user_role->discount_type;
		}

		$price = (float) $product->get_regular_price();
		if ( 'percentage' === $discount_type && ! empty( $price ) && ! empty( $discount ) ) {
			$discount = $price * ( $discount / 100 );
		}

		$price = $price - $discount;
		if ( $price < 0 ) {
			$price = 0;
		}

		return apply_filters( 'wc_wholesale_manager_product_price', $price, $product_id, $user_id );
	}

	/**
	 * Is tax exempt for a b2b user.
	 *
	 * @param int $user_id User ID.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public static function is_tax_exempt( $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		if ( ! self::is_wholesaler( $user_id ) ) {
			return false;
		}
		$user_role = self::get_user_wholesaler_role( $user_id );
		if ( ! $user_role ) {
			return false;
		}
		switch ( $user_role->tax_status ) {
			case 'inherit':
				$exempt = get_option( 'wcwm_disable_tax', 'no' ) === 'yes';
				break;
			case 'enable':
				$exempt = false;
				break;
			case 'disable':
				$exempt = true;
				break;
			default:
				$exempt = false;
		}

		return apply_filters( 'wc_wholesale_manager_is_tax_exempt', $exempt, $user_id );
	}
}
