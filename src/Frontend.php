<?php

namespace WooCommerceWholesaleManager;

defined( 'ABSPATH' ) || exit;

/**
 * Frontend class
 *
 * @class Frontend
 * @package WooCommerce Wholesale Manager
 */
class Frontend {

	/**
	 * Frontend constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
		// Protect product and categories.
		add_action( 'template_redirect', array( __CLASS__, 'wholesale_store_redirect' ) );
		add_action( 'template_redirect', array( __CLASS__, 'redirect_products' ) );
		add_filter( 'woocommerce_is_purchasable', array( __CLASS__, 'is_purchasable' ), 20, 2 );
		add_action( 'woocommerce_product_query', array( __CLASS__, 'product_query' ), 10, 1 );
		add_filter( 'woocommerce_shortcode_products_query', array( __CLASS__, 'product_query' ), 10, 1 );
		add_filter( 'wp_get_nav_menu_items', array( __CLASS__, 'hide_menu_items' ), 10, 1 );
		// Registration related hooks.
		add_shortcode( 'wholesale_registration_form', array( __CLASS__, 'registration_form_shortcode' ) );
		add_action( 'init', array( __CLASS__, 'process_registration' ), 20 );
		// For pending approval user login and password reset is disabled.
		add_filter( 'wp_authenticate_user', array( __CLASS__, 'authenticate_customer' ), 10, 1 );
		add_filter( 'allow_password_reset', array( __CLASS__, 'block_password_reset' ), 10, 2 );
		add_filter( 'wc_wholesale_manager_is_wholesaler', array( __CLASS__, 'maybe_treat_admin_as_wholesaler' ), 10, 1 );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @param string $hook Hook name.
	 *
	 * @since 1.0.0
	 */
	public static function enqueue_scripts( $hook ) {
	}

	/**
	 * Redirect to wholesale store.
	 *
	 * @since 1.0.0
	 */
	public static function wholesale_store_redirect() {
		$is_wholesale_store = get_option( 'wcwm_wholesale_only_store', 'no' );
		if ( is_woocommerce() && 'yes' === $is_wholesale_store && ! Helper::is_wholesaler() ) {
			$account_page = get_option( 'woocommerce_myaccount_page_id' );
			wp_safe_redirect( get_permalink( $account_page ) );
			exit;
		}

		// hide all woocommerce pages from menu.
		if ( is_user_logged_in() && ! Helper::is_wholesaler() ) {
			add_filter( 'wp_nav_menu_objects', array( __CLASS__, 'hide_menu_items' ), 10, 1 );
		}
	}

	/**
	 * Hide menu items.
	 *
	 * @param array $items Menu items.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function hide_menu_items( $items ) {
		// Hide all WooCommerce pages from menu.
		$is_wholesale_store = get_option( 'wcwm_wholesale_only_store', 'no' );
		foreach ( $items as $key => $item ) {
			if ( 'product' === $item->object && ! Helper::is_product_visible( $item->object_id ) ) {
				unset( $items[ $key ] );
			} elseif ( 'product_cat' === $item->object && ! Helper::is_category_visible( $item->object_id ) ) {
				unset( $items[ $key ] );
			} elseif ( 'page' === $item->object && is_woocommerce() && $is_wholesale_store ) {
				unset( $items[ $key ] );
			}
		}

		return $items;
	}

	/**
	 * Redirect products.
	 *
	 * @since 1.0.0
	 */
	public static function redirect_products() {
		if ( is_product() && ! Helper::is_product_visible( get_the_ID() ) ) {
			wp_safe_redirect( get_permalink( wc_get_page_id( 'shop' ) ) );
			exit;
		}
	}

