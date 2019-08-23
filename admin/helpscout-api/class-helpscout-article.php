<?php
/**
 * HelpScout_DOCS API plugin file.
 *
 * @package HelpScout_Docs_API
 */

namespace HelpScout_Docs_API;

/**
 * CRUD for Articles in HelpScout docs.
 */
class HelpScout_Article {
	/**
	 * Creates an article in HelpScout.
	 *
	 * @param int $post_id The ID of the post to create in HelpScout docs.
	 *
	 * @return array|\WP_Error Response array or WP Error.
	 */
	public static function create( $post_id ) {
		$hs_data = get_post_meta( $post_id, '_published_to_helpscout', true );
		if ( is_array( $hs_data ) ) {
			return self::update( $post_id );
		}
		$args = [
			'body' => json_encode( self::get_post_data( $post_id ) ),
		];
		$resp = HelpScout_Request::post( 'articles?reload=true', $args );

		self::set_post_data( $resp, $post_id );

		return $resp;
	}

	/**
	 * Update a post in HelpScout Docs.
	 *
	 * @param int $post_id The ID of the post to update in HelpScout docs.
	 *
	 * @return array|\WP_Error Response array or WP Error.
	 */
	public static function update( $post_id ) {
		$post_data = self::get_post_data( $post_id );
		$hs_data   = get_post_meta( $post_id, '_published_to_helpscout', true );

		$args = [
			'body' => json_encode( $post_data ),
		];
		$resp = HelpScout_Request::put( 'articles/' . $hs_data['id'] . '?reload=true', $args );

		self::set_post_data( $resp, $post_id );

		return $resp;
	}

	/**
	 * Update a post's HelpScout data.
	 *
	 * @param \WP_Error|array $resp    A WP Remote request response
	 * @param int             $post_id The ID of the post to create or update in HelpScout docs.
	 *
	 * @return array The new HelpScout data.
	 */
	private static function set_post_data( $resp, $post_id ) {
		if ( is_wp_error( $resp ) || $resp['response']['code'] !== 200 ) {
			return [ 'error' => 'Request was not successful.' ];
		}
		$body = json_decode( wp_remote_retrieve_body( $resp ) );
		$data = [
			'id'           => $body->article->id,
			'collectionId' => $body->article->collectionId,
			'slug'         => $body->article->slug,
			'number'       => $body->article->number,
		];
		update_post_meta( $post_id, '_published_to_helpscout', $data );

		echo '<pre>' . print_r( $data, 1 ) . '</pre>';

		return $data;
	}

	/**
	 * Retrieves the HelpScout data from post meta.
	 *
	 * @param int $post_id The ID of the post to create or update in HelpScout docs.
	 *
	 * @return array HelpScout post data.
	 */
	private static function get_post_data( $post_id ) {
		$post = get_post( $post_id );

		return [
			'collectionId' => Options::get( 'collection-id' ),
			'status'       => 'published',
			'slug'         => $post->post_name,
			'name'         => $post->post_title,
			'text'         => $post->post_content,
		];
	}
}
