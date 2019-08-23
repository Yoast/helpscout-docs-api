<?php
/**
 * HelpScout_DOCS API plugin file.
 *
 * @package HelpScout_Docs_API
 */

namespace HelpScout_Docs_API;

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
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		parent::__construct();
	}

	/**
	 * Register the needed option and its settings sections.
	 */
	public function admin_init() {
		register_setting( self::$option_group, parent::$option_name, array( $this, 'sanitize_options_on_save' ) );

		$sections = array(
			new Options_General(),
		);

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
		//		print_r( $_POST );
		//		die;

		if ( isset( $_POST['yst_active_tab'] ) ) {
			set_transient( 'yst_active_tab', $_POST['yst_active_tab'] );
		}
		foreach ( $new_options as $key => $value ) {
			switch ( self::$option_var_types[ $key ] ) {
				case 'string':
					$new_options[ $key ] = $this->sanitize_string( $new_options[ $key ] );
					break;
				case 'bool':
					if ( isset( $new_options[ $key ] ) ) {
						$new_options[ $key ] = true;
					} else {
						$new_options[ $key ] = false;
					}
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
		echo '<input id="' . $args['name'] . '" type="text" class="text" name="helpscout_api_key[' . esc_attr( $args['name'] ) . ']" value="' . esc_attr( $args['value'] ) . '"/>';
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
			echo '<input type="radio" name="helpscout_api_key[' . esc_attr( $args['name'] ) . ']" id="' . esc_attr( $args['name'] . '_' . $value ) . '" value="' . esc_attr( $value ) . '" ' . $checked . '/> <label for="' . esc_attr( $args['name'] . '_' . $value ) . '">' . $label . '</label><br/>';
		}
		$this->input_desc( $args );
	}

	/**
	 * Create a number input.
	 *
	 * @param array $args Arguments to get data from.
	 */
	public function input_number( $args ) {
		echo '<input id="' . $args['name'] . '" type="number" class="text" name="helpscout_api_key[' . esc_attr( $args['name'] ) . ']" value="' . esc_attr( $args['value'] ) . '"/>';
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
		echo '<input id="' . $args['name'] . '" class="checkbox" type="checkbox" ' . checked( $option, true, false ) . ' name="helpscout_api_key[' . esc_attr( $args['name'] ) . ']"/>';
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
		echo '<input id="' . $args['id'] . '" class="checkbox array" type="checkbox" ' . checked( $option, true, false ) . ' name="helpscout_api_key[' . esc_attr( $args['name'] ) . '][' . esc_attr( $args['value'] ) . ']"/>';
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
				array( $this, 'input_checkbox' ),
				$this->page,
				$section,
				array(
					'name'  => $key,
					'value' => Options::get( $key ),
				)
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
