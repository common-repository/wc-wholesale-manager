<?php

namespace WooCommerceWholesaleManager;

defined( 'ABSPATH' ) || exit;

/**
 * Class Store.
 *
 * @since 1.0.0
 *
 * @package WooCommerceWholesaleManager
 */
class Store {

	/**
	 * Store constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Regular products.
		add_filter( 'woocommerce_product_get_price', array( $this, 'get_price' ), 99, 2 );
		add_filter( 'woocommerce_get_price_html', array( $this, 'get_price_html' ), 999, 2 );

		add_action( 'wp', array( $this, 'handle_tax_for_b2b2_user' ), PHP_INT_MAX );
		add_filter( 'pre_option_woocommerce_tax_display_shop', array( $this, 'handle_tax_display_for_b2b_user' ), PHP_INT_MAX, 1 );
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'cart_calculate' ), 10, 1 );
		add_filter( 'woocommerce_coupons_enabled', array( $this, 'disable_coupons' ), PHP_INT_MAX, 1 );
		add_filter( 'woocommerce_package_rates', array( $this, 'filter_shipping_methods' ), PHP_INT_MAX, 1 );
		add_filter( 'woocommerce_available_payment_gateways', array( $this, 'filter_payment_methods' ), PHP_INT_MAX, 1 );
	}


	/**
	 * Get the price for a product.
	 *
	 * @param float       $price The price.
	 * @param \WC_Product $product The product.
	 *
	 * @since 1.0.0
	 * @return float
	 */
	public function get_price( $price, $product ) {
		if ( is_admin() || is_cart() || is_checkout() || ! is_user_logged_in() ) {
			return $price;
		}

		if ( ! $product->is_type( 'simple' ) ) {
			return $price;
		}

		$b2b_price = Helper::get_b2b_price( $product->get_id() );
		if ( $b2b_price ) {
			$price = $b2b_price;
		}

		return $price;
	}

	/**
	 * Get the price html for a product.
	 *
	 * @param string      $price_html The price html.
	 * @param \WC_Product $product The product.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_price_html( $price_html, $product ) {
		if ( is_admin() || ! is_user_logged_in() || ! Helper::is_wholesaler() ) {
			return $price_html;
		}

		if ( ! $product->is_type( 'simple' ) ) {
			return $price_html;
		}

		if ( (float) $product->get_regular_price() === (float) $product->get_price() ) {
			if ( $product->is_on_sale() ) {
				$price_html = wc_price( wc_get_price_to_display( $product, array( 'price' => $product->get_price() ) ) ) . $product->get_price_suffix();
			}

			return $price_html;
		}

		if ( ! is_numeric( $product->get_regular_price() ) || get_option( 'wcwm_show_original_price', 'yes' ) === 'no' ) {
			$price_html = wc_price( wc_get_price_to_display( $product, array( 'price' => $product->get_price() ) ) ) . $product->get_price_suffix();
		} else {
			$price_html = sprintf(
				'<del>%s</del> <ins>%s</ins>%s',
				wc_price( wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ) ),
				wc_price( wc_get_price_to_display( $product, array( 'price' => $product->get_price() ) ) ),
				$product->get_price_suffix()
			);
		}

		return $price_html;
	}

	/**
	 * Handle tax for b2b user.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function handle_tax_for_b2b2_user() {
		if ( is_admin() || ! Helper::is_wholesaler() || ! isset( WC()->customer ) ) {
			return;
		}

		if ( Helper::is_tax_exempt() ) {
			WC()->customer->set_is_vat_exempt( true );
		} else {
			WC()->customer->set_is_vat_exempt( false );
		}
	}

	/**
	 * Filters the WC tax display in shop setting to respect the tax status
	 *
	 * @param   string $pre_option The pre option.
	 *
	 * @since 1.0.0
	 * @return  string $pre_option The pre option.
	 */
	public function handle_tax_display_for_b2b_user( $pre_option ) {
		if ( is_admin() || ! Helper::is_wholesaler() || ! isset( WC()->customer ) ) {
			return $pre_option;
		}

		$role = Helper::get_user_wholesaler_role();
		if ( ! $role ) {
			return $pre_option;
		}

		if ( Helper::is_tax_exempt() ) {
			return 'excl';
		}

		switch ( $role->tax_display ) {
			case 'enable':
				$pre_option = 'incl';
				break;
			case 'disable':
				$pre_option = 'excl';
				break;
			case 'inherit':
			default:
				$pre_option = 'excl';
		}

		return $pre_option;
	}

	/**
	 * Cart calculation.
	 *
	 * @param \WC_Cart $cart The cart.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function cart_calculate( $cart ) {
		if ( is_admin() || ! is_user_logged_in() || ! Helper::is_wholesaler() ) {
			return;
		}

		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			$product = $cart_item['data'];

			if ( ! $product->is_type( 'simple' ) ) {
				continue;
			}

			$b2b_price = Helper::get_b2b_price( $product->get_id() );
			if ( $b2b_price ) {
				$cart_item['data']->set_price( $b2b_price );
			}
		}
	}

	/**
	 * Disable coupons for B2B users.
	 *
	 * @param bool $enabled Whether coupons are enabled.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function disable_coupons( $enabled ) {
		if ( is_admin() || ! is_user_logged_in() || ! Helper::is_wholesaler() || ! $enabled ) {
			return $enabled;
		}

		return 'yes' === get_option( 'wcwm_disable_coupon', 'no' ) ? false : $enabled;
	}

	/**
	 * Filter shipping methods.
	 *
	 * @param array $rates The shipping methods.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function filter_shipping_methods( $rates ) {
		if ( is_admin() || ! is_user_logged_in() || ! Helper::is_wholesaler() ) {
			return $rates;
		}

		$role = Helper::get_user_wholesaler_role();
		if ( ! $role || empty( $role->disabled_gateways ) ) {
			return $rates;
		}
		$shipping_methods = $role->disabled_shipping_methods;
		foreach ( $rates as $key => $rate ) {
			if ( ! in_array( $rate->get_method_id(), $shipping_methods, true ) ) {
				unset( $rates[ $key ] );
			}
		}

		return $rates;
	}

	/**
	 * Filter payment methods.
	 *
	 * @param array $gateways The payment methods.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function filter_payment_methods( $gateways ) {
		if ( is_admin() || ! is_user_logged_in() || ! Helper::is_wholesaler() ) {
			return $gateways;
		}

		$role = Helper::get_user_wholesaler_role();
		if ( ! $role || empty( $role->disabled_gateways ) ) {
			return $gateways;
		}
		$disabled_gateways = $role->disabled_gateways;
		foreach ( $gateways as $key => $gateway ) {
			if ( in_array( $key, $disabled_gateways, true ) ) {
				unset( $gateways[ $key ] );
			}
		}

		return $gateways;
	}
}
