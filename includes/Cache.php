<?php
/**
 * Helper class for handling object caching.
 *
 * @package WyvernToolkit
 */

namespace WyvernToolkit;

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper class for handling object caching.
 *
 * @since 1.0.0
 */
class Cache {

	const KEY = 'wyvern_toolkit';

	/**
	 * Gets cache data.
	 *
	 * @param string $key Key for cache group.
	 * @return mixed
	 */
	public static function get( $key ) {
		return wp_cache_get( self::KEY, $key );
	}

	/**
	 * Sets cache.
	 *
	 * @param string $key Key for cache group.
	 * @param mixed  $data Data value to cache.
	 * @return bool
	 */
	public static function set( $key, $data ) {
		return wp_cache_set( self::KEY, $data, $key );
	}

	/**
	 * Deletes data in keyed cache.
	 *
	 * @param string $key Key for cache group.
	 * @return bool
	 */
	public static function delete( $key ) {
		return wp_cache_delete( self::KEY, $key );
	}

	/**
	 * Flushes all existing caches from WordPress.
	 *
	 * @return bool
	 */
	public static function purge_all() {
		return wp_cache_flush();
	}
}
