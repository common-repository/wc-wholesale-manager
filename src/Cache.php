<?php

namespace WooCommerceWholesaleManager;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Cache class.
 *
 * Wrapper class for WP transients and object cache.
 *
 * @since 1.0.0
 */
class Cache {

	/**
	 * Is cache enabled?
	 *
	 * @var bool
	 */
	public static $enabled = true;

	/**
	 * Cache constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {}

	/**
	 * Get default transient expiration value in hours.
	 *
	 * @return int
	 */
	public static function get_default_transient_expiration() {
		return apply_filters( 'wc_wholesale_manager_cache_lifetime', 6 );
	}

	/**
	 * Set a transient value.
	 *
	 * @param string   $key Transient key.
	 * @param mixed    $value Transient value.
	 * @param bool|int $expiration In hours. Optional.
	 *
	 * @return bool
	 */
	public static function set_transient( $key, $value, $expiration = false ) {
		if ( ! self::$enabled ) {
			return false;
		}
		if ( ! $expiration ) {
			$expiration = self::get_default_transient_expiration();
		}
		return set_transient( 'wc_wholesale_manager_' . $key, $value, $expiration * HOUR_IN_SECONDS );
	}

	/**
	 * Get the value of a transient.
	 *
	 * @param string $key Transient key.
	 *
	 * @return bool|mixed
	 */
	public static function get_transient( $key ) {
		if ( ! self::$enabled ) {
			return false;
		}
		return get_transient( 'wc_wholesale_manager_' . $key );
	}

	/**
	 * Delete a transient.
	 *
	 * @param string $key Transient key.
	 */
	public static function delete_transient( $key ) {
		delete_transient( 'wc_wholesale_manager_' . $key );
	}

	/**
	 * Sets a value in cache.
	 *
	 * Only sets if key is not falsy.
	 *
	 * @param string $key Cache key.
	 * @param mixed  $value Cache value.
	 * @param string $group Cache group.
	 */
	public static function set( $key, $value, $group ) {
		if ( ! $key || ! self::$enabled ) {
			return;
		}
		wp_cache_set( (string) $key, $value, "wc_wholesale_manager_$group" );
	}

	/**
	 * Retrieves the cache contents from the cache by key and group.
	 *
	 * @param string $key Cache key.
	 * @param string $group Cache group.
	 *
	 * @return bool|mixed
	 */
	public static function get( $key, $group ) {
		if ( ! $key || ! self::$enabled ) {
			return false;
		}
		return wp_cache_get( (string) $key, "wc_wholesale_manager_$group" );
	}

	/**
	 * Checks if a cache key and group value exists.
	 *
	 * @param string $key Cache key.
	 * @param string $group Cache group.
	 *
	 * @return bool
	 */
	public static function exists( $key, $group ) {
		if ( ! $key || ! self::$enabled ) {
			return false;
		}
		$found = false;
		wp_cache_get( (string) $key, "wc_wholesale_manager_$group", false, $found );
		return $found;
	}

	/**
	 * Remove the item from the cache.
	 *
	 * @param string $key Cache key.
	 * @param string $group Cache group.
	 */
	public static function delete( $key, $group ) {
		if ( ! $key || ! self::$enabled ) {
			return;
		}
		wp_cache_delete( (string) $key, "wc_wholesale_manager_$group" );
	}
}
