<?php
/**
 * HelpScout_DOCS API plugin file.
 *
 * @package HelpScout_Docs_API
 */

namespace HelpScout_Docs_API;

/**
 * Perform requests to the HelpScout API.
 */
class HelpScout_Request {
	/**
	 * Registers an HTTP request
	 *
	 * @param string $endpoint The endpoint to request to.
	 * @param array  $args     The arguments for this request.
	 *
	 * @return array|\WP_Error Response array or WP Error.
	 */
	public static function request( $endpoint, $args ) {
		$args['headers'] = array_merge(
			isset( $args['headers'] ) ? $args['headers'] : [],
			[ 'Content-Type' => 'application/json' ]
		);
		$args            = self::add_auth( $args );

		return wp_remote_request( self::get_endpoint_url( $endpoint ), $args );
	}

	/**
	 * Performs a GET request.
	 *
	 * @param string $endpoint The endpoint to request to.
	 * @param array  $args     The arguments for this request.
	 *
	 * @return array|\WP_Error Response array or WP Error.
	 */
	public static function get( $endpoint, $args = [] ) {
		$args = self::add_auth( $args );

		$cache_key = md5( Options::get( 'api-key' ) . $endpoint . json_encode( $args ) );
		$cache     = get_transient( $cache_key );
		if ( ! $cache ) {
			$with_site_id = isset( $args['with_site_id'] ) ? $args['with_site_id'] : false;
			unset( $args['with_site_id'] );

			$cache = wp_remote_get( self::get_endpoint_url( $endpoint, $with_site_id ), $args );
			set_transient( $cache_key, $cache );
		}

		return $cache;
	}

	/**
	 * Performs a POST request.
	 *
	 * @param string $endpoint The endpoint to request to.
	 * @param array  $args     The arguments for this request.
	 *
	 * @return array|\WP_Error Response array or WP Error.
	 */
	public static function post( $endpoint, $args ) {
		$args['headers'] = array_merge(
			isset( $args['headers'] ) ? $args['headers'] : [],
			[ 'Content-Type' => 'application/json' ]
		);
		$args            = self::add_auth( $args );

		return wp_remote_post( self::get_endpoint_url( $endpoint ), $args );
	}

	/**
	 * Performs a PUT request.
	 *
	 * @param string $endpoint The endpoint to request to.
	 * @param array  $args     The arguments for this request.
	 *
	 * @return array|\WP_Error Response array or WP Error.
	 */
	public static function put( $endpoint, $args ) {
		$args['headers'] = array_merge(
			isset( $args['headers'] ) ? $args['headers'] : [],
			[ 'Content-Type' => 'application/json' ]
		);
		$args['method']  = 'PUT';
		$args            = self::add_auth( $args );

		return wp_remote_request( self::get_endpoint_url( $endpoint ), $args );
	}

	/**
	 * Turn the endpoint into a full endpoint URL.
	 *
	 * @param string $endpoint The endpoint to use.
	 * @param bool   $with_site_id
	 *
	 * @return string Endpoint URL.
	 */
	private static function get_endpoint_url( $endpoint, $with_site_id = true ) {
		$endpoint = 'https://docsapi.helpscout.net/v1/' . $endpoint;
		if ( $with_site_id ) {
			$endpoint = add_query_arg( 'siteId', Options::get( 'site-id' ), $endpoint );
		}

		return $endpoint;
	}

	/**
	 * Adds a basic auth HTTP header to the request.
	 *
	 * @link https://johnblackbourn.com/wordpress-http-api-basicauth/
	 *
	 * @param array $args Array with request arguments.
	 *
	 * @return array Array with request arguments.
	 */
	private static function add_auth( $args ) {
		$args['headers'] = array_merge(
			isset( $args['headers'] ) ? $args['headers'] : [],
			[ 'Authorization' => 'Basic ' . base64_encode( Options::get( 'api-key' ) . ':X' ) ]
		);

		return $args;
	}

}
