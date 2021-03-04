<?php

namespace Yoast\HelpScout_Docs_API\Admin\HelpScout_API;

use Yoast\HelpScout_Docs_API\Includes\Options;

/**
 * CRUD for Collections in HelpScout docs.
 */
class HelpScout_Collection {

	/**
	 * The endpoint for the Collections API.
	 *
	 * @var string
	 */
	private static $endpoint = 'collections';

	/**
	 * The option key for the collections option.
	 *
	 * @var string
	 */
	private static $option_key = 'collections';

	/**
	 * Creates a collection in HelpScout for a Post Type.
	 *
	 * @param string $post_type The post type we're creating a Collection for.
	 *
	 * @return string The ID of the created collection.
	 */
	public static function create( $post_type ) {
		$post_type_object  = get_post_type_object( $post_type );
		$collection_prefix = Options::get( 'collection-prefix' );
		$collection_name   = empty( $collection_prefix ) ? $post_type_object->labels->name : $collection_prefix . ' ' . $post_type_object->labels->name;
		$body              = [
			'siteId' => Options::get( 'site-id' ),
			'name'   => $collection_name,
		];
		$args              = [
			'body' => wp_json_encode( $body, JSON_UNESCAPED_SLASHES ),
		];
		$resp              = HelpScout_Request::post( self::$endpoint, $args );

		// Save the ID from the response.
		$header       = wp_remote_retrieve_header( $resp, 'Location' );
		$endpoint_url = trailingslashit( HelpScout_Request::get_endpoint_url( self::$endpoint ) );
		$id           = str_replace( $endpoint_url, '', $header );

		self::set_id( $post_type, $id );

		return $id;
	}

	/**
	 * Retrieve a Collection's ID.
	 *
	 * @param string $post_type The collection you want the ID for.
	 *
	 * @return false|string The Collection ID when it exists, or false.
	 */
	public static function get_id( $post_type ) {
		$collections = Options::get( self::$option_key );

		return isset( $collections[ $post_type ] ) ? $collections[ $post_type ] : false;
	}

	/**
	 * Save the ID for a collection to our options.
	 *
	 * @param string $post_type The name of the post type collection whose ID we're setting.
	 * @param string $id        The ID.
	 */
	public static function set_id( $post_type, $id ) {
		$collections = Options::get( self::$option_key );

		$collections[ $post_type ] = $id;

		Options::set( self::$option_key, $collections );
	}

	/**
	 * Save the ID for a collection to our options.
	 *
	 * @param string $post_type The name of the post type collection whose ID we're un-setting.
	 */
	public static function unset_id( $post_type ) {
		$collections = Options::get( self::$option_key );

		unset( $collections[ $post_type ] );

		Options::set( self::$option_key, $collections );
	}

	/**
	 * Deletes a Collection from HelpScout.
	 *
	 * @param string $post_type The name of the post type Collection to delete.
	 *
	 * @return bool True when we could delete it, false when we could not.
	 */
	public static function delete( $post_type ) {
		$id = self::get_id( $post_type );
		if ( ! $id ) {
			return false;
		}

		HelpScout_Request::delete( self::$endpoint . '/' . $id );
		self::unset_id( $post_type );

		return true;
	}
}
