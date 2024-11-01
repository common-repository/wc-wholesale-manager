<?php
/**
 * Registration Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/b2b-form-registration.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'wc_wholesale_manager_before_register_form' ); ?>

<div class="woocommerce">

	<?php wc_print_notices(); ?>

	<h2><?php esc_html_e( 'Register', 'wc-wholesale-manager' ); ?></h2>

	<form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?> >

		<?php do_action( 'woocommerce_register_form_start' ); ?>

		<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="reg_username"><?php esc_html_e( 'Username', 'wc-wholesale-manager' ); ?>&nbsp;<span class="required">*</span></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="" />
			</p>

		<?php endif; ?>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="reg_email"><?php esc_html_e( 'Email address', 'wc-wholesale-manager' ); ?>&nbsp;<span class="required">*</span></label>
			<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="" />
		</p>

		<?php do_action( 'wc_wholesale_manager_register_form' ); ?>
		<?php do_action( 'woocommerce_register_form' ); ?>

		<p class="woocommerce-FormRow form-row">
			<?php wp_nonce_field( 'wcwm_register_wholesaler' ); ?>
			<input type="hidden" name="action" value="wcwm_register_wholesaler">
			<button type="submit" class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit" name="register" value="<?php esc_attr_e( 'Register', 'wc-wholesale-manager' ); ?>"><?php esc_html_e( 'Register', 'wc-wholesale-manager' ); ?></button>
		</p>
		<?php do_action( 'woocommerce_register_form_end' ); ?>
	</form>
</div>

<?php do_action( 'wc_wholesale_manager_after_register_form' ); ?>
