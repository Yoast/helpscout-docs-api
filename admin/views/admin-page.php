<?php
/**
 * HelpScout_DOCS API plugin file.
 *
 * @package Yoast/Clicky/View
 */

namespace HelpScout_Docs_API;

?><div class="wrap">
	<h2>
		<?php esc_html_e( 'HelpScout Docs API', 'helpscout-docs-api' ); ?>
        <?php settings_errors(); ?>
	</h2>

	<form action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" method="post">
        <input type="hidden" name="yst_active_tab" id="yst_active_tab" value="<?php echo get_transient( 'yst_active_tab' ); ?>" />
		<?php
		settings_fields( Options_Admin::$option_group );
		?>
		<div id="yoast_wrapper">
			<h2 class="nav-tab-wrapper" id="yoast-tabs">
                <a class="nav-tab" id="general-tab" href="#top#general"><?php esc_html_e( 'General', 'helpscout-docs-api' ); ?></a>
			</h2>

			<div class="tabwrapper">
                <div id="general" class="yoast_tab">
					<?php do_settings_sections( 'helpscout-docs-api-general' ); ?>
                </div>
			</div>
			<?php
			submit_button( __( 'Save settings', 'helpscout-docs-api' ) );
			?>
		</div>
	</form>
	<div id="yoast_sidebar">
		<?php
		$this->yoast_news();
		?>
	</div>
</div>
