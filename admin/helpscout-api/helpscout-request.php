<?php

namespace Yoast\HelpScout_Docs_API\Admin\HelpScout_API;

use Yoast\HelpScout_Docs_API\Includes\Options;

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

		$cache_key = md5( Options::get( 'api-key' ) . $endpoint . wp_json_encode( $args, JSON_UNESCAPED_SLASHES ) );
		$cache     = get_transient( $cache_key );
		if ( ! $cache ) {

			$cache = wp_remote_get( self::get_endpoint_url( $endpoint ), $args );
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
		$endpoint        = self::get_endpoint_url( $endpoint );

		return wp_remote_post( $endpoint, $args );
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
		return self::custom_http( 'PUT', $endpoint, $args );
	}

	/**
	 * Performs a DELETE request.
	 *
	 * @param string $endpoint The endpoint to request to.
	 *
	 * @return array|\WP_Error Response array or WP Error.
	 */
	public static function delete( $endpoint ) {
		return self::custom_http( 'DELETE', $endpoint );
	}

	/**
	 * Performs a HTTP request of a type you specify.
	 *
	 * @param string $method   The HTTP method to use.
	 * @param string $endpoint The endpoint to request to.
	 * @param array  $args     The arguments for this request.
	 *
	 * @return array|\WP_Error Response array or WP Error.
	 */
	private static function custom_http( $method, $endpoint, $args = [] ) {
		if ( $args !== [] ) {
			$args['headers'] = array_merge(
				isset( $args['headers'] ) ? $args['headers'] : [],
				[ 'Content-Type' => 'application/json' ]
			);
		}
		$args['method'] = $method;
		$args           = self::add_auth( $args );

		return wp_remote_request( self::get_endpoint_url( $endpoint ), $args );
	}

	/**
	 * Turn the endpoint into a full endpoint URL.
	 *
	 * @param string $endpoint The endpoint to use.
	 *
	 * @return string Endpoint URL.
	 */
	public static function get_endpoint_url( $endpoint ) {
		$endpoint = 'https://docsapi.helpscout.net/v1/' . $endpoint;

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
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- base64 is required here.
			[ 'Authorization' => 'Basic ' . base64_encode( Options::get( 'api-key' ) . ':X' ) ]
		);

		return $args;
	}
}
