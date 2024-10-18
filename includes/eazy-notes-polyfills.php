<?php
/**
 * PHP Polyfills
 *
 * @package Eazy_Notes
 * @subpackage Eazy_Notes/includes
 * @version 1.0.0
 */

\defined( 'ABSPATH' ) || exit;

// PHP < 8
if ( !\function_exists( 'str_contains' ) )
{
	function str_contains( string $haystack, string $needle )
	{
		return empty( $needle ) || \strpos( $haystack, $needle ) !== false;
	}
}