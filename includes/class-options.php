<?php
/**
 * HelpScout_DOCS API plugin file.
 *
 * @package HelpScout_Docs_API
 */

namespace HelpScout_Docs_API;

/**
 * Options Class for the Yoast HelpScout Docs API plugin.
 */
class Options {

	/**
	 * The default options for the Yoast HelpScout Docs API plugin.
	 *
	 * @var array
	 */
	public static $option_defaults = [
		'api-key'       => '',
		'site-id'       => '',
		'collection-id' => '',
	];

	/**
	 * Holds the type of variable that each option is, so we can cast it to that.
	 *
	 * @var array
	 */
	public static $option_var_types = [
		'api-key'       => 'string',
		'site-id'       => 'string',
		'collection-id' => 'string',
	];

	/**
	 * Name of the option we're using.
	 *
	 * @var string
	 */
	public static $option_name = 'helpscout_api_key';

	/**
	 * Saving active instance of this class in this static var.
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Holds the actual options.
	 *
	 * @var array
	 */
	public static $options = array();

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->load_options();
		$this->sanitize_options();
	}

	/**
	 * Loads Control-options set in WordPress.
	 *
	 * If already set: trim some option. Otherwise load defaults.
	 */
	private static function load_options() {
		$options = get_option( self::$option_name );
		if ( is_array( $options ) ) {
			self::$options = array_merge( self::$option_defaults, $options );

			return;
		}

		self::$options = self::$option_defaults;
		update_option( self::$option_name, self::$options );
	}

	/**
	 * Forces all options to be of the type we expect them to be of.
	 */
	private function sanitize_options() {
		foreach ( self::$options as $key => $value ) {
			if ( ! isset( self::$option_var_types[ $key ] ) ) {
				unset( self::$options[ $key ] );
			}
			switch ( self::$option_var_types[ $key ] ) {
				case 'array':
					if ( ! is_array( self::$options[ $key ] ) ) {
						self::$options[ $key ] = [];
					}
					break;
				case 'bool':
					self::$options[ $key ] = (bool) $value;
					break;
				case 'int':
					self::$options[ $key ] = (int) $value;
					break;
				case 'string':
					self::$options[ $key ] = (string) $value;
					break;
			}
		}
	}

	/**
	 * Getting instance of this object. If instance doesn't exists it will be created.
	 *
	 * @return object|Options
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Options();
		}

		return self::$instance;
	}

	/**
	 * Returns the Yoast_SEO_Granular_Control options.
	 *
	 * @param string $key The option to retrieve.
	 *
	 * @return mixed The option.
	 */
	public static function get( $key ) {
		if ( self::$options === array() ) {
			self::load_options();
		}

		return self::$options[ $key ];
	}
}
