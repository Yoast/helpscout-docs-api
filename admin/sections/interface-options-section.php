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
interface Options_Section {
	/**
	 * Registers the options section.
	 *
	 * @return void
	 */
	public function register();
}