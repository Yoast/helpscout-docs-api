<?php
/**
 * HelpScout_DOCS API plugin file.
 *
 * @package HelpScout_Docs_API
 */

namespace HelpScout_Docs_API;

/**
 * Class for the HelpScout Docs API plugin admin page.
 */
class Admin_Page extends Admin {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		new Options_Admin();

		add_action( 'admin_print_scripts', array( $this, 'config_page_scripts' ) );
		add_action( 'admin_print_styles', array( $this, 'config_page_styles' ) );
	}

	/**
	 * Enqueue the styles for the admin page.
	 */
	public function config_page_styles() {
		wp_enqueue_style( 'yseo-gc-admin-css', HS_DOCS_API_DIR_URL . 'css/dist/admin.css', null, HS_DOCS_API_PLUGIN_VERSION );
	}

	/**
	 * Enqueue the scripts for the admin page.
	 */
	public function config_page_scripts() {
		wp_enqueue_script( 'yseo-gc-admin-js', HS_DOCS_API_DIR_URL . 'js/admin.min.js', null, HS_DOCS_API_PLUGIN_VERSION );
	}

	/**
	 * Creates the configuration page.
	 */
	public function config_page() {
		require HS_DOCS_API_DIR_PATH . 'admin/views/admin-page.php';
	}

	/**
	 * Create a postbox widget.
	 *
	 * @param string $title   Title to display.
	 * @param string $content Content to display.
	 */
	private function box( $title, $content ) {
		// @codingStandardsIgnoreLine
		echo '<div class="yoast_box"><h3>' . esc_html( $title ) . '</h3><div class="inside">' . $content . '</div></div>';
	}

	/**
	 * Create a "plugin like" box.
	 */
	public function like_text() {
		require HS_DOCS_API_DIR_PATH . 'admin/views/like-box.php';
	}

	/**
	 * Generate an RSS box.
	 *
	 * @param string $feed        Feed URL to parse.
	 * @param string $title       Title of the box.
	 * @param string $extra_links Additional links to add to the output, after the RSS subscribe link.
	 */
	private function rss_news( $feed, $title, $extra_links = '' ) {
		$content = get_transient( 'helpscout-docs-api-feed' );
		if ( empty( $content ) ) {
			include_once ABSPATH . WPINC . '/feed.php';
			$rss = fetch_feed( $feed );

			if ( is_wp_error( $rss ) ) {
				$rss = '<li class="yoast">' . __( 'No news items, feed might be broken...', 'helpscout-docs-api' ) . '</li>';
			}
			else {
				$rss_items = $rss->get_items( 0, $rss->get_item_quantity( 5 ) );

				$rss = '';
				foreach ( $rss_items as $item ) {
					$url  = preg_replace( '/#.*/', '', esc_url( $item->get_permalink(), $protocols = null, 'display' ) );
					$rss .= '<li class="yoast">';
					$rss .= '<a href="' . $url . '#utm_source=wpadmin&utm_medium=sidebarwidget&utm_term=newsitem&utm_campaign=clickywpplugin">' . $item->get_title() . '</a> ';
					$rss .= '</li>';
				}
			}

			$content  = '<ul>';
			$content .= $rss;
			$content .= $extra_links;
			$content .= '</ul>';

			set_transient( 'helpscout-docs-api-feed', $content );
		}

		$this->box( $title, $content );
	}

	/**
	 * Box with latest news from Yoast.com for sidebar.
	 */
	private function yoast_news() {
		$extra_links  = '<li class="facebook"><a href="https://www.facebook.com/yoast">' . __( 'Like Yoast on Facebook', 'helpscout-docs-api' ) . '</a></li>';
		$extra_links .= '<li class="twitter"><a href="https://twitter.com/yoast">' . __( 'Follow Yoast on Twitter', 'helpscout-docs-api' ) . '</a></li>';
		$extra_links .= '<li class="email"><a href="https://yoast.com/newsletter/">' . __( 'Subscribe by email', 'helpscout-docs-api' ) . '</a></li>';

		$this->rss_news( 'https://yoast.com/feed/', __( 'Latest news from Yoast', 'helpscout-docs-api' ), $extra_links );
	}

}
