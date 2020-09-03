<?php
/**
* Plugin Name: WWOPN Team Members
* Plugin URI: github.com/cumulus-digital/wwopn-teams
* GitHub Plugin URI: cumulus-digital/wwopn-teams
* Description: A plugin to create a team members
* Version:  0.10
* Author: Daniel Vena
* Author URI: westwoodone.com
* License: GPL2
*/
namespace WWOPN_Teams;

const PLUGIN_NAME = 'wwopn-teams';
const PREFIX = 'wpn_teams';
const TXTDOMAIN = PREFIX;
const BASEPATH = PLUGIN_NAME;
const BASE_FILENAME = PLUGIN_NAME . DIRECTORY_SEPARATOR . PLUGIN_NAME . '.php';

require_once __DIR__ . '/helpers.php';

require_once __DIR__ . '/cpt.php';
require_once __DIR__ . '/team.php';
require_once __DIR__ . '/role.php';
require_once __DIR__ . '/shortcodes.php';

/**
 * Flush permalinks on activation
 */
function plugin_activation() {
	if ( ! \get_option('permalink_structure')) {
		die(
			'<p style="font-family:sans-serif">' .
			sprintf(__('WWOPN Team Members requires a <a href="%s" target="_top">permalink structure</a> be set to something other than "Plain".'), \admin_url('options-permalink.php'))
		);
	}

	// Flush permalinks after activation
	\add_action( 'admin_init', 'flush_rewrite_rules', 20 );

}
\register_activation_hook( __FILE__, __NAMESPACE__ . '\plugin_activation');

/**
 * Ensure a permalink structure exists, 
 * otherwise display an error on all admin pages
 */
function plugin_checkPermalinks() {
	if (\get_option('permalink_structure')) {
		return;
	}
	?>
	<div class="notice notice-error">
		<p>
		<?=sprintf(__('WWOPN Team Members requires a <a href="%s">permalink structure</a> be set to something other than "Plain".'), \admin_url('options-permalink.php'))?>
		</p>
	</div>
	<?php
}
\add_action( 'admin_notices', __NAMESPACE__ . '\plugin_checkPermalinks' );

function frontend_enqueueStyles() {
	\wp_register_style(
		PREFIX . '_style',
		\plugin_dir_url(__FILE__) . 'assets/styles.css'
	);
	\wp_enqueue_style(PREFIX . '_style');
}
\add_action('wp_enqueue_scripts', __NAMESPACE__ . '\frontend_enqueueStyles');
