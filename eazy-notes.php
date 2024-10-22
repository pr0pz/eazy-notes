<?php
/**
 * Eazy Notes
 *
 * @package           Eazy_Notes
 * @author            Wellington Esteco <info@propz.de>
 * @copyright         2024 propz.de
 * @license           GPL-3.0-or-later
 * 
 * @wordpress-plugin
 * Plugin Name:       Eazy Notes
 * Plugin URI:        https://propz.de/plugins-tools/eazy-notes/
 * Description:       A simple and secure way to share notes with the world. A little bite like pastebin, but inside your WordPress website.
 * Version:           1.0.0
 * Requires at least: 5.3
 * Tested up to:      6.6.2
 * Requires PHP:      7.4
 * Author:            Wellington Estevo <info@propz.de>
 * Author URI:        https://propz.de
 * License:           GPL v3 or later
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       eazy-notes
 * Domain Path:       /languages
 */

namespace Propz\Eazy_Notes;

\defined( 'ABSPATH' ) || exit;

\define( 'EAZY_NOTES_PLUGIN_NAME', 'Eazy Notes' );
\define( 'EAZY_NOTES_VERSION', '1.0.0' );
\define( 'EAZY_NOTES_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
\define( 'EAZY_NOTES_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

	/**
	 * Polyfils
	 */
	require EAZY_NOTES_PLUGIN_PATH . 'includes/eazy-notes-polyfills.php';

	/**
	 * On plugin activate
	 */
	function eazy_notes_activate(): void
	{
		require_once EAZY_NOTES_PLUGIN_PATH . 'includes/class-eazy-notes-activator.php';
		Eazy_Notes_Activator::activate();
	}
	register_activation_hook( __FILE__, __NAMESPACE__ . '\\eazy_notes_activate' );
	
	/**
	 * On plugin dactivate
	 */
	function eazy_notes_deactivate(): void
	{
		require_once EAZY_NOTES_PLUGIN_PATH . 'includes/class-eazy-notes-deactivator.php';
		Eazy_Notes_Deactivator::deactivate();
	}
	register_deactivation_hook( __FILE__, __NAMESPACE__ . '\\eazy_notes_deactivate' );

	/**
	 * The core plugin class
	 */
	require EAZY_NOTES_PLUGIN_PATH . 'includes/class-eazy-notes.php';

	/**
	 * Begins execution of the plugin.
	 */
	$plugin = new Eazy_Notes(
		EAZY_NOTES_PLUGIN_NAME,
		EAZY_NOTES_PLUGIN_PATH,
		EAZY_NOTES_PLUGIN_URL,
		EAZY_NOTES_VERSION
	);
	$plugin->run();