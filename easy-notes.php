<?php
/**
 * Easy Notes Lite
 *
 * @package           Easy_Notes
 * @author            Wellington Esteco <info@propz.de>
 * @copyright         2024 propz.de
 * @license           GPL-3.0-or-later
 * 
 * @wordpress-plugin
 * Plugin Name:       Easy Notes Lite
 * Plugin URI:        https://propz.de/plugins-tools/easy-notes/
 * Description:       A simple and secure way to share notes with the world. A little bite like pastebin, but inside your WordPress website.
 * Version:           1.0.0
 * Requires at least: 5.3
 * Tested up to:      6.6.2
 * Requires PHP:      7.4
 * Author:            Wellington Estevo <info@propz.de>
 * Author URI:        https://propz.de
 * License:           GPL v3 or later
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       easy-notes-lite
 * Domain Path:       /languages
 */

namespace Propz\Easy_Notes_Lite;

\defined( 'ABSPATH' ) || exit;

\define( 'EASY_NOTES_LITE_PLUGIN_NAME', 'Easy Notes' );
\define( 'EASY_NOTES_LITE_VERSION', '1.0.0' );
\define( 'EASY_NOTES_LITE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
\define( 'EASY_NOTES_LITE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

	/**
	 * Polyfils
	 */
	require EASY_NOTES_LITE_PLUGIN_PATH . 'includes/easy-notes-polyfills.php';

	/**
	 * On plugin activate
	 */
	function easy_notes_activate(): void
	{
		require_once EASY_NOTES_LITE_PLUGIN_PATH . 'includes/class-easy-notes-activator.php';
		Easy_Notes_Activator::activate();
	}
	register_activation_hook( __FILE__, __NAMESPACE__ . '\\easy_notes_activate' );
	
	/**
	 * On plugin dactivate
	 */
	function easy_notes_deactivate(): void
	{
		require_once EASY_NOTES_LITE_PLUGIN_PATH . 'includes/class-easy-notes-deactivator.php';
		Easy_Notes_Deactivator::deactivate();
	}
	register_deactivation_hook( __FILE__, __NAMESPACE__ . '\\easy_notes_deactivate' );

	/**
	 * The core plugin class
	 */
	require EASY_NOTES_LITE_PLUGIN_PATH . 'includes/class-easy-notes.php';

	/**
	 * Begins execution of the plugin.
	 */
	$plugin = new Easy_Notes(
		EASY_NOTES_LITE_PLUGIN_NAME,
		EASY_NOTES_LITE_PLUGIN_PATH,
		EASY_NOTES_LITE_PLUGIN_URL,
		EASY_NOTES_LITE_VERSION
	);
	$plugin->run();