	/**
	 * Is purchasable.
	 *
	 * @param bool        $purchasable Purchasable.
	 * @param \WC_Product $product Product object.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public static function is_purchasable( $purchasable, $product ) {
		if ( ! Helper::is_product_visible( $product->get_id() ) ) {
			return false;
		}

		return $purchasable;
	}

	/**
	 * Product query.
	 *
	 * @param \WP_Query $query Query object.
	 *
	 * @since 1.0.0
	 */
	public static function product_query( $query ) {
		$is_b2b_user = Helper::is_wholesaler();
		// if is b2b user then all public_only products should be excluded.
		if ( is_woocommerce() && $query->is_main_query() && isset( $query->query_vars['wc_query'] ) ) {
			if ( $is_b2b_user ) {
				$products   = Helper::get_protected_products( 'public_only' );
				$categories = Helper::get_protected_categories( 'public_only' );
				if ( ! empty( $products ) ) {
					$query->set( 'post__not_in', $products );
				}

				if ( ! empty( $categories ) ) {
					$query->set(
						'tax_query',
						array(
							array(
								'taxonomy' => 'product_cat',
								'field'    => 'term_id',
								'terms'    => $categories,
								'operator' => 'NOT IN',
							),
						)
					);
				}
			} elseif ( ! $is_b2b_user ) {
				$products   = Helper::get_protected_products( 'wholesaler_only' );
				$categories = Helper::get_protected_categories( 'wholesaler_only' );
				if ( ! empty( $products ) ) {
					$query->set( 'post__not_in', $products );
				}

				if ( ! empty( $categories ) ) {
					$query->set(
						'tax_query',
						array(
							array(
								'taxonomy' => 'product_cat',
								'field'    => 'term_id',
								'terms'    => $categories,
								'operator' => 'NOT IN',
							),
						)
					);
				}
			}
		}

		return $query;
	}

	/**
	 * Registration form shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public static function registration_form_shortcode( $atts = array() ) {
		if ( ! is_admin() && is_user_logged_in() ) {
			do_action( 'user_already_registered' );

			return esc_html__( 'You are already registered!', 'wc-wholesale-manager' );
		}

		ob_start();
		wc_get_template(
			'/myaccount/registration-form.php',
			array(
				'atts' => $atts,
			),
			'',
			Plugin::instance()->get_template_path()
		);

		return ob_get_clean();
	}

	/**
	 * Process registration form.
	 *
	 * @throws \Exception Exception.
	 * @since 1.0.0
	 * @return void
	 */
	public static function process_registration() {
		$action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : null;
		$nonce  = isset( $_POST['_wpnonce'] ) ? sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ) : null;
		if ( 'wcwm_register_wholesaler' !== $action || empty( $nonce ) || ! wp_verify_nonce( $nonce, 'wcwm_register_wholesaler' ) ) {
			return;
		}

