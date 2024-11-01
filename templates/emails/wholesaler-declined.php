<?php
/**
 * Wholesale Registration Declined Email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/wholesaler-declined.php.
 *
 * HOWEVER, on occasion PluginEver will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @version 4.x.x
 * @package WooCommerceB2B
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php
/**
 * Output body content - this is set in each email's settings.
 */
if ( $body_content ) {
	echo wp_kses_post( wpautop( wptexturize( $body_content ) ) );
}

do_action( 'woocommerce_email_footer', $email );
