<?php
/**
 * HelpScout_DOCS API plugin file.
 *
 * @package HelpScout_Docs_API
 */

namespace HelpScout_Docs_API;

/**
 * Adds general options.
 */
class Options_Post_Types extends Options_Admin implements Options_Section {
	/**
	 * @var string
	 */
	var $page = 'helpscout-docs-api-post-types';

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

	public function indexation_section() {
		$non_indexed = [];
		foreach ( Options::get( 'post-types' ) as $post_type_name => $on ) {
			$post_type                      = get_post_type_object( $post_type_name );
			$count                          = wp_count_posts( $post_type_name );
			$non_indexed[ $post_type_name ] = get_posts(
				[
					'post_type'      => $post_type_name,
					'posts_per_page' => - 1,
					'post_status'    => 'publish',
					'meta_query'     => [
						[
							'key'     => '_helpscout_data',
							'compare' => 'NOT EXISTS',
						],
					],
					'fields'         => 'ids',
				] );
			echo '<p>';
			printf( 'There are %d %s, of which %d are not indexed. ', $count->publish, $post_type->labels->name, count( $non_indexed[ $post_type_name ] ) );
			if ( count( $non_indexed[ $post_type_name ] ) > 0 ) {
				printf( '<a href="' . add_query_arg( [ 'index' => $post_type->name ] ) . '" class="button">Index %s</a> <br/>', $post_type->labels->name );
			}
			echo '</p>';
		}

		foreach ( Options::get( 'post-types' ) as $post_type_name => $on ) {
			if ( isset( $_GET['index'] ) && $_GET['index'] === $post_type_name ) {
				foreach ( $non_indexed[ $post_type_name ] as $post_id ) {
					HelpScout_Article::create( $post_id );
					HelpScout_Redirect::create( $post_id );
					echo '.';
					usleep( 250000 );
					flush();
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
