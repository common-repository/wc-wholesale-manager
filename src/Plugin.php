<?php

namespace WooCommerceWholesaleManager;

defined( 'ABSPATH' ) || exit;

/**
 * Class Plugin.
 *
 * @since 1.0.0
 *
 * @package WooCommerceWholesaleManager
 */
class Plugin extends Lib\Plugin {

	/**
	 * Plugin constructor.
	 *
	 * @param array $data The plugin data.
	 *
	 * @since 1.0.0
	 */
	protected function __construct( $data ) {
		parent::__construct( $data );
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Define constants.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function define_constants() {
		$this->define( 'WCWM_VERSION', $this->get_version() );
		$this->define( 'WCWM_FILE', $this->get_file() );
		$this->define( 'WCWM_PATH', $this->get_dir_path() );
		$this->define( 'WCWM_URL', $this->get_dir_url() );
		$this->define( 'WCWM_ASSETS_URL', $this->get_assets_url() );
		$this->define( 'WCWM_ASSETS_PATH', $this->get_assets_path() );
	}

	/**
	 * Include required files.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function includes() {}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init_hooks() {
		register_activation_hook( $this->get_file(), array( Installer::class, 'install' ) );
		add_action( 'admin_notices', array( $this, 'output_admin_notices' ) );
		add_action( 'before_woocommerce_init', array( $this, 'on_before_woocommerce_init' ) );
		add_action( 'woocommerce_init', array( $this, 'init' ), 0 );
	}

	/**
	 * Run on before WooCommerce init.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function on_before_woocommerce_init() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', $this->get_file(), true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', $this->get_file(), true );
		}
	}

	/**
	 * Missing dependencies notice.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function output_admin_notices() {
		$notices = array();

		$discount_percentage = esc_html__( '30%', 'wc-wholesale-manager' );
		if ( ! function_exists( 'wc_wholesale_manager_pro' ) ) {
			$notices[] = array(
				'type'        => 'info',
				'classes'     => 'wcwm-halloween',
				'dismissible' => false,
				'id'          => 'wcwm_halloween_promotion',
				'message'     => sprintf(
				/* translators: %1$s: link to the plugin page, %2$s: Offer content, %3$s: link to the plugin page, %4$s: end link to the plugin page */
					__( '%1$s%2$s%3$s Upgrade Now and Save %4$s', 'wc-wholesale-manager' ),
					'<div class="wcwm-halloween__header"><div class="wcwm-halloween__icon"><img src="' . wc_wholesale_manager()->get_dir_url( 'assets/dist/images/halloween-icon.svg' ) . '" alt="WC Wholesale Manager Halloween offer"></div><div class="wcwm-halloween__content"><strong class="wcwm-halloween__title">',
					'👻 Halloween Sale: ' . $discount_percentage . ' OFF on WC Wholesale Manager Pro</strong><p>Grab a ' . $discount_percentage . ' discount on WC Wholesale Manager Pro and all our premium plugins this Halloween! Use code <strong>‘BIGTREAT30’</strong>. Don\'t miss out!</p>',
					'<a class="button button-primary" href="' . esc_url( wc_wholesale_manager()->get_premium_url() ) . '?utm_source=plugin&utm_medium=notice&utm_campaign=halloween-2024&discount=bigtreat30" target="_blank">',
					$discount_percentage . '</a></div></div>',
				),
			);
		} else {
			$notices[] = array(
				'type'        => 'info',
				'classes'     => 'wcwm-halloween',
				'dismissible' => true,
				'id'          => 'wcwm_halloween_promotion',
				'message'     => sprintf(
				/* translators: %1$s: link to the plugin page, %2$s: Offer content, %3$s: link to the plugin page, %4$s: end link to the plugin page */
					__( '%1$s%2$s%3$s Claim your discount! %4$s', 'wc-wholesale-manager' ),
					'<div class="wcwm-halloween__header"><div class="wcwm-halloween__icon"><img src="' . wc_wholesale_manager()->get_dir_url( 'assets/dist/images/halloween-icon.svg' ) . '" alt="WC Wholesale Manager Halloween offer"></div><div class="wcwm-halloween__content"><strong class="wcwm-halloween__title">',
					'👻 Halloween Sale: ' . $discount_percentage . ' OFF on All Plugins</strong><p>Get ' . $discount_percentage . ' OFF on all premium plugins with code <strong>‘BIGTREAT30’</strong>. Hurry, this deal won’t last long!</p>',
					'<a class="button button-primary" href="' . esc_url( 'https://pluginever.com/plugins/?utm_source=plugin&utm_medium=notice&utm_campaign=halloween-2024&discount=bigtreat30' ) . '" target="_blank">',
					'</a></div></div>',
				),
			);
		}

		foreach ( $notices as $notice ) {
			$notice = wp_parse_args(
				$notice,
				array(
					'id'          => wp_generate_password( 12, false ),
					'type'        => 'info',
					'classes'     => '',
					'message'     => '',
					'dismissible' => false,
				)
			);

			$notice_classes = array( 'notice', 'notice-' . $notice['type'] );
			if ( $notice['dismissible'] ) {
				$notice_classes[] = 'is-dismissible';
			}
			if ( $notice['classes'] ) {
				$notice_classes[] = $notice['classes'];
			}
			?>
			<div class="notice wcwm-notice <?php echo esc_attr( implode( ' ', $notice_classes ) ); ?>" data-notice-id="<?php echo esc_attr( $notice['id'] ); ?>">
				<p><?php echo wp_kses_post( $notice['message'] ); ?></p>
			</div>
			<?php
		}
	}

	/**
	 * Init the plugin after plugins_loaded so environment variables are set.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {
		$this->services->add( Installer::class );
		$this->services->add( Store::class );
		$this->services->add( Roles::class );
		$this->services->add( Emails::class );

		if ( self::is_request( 'admin' ) ) {
			$this->services->add( Admin\Admin::class );
		}

		if ( self::is_request( 'frontend' ) ) {
			$this->services->add( Frontend::class );
		}

		// Init action.
		do_action( 'wc_wholesale_manager_init' );
	}
}
