<?php

namespace Yoast\HelpScout_Docs_API\Admin;

use Yoast\HelpScout_Docs_API\Includes\Options;

/**
 * Backend Class for the Yoast HelpScout Docs API plugin options.
 */
class Options_Admin extends Options {

	/**
	 * The option group name.
	 *
	 * @var string
	 */
	public static $option_group = 'HelpScout_Docs_API';

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', [ $this, 'admin_init' ] );

		parent::__construct();
	}

	/**
	 * Register the needed option and its settings sections.
	 */
	public function admin_init() {
		register_setting( self::$option_group, parent::$option_name, [ $this, 'sanitize_options_on_save' ] );

		$sections = [
			new Options_General(),
			new Options_Post_Types(),
		];

		foreach ( $sections as $section ) {
			$section->register();
		}
	}

	/**
	 * Sanitizes and trims a string.
	 *
	 * @param string $string String to sanitize.
	 *
	 * @return string
	 */
	private function sanitize_string( $string ) {
		return (string) trim( sanitize_text_field( $string ) );
	}

	/**
	 * Sanitize options.
	 *
	 * @param array $new_options Options to sanitize.
	 *
	 * @return array
	 */
	public function sanitize_options_on_save( $new_options ) {
		$nonce = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
		if ( wp_verify_nonce( $nonce, 'helpscout-docs-api' ) && isset( $_POST['hs_docs_active_tab'] ) ) {
			$active_tab = filter_input( INPUT_POST, 'hs_docs_active_tab', FILTER_SANITIZE_STRING );
			set_transient( 'hs_docs_active_tab', $active_tab );
		}
		foreach ( $new_options as $key => $value ) {
			switch ( self::$option_var_types[ $key ] ) {
				case 'string':
					$new_options[ $key ] = $this->sanitize_string( $new_options[ $key ] );
					break;
				case 'bool':
					if ( isset( $new_options[ $key ] ) ) {
						$new_options[ $key ] = true;
						break;
					}
					$new_options[ $key ] = false;
					break;
				case 'int':
					$new_options[ $key ] = (int) $new_options[ $key ];
					break;
				case 'array':
					if ( ! is_array( $new_options[ $key ] ) ) {
						$new_options[ $key ] = (array) $new_options[ $key ];
					}
					break;
			}
		}

		return $new_options;
	}

	/**
	 * Output an optional input description.
	 *
	 * @param array $args Arguments to get data from.
	 */
	private function input_desc( $args ) {
		if ( isset( $args['desc'] ) ) {
			echo '<p class="description">' . esc_html( $args['desc'] ) . '</p>';
		}
	}

	/**
	 * Create a text input.
	 *
	 * @param array $args Arguments to get data from.
	 */
	public function input_text( $args ) {
		echo '<input id="' . esc_attr( $args['name'] ) . '" type="text" class="text" name="helpscout_api_key[' . esc_attr( $args['name'] ) . ']" value="' . esc_attr( $args['value'] ) . '"/>';
		$this->input_desc( $args );
	}

	/**
	 * Create a text input.
	 *
	 * @param array $args Arguments to get data from.
	 */
	public function input_radio( $args ) {
		foreach ( $args['values'] as $value => $label ) {
			$checked = '';
			if ( $value === $args['value'] ) {
				$checked = 'checked';
			}
			echo '<input type="radio" name="helpscout_api_key[' . esc_attr( $args['name'] ) . ']" id="' . esc_attr( $args['name'] . '_' . $value ) . '" value="' . esc_attr( $value ) . '" ' . esc_attr( $checked ) . '/> <label for="' . esc_attr( $args['name'] . '_' . $value ) . '">' . esc_html( $label ) . '</label><br/>';
		}
		$this->input_desc( $args );
	}

	/**
	 * Create a number input.
	 *
	 * @param array $args Arguments to get data from.
	 */
	public function input_number( $args ) {
		echo '<input id="' . esc_attr( $args['name'] ) . '" type="number" class="text" name="helpscout_api_key[' . esc_attr( $args['name'] ) . ']" value="' . esc_attr( $args['value'] ) . '"/>';
		$this->input_desc( $args );
	}

	/**
	 * Create a checkbox input.
	 *
	 * @param array $args Arguments to get data from.
	 */
	public function input_checkbox( $args ) {
		$val    = Options::get( $args['name'] );
		$option = isset( $val ) ? $val : false;
		echo '<input id="' . esc_attr( $args['name'] ) . '" class="checkbox" type="checkbox" ' . checked( $option, true, false ) . ' name="helpscout_api_key[' . esc_attr( $args['name'] ) . ']"/>';
		$this->input_desc( $args );
	}

	/**
	 * Create a checkbox input.
	 *
	 * @param array $args Arguments to get data from.
	 */
	public function input_checkbox_array( $args ) {
		$val    = Options::get( $args['name'] );
		$option = ( isset( $val[ $args['value'] ] ) && $val[ $args['value'] ] === 'on' );
		echo '<input id="' . esc_attr( $args['id'] ) . '" class="checkbox array" type="checkbox" ' . checked( $option, true, false ) . ' name="helpscout_api_key[' . esc_attr( $args['name'] ) . '][' . esc_attr( $args['value'] ) . ']"/>';
		$this->input_desc( $args );
	}

	/**
	 * Generates a list of checkboxes based on an array.
	 *
	 * @param array  $checkboxes The checkboxes to list.
	 * @param string $section    The current section.
	 *
	 * @return void
	 */
	protected function checkbox_list( $checkboxes, $section ) {
		foreach ( $checkboxes as $key => $label ) {
			add_settings_field(
				$key,
				'<label for="' . $key . '">' . $label . '</label>',
				[ $this, 'input_checkbox' ],
				$this->page,
				$section,
				[
					'name'  => $key,
					'value' => Options::get( $key ),
				]
			);
		}
	}

	/**
	 * Returns all the currently existing WordPress Roles.
	 *
	 * @return string[]
	 */
	protected function get_role_names() {
		global $wp_roles;

		if ( ! isset( $wp_roles ) ) {
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Overriding only when it's not set.
			$wp_roles = new \WP_Roles();
		}

		return $wp_roles->get_names();
	}

	/**
	 * Function to output a section intro.
	 *
	 * @param string $text The text to output.
	 */
	protected function intro_helper( $text ) {
		echo '<p>';
		echo esc_html( $text );
		echo '</p>';
	}
}
