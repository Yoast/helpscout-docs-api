<?php
/**
 * HelpScout_DOCS API plugin file.
 *
 * @package HelpScout_Docs_API
 */

namespace HelpScout_Docs_API;

/**
 * Backend Class the Yoast HelpScout Docs API plugin.
 */
class Admin {
	/**
	 * Menu slug for WordPress admin.
	 *
	 * @access private
	 * @var string
	 */
	public $hook = 'helpscout-docs-api';

	/**
	 * Construct of class HelpScout Docs API admin.
	 *
	 * @access private
	 * @link   https://codex.wordpress.org/Function_Reference/add_action
	 * @link   https://codex.wordpress.org/Function_Reference/add_filter
	 */
	public function __construct() {
		add_filter( 'plugin_action_links', array( $this, 'add_action_link' ), 10, 2 );

		add_action( 'publish_post', array( $this, 'insert_post' ) );
		add_action( 'admin_menu', array( $this, 'admin_init' ) );
	}

	/**
	 * Initialize needed actions.
	 */
	public function admin_init() {
		$this->register_menu_pages();
	}

	/**
	 * Creates the dashboard and options pages.
	 *
	 * @link https://codex.wordpress.org/Function_Reference/add_options_page
	 * @link https://codex.wordpress.org/Function_Reference/add_dashboard_page
	 */
	private function register_menu_pages() {
		add_options_page(
			__( 'HelpScout Docs API', 'helpscout-docs-api' ),
			__( 'HelpScout API', 'helpscout-docs-api' ),
			'manage_options',
			$this->hook,
			array( new Admin_Page(), 'config_page' )
		);

	}

	/**
	 * Returns the plugins settings page URL.
	 *
	 * @return string Admin URL to the current plugins settings URL.
	 */
	private function plugin_options_url() {
		return admin_url( 'options-general.php?page=' . $this->hook );
	}

	/**
	 * Add a link to the settings page to the plugins list.
	 *
	 * @param array  $links Links to add.
	 * @param string $file  Plugin file name.
	 *
	 * @return array
	 */
	public function add_action_link( $links, $file ) {
		static $this_plugin;
		if ( empty( $this_plugin ) ) {
			$this_plugin = HS_DOCS_API_PLUGIN_FILE;
		}
		if ( $file === $this_plugin ) {
			$settings_link = '<a href="' . $this->plugin_options_url() . '">' . __( 'Settings', 'helpscout-docs-api' ) . '</a>';
			array_unshift( $links, $settings_link );
		}

		return $links;
	}
}
