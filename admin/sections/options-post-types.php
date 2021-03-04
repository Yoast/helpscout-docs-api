<?php

namespace Yoast\HelpScout_Docs_API\Admin\Sections;

use Yoast\HelpScout_Docs_API\Admin\HelpScout_API\HelpScout_Article;
use Yoast\HelpScout_Docs_API\Admin\HelpScout_API\HelpScout_Post_Data;
use Yoast\HelpScout_Docs_API\Admin\HelpScout_API\HelpScout_Redirect;
use Yoast\HelpScout_Docs_API\Admin\Options_Admin;
use Yoast\HelpScout_Docs_API\Includes\Options;

/**
 * Adds general options.
 */
class Options_Post_Types extends Options_Admin implements Options_Section {

	/**
	 * Admin page slug.
	 *
	 * @var string
	 */
	public $page = 'helpscout-docs-api-post-types';

	/**
	 * Registers the options section.
	 *
	 * @return void
	 */
	public function register() {
		$this->section_post_types();
		$this->section_post_types_index();
	}

	/**
	 * The Post types section.
	 */
	private function section_post_types() {
		$section = 'general-settings-post-types';

		add_settings_section(
			$section,
			__( 'Post types', 'helpscout-docs-api' ),
			[ $this, 'section_post_types_intro' ],
			$this->page
		);

		foreach ( get_post_types( [ 'public' => true ], 'objects' ) as $post_type ) {
			$key = 'post-types';
			$id  = $key . '_' . $post_type->name;
			add_settings_field(
				$key . '_' . $post_type->name,
				// Translators: %s becomes the name of the role.
				'<label for="' . $id . '">' . $post_type->label . ' <code>' . $post_type->name . '</code></label>',
				[ $this, 'input_checkbox_array' ],
				$this->page,
				$section,
				[
					'id'    => $id,
					'name'  => $key,
					'value' => $post_type->name,
				]
			);
		}
	}

	/**
	 * The Post types indexation section.
	 */
	private function section_post_types_index() {
		$section = 'general-settings-index';

		add_settings_section(
			$section,
			__( 'Index post types', 'helpscout-docs-api' ),
			[ $this, 'indexation_section' ],
			$this->page
		);
	}

	/**
	 * Renders the indexation section.
	 *
	 * @return void
	 */
	public function indexation_section() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- false positive.
		if ( Options::get( 'api-key' ) === '' || ! isset( $_GET['page'] ) || $_GET['page'] !== 'helpscout-docs-api' ) {
			return;
		}

		$enabled_post_types = Options::get( 'post-types' );
		if ( $enabled_post_types === [] ) {
			return;
		}

		global $wpdb;

		$non_indexed_post_ids = [];
		$non_indexed_count    = [];

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Deliberate decision for speed reasons to do this here.
		$excluded_posts = $wpdb->get_col( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key IN ( '_yoast_wpseo_meta-robots-noindex', 'search_exclude' )" );

		foreach ( $enabled_post_types as $post_type_name => $on ) {
			$post_type   = get_post_type_object( $post_type_name );
			$count_total = wp_count_posts( $post_type_name );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Deliberate decision for speed reasons to do this here.
			$non_indexed_post_ids[ $post_type_name ] = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = %s AND post_status = 'publish' AND post_password = '' AND ID NOT IN ( SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s )", $post_type_name, HelpScout_Post_Data::$meta_key ) );
			$non_indexed_post_ids[ $post_type_name ] = array_diff( $non_indexed_post_ids[ $post_type_name ], $excluded_posts );
			$non_indexed_count[ $post_type_name ]    = count( $non_indexed_post_ids[ $post_type_name ] );

			echo '<p>';
			printf( 'There are %d %s, of which %d are not indexed. ', esc_html( $count_total->publish ), esc_html( $post_type->labels->name ), esc_html( $non_indexed_count[ $post_type_name ] ) );
			if ( count( $non_indexed_post_ids[ $post_type_name ] ) > 0 ) {
				printf( '<a href="' . esc_attr( admin_url( 'options-general.php?page=helpscout-docs-api&index_nonce=' . wp_create_nonce( 'helpscout-index' ) . '&index=' . $post_type->name ) ) . '" class="button">Index %s</a> <br/>', esc_html( $post_type->labels->name ) );
			}
			echo '</p>';
		}

		$nonce = filter_input( INPUT_GET, 'index_nonce', FILTER_SANITIZE_STRING );
		foreach ( $enabled_post_types as $post_type_name => $on ) {
			if ( isset( $_GET['index'] ) && wp_verify_nonce( $nonce, 'helpscout-index' ) && $_GET['index'] === $post_type_name ) {
				if ( $non_indexed_count[ $post_type_name ] > 0 ) {
					foreach ( $non_indexed_post_ids[ $post_type_name ] as $post_id ) {
						HelpScout_Article::create( $post_id );
						HelpScout_Redirect::create( $post_id );
						echo '.';
						usleep( 250000 );
						flush();
					}
				}
			}
		}
	}

	/**
	 * The API keys section's intro.
	 *
	 * @return void
	 */
	public function section_post_types_intro() {
		$this->intro_helper( __( 'Select which post type(s) you\'d like to sync to HelpScout.', 'helpscout-docs-api' ) );
	}
}
