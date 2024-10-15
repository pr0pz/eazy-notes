<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://propz.de
 * @since             1.0.0
 * @package           Easy_Notes
 * 
 * @wordpress-plugin
 * Plugin Name:       Easy Notes Lite
 * Plugin URI:        https://propz.de/plugins-tools/easy-notes/
 * Description:       Share notes with the world inside your wordpress website.
 * Version:           1.0.0
 * Author:            Wellington Estevo <info@propz.de>
 * Author URI:        https://propz.de
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/lgpl-3.0.txt
 * Text Domain:       easy-notes
 * Domain Path:       /languages
 */

namespace Propz\Easy_Notes_Lite;

\defined( 'ABSPATH' ) || exit;

\define( 'EASY_NOTES_LITE_PLUGIN_NAME', 'Easy Notes' );
\define( 'EASY_NOTES_LITE_PLUGIN_ID', sanitize_title_with_dashes( EASY_NOTES_LITE_PLUGIN_NAME ) );
\define( 'EASY_NOTES_LITE_VERSION', '1.0.0' );
\define( 'EASY_NOTES_LITE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
\define( 'EASY_NOTES_LITE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

	/**
	 * On plugin activate
	 */
	function easy_notes_activate()
	{
		require_once EASY_NOTES_LITE_PLUGIN_PATH . 'includes/class-easy-notes-activator.php';
		Easy_Notes_Activator::activate( EASY_NOTES_LITE_PLUGIN_ID );
	}
	register_activation_hook( __FILE__, __NAMESPACE__ . '\\easy_notes_activate' );
	
	/**
	 * On plugin dactivate
	 */
	function easy_notes_deactivate()
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