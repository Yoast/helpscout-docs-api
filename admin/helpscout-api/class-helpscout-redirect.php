<?php
/**
 * HelpScout_DOCS API plugin file.
 *
 * @package HelpScout_Docs_API
 */

namespace HelpScout_Docs_API;

/**
 * CRUD for Redirects in HelpScout docs.
 */
class HelpScout_Redirect {
	private static $endpoint = 'redirects';

	/**
	 * Creates a redirect from the HelpScout version of a post to the post itself.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return array|\WP_Error Response array or WP Error.
	 */
	public static function create( $post_id ) {
		$data = HelpScout_Post_Data::get( $post_id );
		if ( isset( $data['redirectId'] ) ) {
			$resp = self::update( $data['redirectId'], $post_id );
			self::set_post_data( $resp, $post_id );

			return $resp;
		}

		$body = self::request_body( $post_id );
		if ( $body === [] ) {
			return [ 'error' => 'something went wrong.' ];
		}

		$resp = HelpScout_Request::post( self::$endpoint, $body );
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
		$data               = HelpScout_Post_Data::get( $post_id );
		$header             = wp_remote_retrieve_header( $resp, 'Location' );
		$endpoint_url       = trailingslashit( HelpScout_Request::get_endpoint_url( self::$endpoint ) );
		$data['redirectId'] = str_replace( $endpoint_url, '', $header );

		HelpScout_Post_Data::set( $post_id, $data );

		return $data;
	}

	/**
	 * Updates a redirect from the HelpScout version of a post to the post itself.
	 *
	 * @param string $redirect_id The HelpScout ID for the redirect we're updating.
	 * @param int    $post_id     Post ID.
	 *
	 * @return array|\WP_Error Response array or WP Error.
	 */
	public static function update( $redirect_id, $post_id ) {
		$body = self::request_body( $post_id );
		if ( $body === [] ) {
			return [ 'error' => 'something went wrong.' ];
		}
		$resp = HelpScout_Request::put( self::$endpoint . '/' . $redirect_id, $body );

		return $resp;
	}

	/**
	 * Calculates the HelpScout slug for a post.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return string The slug.
	 */
	private static function get_helpscout_slug( $post_id ) {
		$hs_data = HelpScout_Post_Data::get( $post_id );

		if ( ! isset( $hs_data['slug'] ) || ! isset( $hs_data['number'] ) ) {
			return false;
		}

		return '/article/' . $hs_data['number'] . '-' . $hs_data['slug'];
	}

	/**
	 * Generates the arguments needed for either a create or update request.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return array The WP request arguments.
	 */
	private static function request_body( $post_id ) {
		$slug = self::get_helpscout_slug( $post_id );
		if ( ! $slug ) {
			return [];
		}
		$body = [
			'siteId'     => Options::get( 'site-id' ),
			'urlMapping' => $slug,
			'redirect'   => get_permalink( $post_id ),
		];

		return [
			'body' => json_encode( $body, JSON_UNESCAPED_SLASHES ),
		];
	}
}
