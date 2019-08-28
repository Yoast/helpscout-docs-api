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
class Options_General extends Options_Admin implements Options_Section {
	/**
	 * @var string
	 */
	var $page = 'helpscout-docs-api-general';

	/**
	 * Registers the options section.
	 *
	 * @return void
	 */
	public function register() {
		$this->section_api_keys();
		$this->section_site_collection();
	}

	/**
	 * The API keys section.
	 */
	private function section_api_keys() {
		$section = 'general-settings-api-keys';

		add_settings_section(
			$section,
			__( 'API keys', 'helpscout-docs-api' ),
			[ $this, 'api_keys_intro' ],
			$this->page
		);

		$fields = [
			'api-key' => __( 'HelpScout API key', 'helpscout-docs-api' ),
		];
		foreach ( $fields as $key => $label ) {
			add_settings_field(
				$key,
				'<label for="' . $key . '">' . $label . '</label>',
				[ $this, 'input_text' ],
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
	 * The API keys section.
	 */
	private function section_site_collection() {
		$section = 'general-settings-site-collection';

		add_settings_section(
			$section,
			__( 'API keys', 'helpscout-docs-api' ),
			[ $this, 'site_collection_intro' ],
			$this->page
		);

		$fields = [
			'site-id' => __( 'Site', 'helpscout-docs-api' ),
		];
		$values = [
			'site-id' => $this->get_sites(),
		];
		if ( Options::get( 'site-id' ) !== '' ) {
			$fields['collection-id'] = __( 'Collection', 'helpscout-docs-api' );
			$values['collection-id'] = $this->get_collections();
		}
		foreach ( $fields as $key => $label ) {
			add_settings_field(
				$key,
				$label,
				array( $this, 'input_radio' ),
				$this->page,
				$section,
				array(
					'name'   => $key,
					'value'  => Options::get( $key ),
					'values' => $values[ $key ],
				)
			);
		}
	}

	/**
	 * Get the docs sites from the HelpScout API.
	 *
	 * @return array
	 */
	private function get_sites() {
		$response = HelpScout_Request::get( 'sites', [ 'with_site_id' => false ] );
		$response = json_decode( wp_remote_retrieve_body( $response ) );
		$sites    = [];
		foreach ( $response->sites->items as $site ) {
			$sites[ $site->id ] = $site->title . ' <code>' . $site->subDomain . '</code>';
		}

		return $sites;
	}

	/**
	 * Get the collections for the site from the HelpScout API.
	 *
	 * @return array
	 */
	private function get_collections() {
		$response    = HelpScout_Request::get( 'collections' );
		$response    = json_decode( wp_remote_retrieve_body( $response ) );
		$collections = [];
		foreach ( $response->collections->items as $collection ) {
			$collections[ $collection->id ] = $collection->name;
		}

		return $collections;
	}

	/**
	 * The Site & collection section's intro.
	 *
	 * @return void
	 */
	public function site_collection_intro() {
		$this->intro_helper( __( 'Choose the site & collection to publish to.', 'helpscout-docs-api' ) );
	}

	/**
	 * The API keys section's intro.
	 *
	 * @return void
	 */
	public function api_keys_intro() {
		$this->intro_helper( __( 'Set your HelpScout API keys.', 'helpscout-docs-api' ) );
	}

}
