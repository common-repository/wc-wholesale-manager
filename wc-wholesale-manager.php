<?php
/**
 * Plugin Name:          Wholesale Manager for WooCommerce
 * Plugin URI:           https://pluginever.com/plugins/woocommerce-wholesale-manager-pro/
 * Description:          Wholesale Manager for WooCommerce is the most powerful WooCommerce B2B plugin created for WooCommerce store owners. Sell products at wholesale prices to your registered B2B customers and also sell products at regular prices to your B2C customers all within your WooCommerce store.
 * Version:              1.2.0
 * Requires at least:    5.0
 * Requires PHP:         7.4
 * Author:               PluginEver
 * Author URI:           https://pluginever.com/
 * License:              GPL v2 or later
 * License URI:          https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:          wc-wholesale-manager
 * Domain Path:          /languages
 * Requires Plugins:     woocommerce
 * Tested up to:         6.6
 * WC requires at least: 3.0.0
 * WC tested up to:      9.3
 *
 * @package WooCommerceWholesaleManager
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

use WooCommerceWholesaleManager\Plugin;

// Don't call the file directly.
defined( 'ABSPATH' ) || exit();

// Require the autoloader.
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Get the plugin instance.
 *
 * @since 1.0.0
 * @return WooCommerceWholesaleManager\Plugin
 */
function wc_wholesale_manager() { // phpcs:ignore
	$data = array(
		'file'             => __FILE__,
		'settings_url'     => admin_url( 'admin.php?page=wc-wholesale-manager' ),
		'support_url'      => 'https://pluginever.com/support/',
		'docs_url'         => 'https://pluginever.com/docs/wc-wholesale-manager/',
		'premium_url'      => 'https://pluginever.com/plugins/woocommerce-wholesale-manager-pro/',
		'premium_basename' => 'wc-wholesale-manager-pro',
		'review_url'       => 'https://wordpress.org/support/plugin/wc-wholesale-manager/reviews/?filter=5#new-post',
	);

	return Plugin::create( $data );
}

// Initialize the plugin.
wc_wholesale_manager();
