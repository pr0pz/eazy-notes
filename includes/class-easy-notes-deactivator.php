<?php
/**
 * Fired during plugin deactivation.
 *
 * @package Easy_Notes
 * @subpackage Easy_Notes/includes
 * @version 1.0.0
 */

namespace Propz\Easy_Notes_Lite;

\defined( 'ABSPATH' ) || exit;

class Easy_Notes_Deactivator
{
	/**
	 * Deactivate function
	 */
	public static function deactivate()
	{
		add_action( 'init', 'flush_rewrite_rules', 20 );
	}
}