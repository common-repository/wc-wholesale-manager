<?php
/**
 * Admin New Wholesaler Email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/admin-new-wholesaler.php.
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

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

/**
 * Output body content - this is set in each email's settings.
 */
if ( $body_content ) {
	echo esc_html( wp_strip_all_tags( wptexturize( $body_content ) ) );
	echo "\n\n----------------------------------------\n\n";
}

echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
