<?php
/**
 * Fired during plugin activation.
 *
 * @package Easy_Notes
 * @subpackage Easy_Notes/includes
 * @version 1.0.0
 */

namespace Propz\Easy_Notes_Lite;

\defined( 'ABSPATH' ) || exit;

class Easy_Notes_Activator
{
	/**
	 * Activate function
	 */
	public static function activate( string $plugin_id )
	{
		$easy_notes = $plugin_id . '/' . $plugin_id .'.php';
		if ( is_plugin_active( $easy_notes ) )
		{
			// Zeige eine Fehlermeldung und verhindere die Aktivierung
			deactivate_plugins( $plugin_id . '-lite/' . $plugin_id .'.php' );
			
			wp_die(
				__( "You can't activate this plugin while the premium version is active.", 'easy-notes' ),
				'Error activating plugin',
				[ 'back_link' => \true ]
			);
		}

		// Flush Rewrite rules after creating post
		add_action( 'init', 'flush_rewrite_rules', 20 );
	}
}