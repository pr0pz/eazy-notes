<?php
/**
 * Fired during plugin activation.
 *
 * @package Eazy_Notes
 * @subpackage Eazy_Notes/includes
 * @version 1.0.0
 */

namespace Propz\Eazy_Notes;

\defined( 'ABSPATH' ) || exit;

class Eazy_Notes_Activator
{
	/**
	 * Activate function
	 */
	public static function activate(): void
	{
		// Prevent plugin activation if premium plugin is active
		if ( is_plugin_active( 'eazy-notes-pro/eazy-notes.php' ) )
		{
			deactivate_plugins( 'eazy-notes/eazy-notes.php' );
			
			wp_die(
				esc_html__( "You can't activate this plugin while the premium version is active.", 'eazy-notes' ),
				'Error activating plugin',
				[ 'back_link' => esc_attr( \true ) ]
			);
		}

		// Flush Rewrite rules after creating post
		add_action( 'init', 'flush_rewrite_rules', 20 );
	}
}