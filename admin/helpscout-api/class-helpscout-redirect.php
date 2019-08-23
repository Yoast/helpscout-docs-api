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
	/**
	 * Creates a redirect from the HelpScout version of a post to the post itself.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return array|\WP_Error Response array or WP Error.
	 */
	public static function create( $post_id ) {
		$resp = HelpScout_Request::post( 'redirects', self::request_body( $post_id ) );

		return $resp;
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
		$resp = HelpScout_Request::put( 'redirects/' . $redirect_id, self::request_body( $post_id ) );

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
		$hs_data = get_post_meta( $post_id, '_published_to_helpscout', true );

		return 'article/' . $hs_data['number'] . '-' . $hs_data['slug'];
	}

	/**
	 * Generates the arguments needed for either a create or update request.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return array The WP request arguments.
	 */
	private static function request_body( $post_id ) {
		return [
			'body' => json_encode(
				[
					'siteId'     => Options::get( 'site-id' ),
					'urlMapping' => self::get_helpscout_slug( $post_id ),
					'redirect'   => 'https://yoast.com/yoast-seo-11-9/' // get_permalink( $post_id ),
				]
			),
		];
	}
}
