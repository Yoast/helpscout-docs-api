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
	private static $endpoint = 'articles';

	/**
	 * Creates an article in HelpScout.
	 *
	 * @param int $post_id The ID of the post to create in HelpScout docs.
	 *
	 * @return array|\WP_Error Response array or WP Error.
	 */
	public static function create( $post_id ) {
		$hs_data = HelpScout_Post_Data::get( $post_id );
		if ( is_array( $hs_data ) ) {
			return self::update( $post_id );
		}
		$body = self::get_post_data( $post_id );
		if ( $body === [] ) {
			return [ 'error' => 'No content' ];
		}
		$args = [
			'body' => json_encode( $body, JSON_UNESCAPED_SLASHES ),
		];
		$resp = HelpScout_Request::post( self::$endpoint . '?reload=true', $args );

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
		$hs_data   = HelpScout_Post_Data::get( $post_id );

		$args = [
			'body' => json_encode( $post_data, JSON_UNESCAPED_SLASHES ),
		];
		$resp = HelpScout_Request::put( self::$endpoint . '/' . $hs_data['id'] . '?reload=true', $args );

		self::set_post_data( $resp, $post_id );

		return $resp;
	}

	/**
	 * Deletes a post from HelpScout.
	 *
	 * @param int $post_id The ID of the post to delete.
	 *
	 * @return array|\WP_Error Response array or WP Error.
	 */
	public static function delete( $post_id ) {
		$hs_data = HelpScout_Post_Data::get( $post_id );

		if ( isset( $hs_data['id'] ) ) {
			$resp = HelpScout_Request::delete( self::$endpoint . '/' . $hs_data['id'] );
			HelpScout_Post_Data::delete( $post_id );

			return $resp;
		}
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
		HelpScout_Post_Data::set( $post_id, $data );

		return $data;
	}

	/**
	 * Retrieves the HelpScout data from post data.
	 *
	 * @param int $post_id The ID of the post to create or update in HelpScout docs.
	 *
	 * @return array HelpScout post data.
	 */
	private static function get_post_data( $post_id ) {
		$post = get_post( $post_id );

		if ( empty( $post->post_content ) ) {
			return [];
		}

		$keywords     = get_post_meta( $post_id, 'search_keywords', true );
		$keywords_arr = [];
		if ( ! empty( $keywords ) ) {
			$keywords_arr = explode( ',', $keywords );
		}

		return [
			'collectionId' => self::get_collection( $post->post_type ),
			'status'       => 'published',
			'slug'         => $post->post_name,
			'name'         => $post->post_title,
			'text'         => self::prepare_content( $post->post_content ),
			'keywords'     => $keywords_arr,
		];
	}

	/**
	 * Retrieves the collection ID for the post type.
	 *
	 * @param string $post_type The post type to get the collection for.
	 *
	 * @return false|string
	 */
	private static function get_collection( $post_type ) {
		static $post_types;
		if ( ! isset( $post_types[ $post_type ] ) ) {
			$post_types[ $post_type ] = HelpScout_Collection::get_id( $post_type );
			if ( ! $post_types[ $post_type ] ) {
				$post_types[ $post_type ] = HelpScout_Collection::create( $post_type );
			}
		}

		return $post_types[ $post_type ];
	}

	/**
	 * Prepare the post content for HelpScout.
	 *
	 * @param string $post_content The post content.
	 *
	 * @return string $post_content The post content.
	 */
	private static function prepare_content( $post_content ) {
		$post_content = do_shortcode( $post_content );
		$post_content = str_replace( ' &amp; ', '&', $post_content );
		$post_content = str_replace( '&nbsp;', ' ', $post_content );

		return $post_content;
	}
}