		$email    = filter_input( INPUT_POST, 'email', FILTER_SANITIZE_EMAIL );
		$username = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ) ) : null;
		$username = 'no' === get_option( 'woocommerce_registration_generate_username' ) && ! empty( $username ) ? $username : '';

		try {
			$error  = new \WP_Error();
			$error  = apply_filters( 'woocommerce_process_registration_errors', $error, $username, '', $email );
			$errors = $error->get_error_messages();

			if ( 1 === count( $errors ) ) {
				throw new \Exception( $error->get_error_message() );
			}

			if ( $errors ) {
				foreach ( $errors as $message ) {
					wc_add_notice( '<strong>' . __( 'Error:', 'wc-wholesale-manager' ) . '</strong> ' . $message, 'error' );
				}
				throw new \Exception();
			}

			if ( empty( $email ) || ! is_email( $email ) ) {
				throw new \Exception( __( 'Please provide a valid email address.', 'wc-wholesale-manager' ) );
			}

			if ( email_exists( $email ) ) {
				$user        = get_user_by( 'email', $email );
				$is_b2b_user = Helper::is_wholesaler( $user->ID );

				if ( $is_b2b_user ) {
					throw new \Exception( __( 'An account is already registered with your email address. Please login.', 'wc-wholesale-manager' ) );
				}

				throw new \Exception( __( 'An account is already registered with this email address, please use a different email address.', 'wc-wholesale-manager' ) );
			}

			if ( 'yes' === get_option( 'woocommerce_registration_generate_username', 'yes' ) && empty( $username ) ) {
				$username = wc_create_new_customer_username( $email );
			}

			$username = sanitize_user( $username );
			if ( empty( $username ) || ! validate_username( $username ) || preg_match( '/\\s/', $username ) ) {
				throw new \Exception( __( 'Please enter a valid account username.', 'wc-wholesale-manager' ) );
			}

			if ( username_exists( $username ) ) {
				$user        = get_user_by( 'login', $username );
				$is_b2b_user = Helper::is_wholesaler( $user->ID );

				if ( $is_b2b_user ) {
					throw new \Exception( __( 'An account is already registered with your username. Please login.', 'wc-wholesale-manager' ) );
				}

				throw new \Exception( __( 'An account is already registered with this username, please use a different username.', 'wc-wholesale-manager' ) );
			}

			$errors = new \WP_Error();
			do_action( 'woocommerce_register_post', $username, $email, $errors );
			do_action( 'wc_wholesale_manager_register_post', $username, $email, $errors );

			$errors = apply_filters( 'woocommerce_registration_errors', $errors, $username, $email );

			if ( $errors->get_error_code() ) {
				throw new \Exception( $errors->get_error_message() );
			}

			$password         = wp_generate_password();
			$is_auto_approval = 'yes' !== get_option( 'wcwm_disable_auto_approval', 'no' );
			$role             = $is_auto_approval ? Helper::get_default_wholesaler_role()->slug : Helper::get_pending_wholesaler_role()->slug;

			$customer_data         = array(
				'user_login' => $username,
				'user_email' => $email,
				'user_pass'  => $password,
			);
			$customer_data         = apply_filters( 'woocommerce_new_customer_data', $customer_data );
			$customer_data['role'] = $role;

			$customer_id = wp_insert_user( $customer_data );

			if ( is_wp_error( $customer_id ) ) {
				throw new \Exception( $customer_id->get_error_message() );
			}

			WC()->mailer();

			do_action( 'wc_wholesale_manager_created_wholesale_user', $customer_id, $customer_data );

			if ( $is_auto_approval ) {
				do_action( 'wc_wholesale_manager_auto_approved_wholesale_user', $customer_id, $password );
				wc_add_notice( __( 'Your account has been created successfully and a password has been sent to your email address.', 'wc-wholesale-manager' ) );
			} else {
				update_user_meta( $customer_id, 'wcwm_approved', 'no' );
				do_action( 'wc_wholesale_manager_pending_wholesale_user', $customer_id );
				wc_add_notice( __( 'Your account has been created and is pending approval. You will receive an email once your account has been approved.', 'wc-wholesale-manager' ), 'success' );
			}
		} catch ( \Exception $e ) {
			wc_add_notice( '<strong>' . __( 'Error:', 'wc-wholesale-manager' ) . '</strong> ' . $e->getMessage(), 'error' );

			return;
		}
	}

	/**
	 * Block password reset for pending users
	 *
	 * @param bool $allow Whether to allow the password reset.
	 * @param int  $user_id The ID of the user attempting to reset a password.
	 *
	 * @since 1.0.0
	 * @return bool $allow
	 */
	public static function block_password_reset( $allow, $user_id ) {
		if ( Helper::is_approval_required( $user_id ) && apply_filters( 'wcwm_block_password_reset', true ) ) {
			$allow = false;
		}

		return $allow;
	}


	/**
	 * Authenticate user on login.
	 *
	 * @param \WP_User $user User.
	 *
	 * @since  1.0.0
	 * @return \WP_User
	 */
	public static function authenticate_customer( $user ) {
		if ( ! is_wp_error( $user ) && Helper::is_approval_required( $user->ID ) ) {
			$message = __( 'Your account is pending approval. You will receive an email once your account has been approved.', 'wc-wholesale-manager' );
			$message = sprintf( '<strong>%s</strong>: %s', __( 'Error', 'wc-wholesale-manager' ), $message );
			$user    = new \WP_Error( 'wcwm_approval_pending', $message );
		}

		return $user;
	}

	/**
	 * Maybe treat admin as a wholesaler customer.
	 *
	 * @param bool $is_wholesaler Whether the user is a wholesaler customer.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public static function maybe_treat_admin_as_wholesaler( $is_wholesaler ) {
		$is_admin_wholesaler = 'yes' === get_option( 'wcwm_admin_is_wholesaler', 'no' );
		if ( ! $is_wholesaler && $is_admin_wholesaler && current_user_can( 'manage_options' ) ) {
			$is_wholesaler = true;
		}

		return $is_wholesaler;
	}
}
