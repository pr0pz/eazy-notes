<?php
/**
 * Fired during plugin deactivation.
 *
 * @package Eazy_Notes
 * @subpackage Eazy_Notes/includes
 * @version 1.0.0
 */

namespace Propz\Eazy_Notes;

\defined( 'ABSPATH' ) || exit;

class Eazy_Notes_Deactivator
{
	/**
	 * Deactivate function
	 * 
	 * @return void
	 */
	public static function deactivate(): void
	{
		add_action( 'init', 'flush_rewrite_rules', 20 );
	}
}