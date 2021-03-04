<?php

namespace Yoast\HelpScout_Docs_API\Admin\HelpScout_API;

/**
 * CRUD for HelpScout docs post data stored in WP.
 */
class HelpScout_Post_Data {

	/**
	 * The meta key we're using to store our HelpScout data.
	 *
	 * @var string
	 */
	public static $meta_key = '_helpscout_data';

	/**
	 * Retrieves a post's HelpScout data.
	 *
	 * @param int $post_id The ID of the post we need data for.
	 *
	 * @return false|array An array of data on success, false on failure.
	 */
	public static function get( $post_id ) {
		return get_post_meta( $post_id, self::$meta_key, true );
	}

	/**
	 * Sets a post's HelpScout data.
	 *
	 * @param int   $post_id The ID of the post we set data for.
	 * @param array $value The new data.
	 *
	 * @return int|bool The new meta field ID if a field with the given key didn't exist and was
	 *                  therefore added, true on successful update, false on failure.
	 */
	public static function set( $post_id, $value ) {
		return update_post_meta( $post_id, self::$meta_key, $value );
	}

	/**
	 * Delete's a post's HelpScout data.
	 *
	 * @param int $post_id The ID of the post we delete data for.
	 *
	 * @return bool
	 */
	public static function delete( $post_id ) {
		return delete_post_meta( $post_id, self::$meta_key );
	}
}
