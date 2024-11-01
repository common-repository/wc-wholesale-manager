<?php
/**
 * WC Wholesale Manager Uninstall.
 *
 * Uninstalling WC Wholesale Manager deletes user roles, pages, tables, and options.
 *
 * @package WooCommerceWholesaleManager
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Remove all the options starting with wcwm_.
$delete_all_options = get_option( 'wcwm_delete_data' );
if ( empty( $delete_all_options ) ) {
	return;
}
// Delete all the options.
global $wpdb;
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'wcwm_%';" );
