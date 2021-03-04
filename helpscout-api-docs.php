<?php
/**
 * Granular Control for Yoast SEO
 *
 * @package   Yoast/HelpScout-Docs-API
 * @copyright Copyright (C) 2019 Yoast BV - support@yoast.com
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 or higher
 *
 * @wordpress-plugin
 * Plugin Name: HelpScout Docs API
 * Version:     0.1
 * Plugin URI:  https://yoast.com/wordpress/plugins/helpscout-docs-api/
 * Description: Sync content to the Helpscout docs API
 * Author:      Team Yoast
 * Author URI:  https://yoast.com/
 * Text Domain: helpscout-docs-api
 */

namespace Yoast\HelpScout_Docs_API;

use Yoast\HelpScout_Docs_API\Admin\Admin;

if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

define( 'HS_DOCS_API_PLUGIN_FILE', __FILE__ );
define( 'HS_DOCS_API_PLUGIN_VERSION', '0.1' );
define( 'HS_DOCS_API_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'HS_DOCS_API_DIR_URL', plugin_dir_url( __FILE__ ) );

/**
 * Class Yoast SEO Granular Control base class.
 */
class Control {

	/**
	 * Initialize the plugin settings.
	 */
	public function __construct() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
			require __DIR__ . '/vendor/autoload.php';
		}

		add_action( 'plugins_loaded', [ $this, 'init' ] );
	}

	/**
	 * Initialize the whole plugin.
	 */
	public function init() {
		if ( is_admin() ) {
			load_plugin_textdomain( 'helpscout-docs-api', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

			new Admin();
		}
	}
}

new Control();
