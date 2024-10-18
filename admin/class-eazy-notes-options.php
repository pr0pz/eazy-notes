<?php
/**
 * Class managing all plugin options.
 *
 * @package Eazy_Notes
 * @subpackage Eazy_Notes/admin
 * @version 1.0.0
 */

namespace Propz\Eazy_Notes;

\defined( 'ABSPATH' ) || exit;

class Eazy_Notes_Options
{
	/** Plugin slug */
	protected string $plugin_slug;

	/**
	 * Constructor
	 */
	public function __construct( string $plugin_slug )
	{
		$this->plugin_slug = $plugin_slug;
	}

	/**
	 * Return all sections
	 * 
	 * @return array All settings sections.
	 */
	public function get_settings_sections(): array
	{
		return [
			'general' => [
				'title' => __( 'General settings', 'eazy-notes' )
			],
			'visibility' => [
				'title' => __( 'Visibility settings', 'eazy-notes' )
			],
			'email' => [
				'title' => __( 'Email settings', 'eazy-notes' )
			]
		];
	}

	/**
	 * Get all settings fields
	 * 
	 * @return array All settings fields.
	 */
	public function get_settings_fields(): array
	{
		return [
			'post_type' => [
				'label'			=> '',
				'value' 		=> 'eazy_note',
				'type'			=> 'hidden',
				'section'		=> 'general'
			],
			'note_name' => [
				'label'			=> __( 'Note Name Singular', 'eazy-notes' ),
				'value'			=> 'Note',
				'type'			=> 'text',
				'section'		=> 'general',
				'premium'		=> \true
			],
			'note_name_plural' => [
				'label'			=> __( 'Note Name Plural', 'eazy-notes' ),
				'value'			=> 'Notes',
				'type'			=> 'text',
				'section'		=> 'general',
				'description'	=> __( "You can rename the notes if you want. The Menu names and URL's are generated from this settings.", 'eazy-notes' ),
				'premium'		=> \true
			],
			'enable_block_editor' => [
				'label'			=> __( 'Use Block Editor aka. Gutenberg?', 'eazy-notes' ),
				'value'			=> 1,
				'type'			=> 'checkbox',
				'section'		=> 'general',
				'description'	=> __( 'If you wanna use the Block Editor (Gutenberg) you need to enable the Rest API (Visibility > Enable Rest API).', 'eazy-notes' )
			],
			'revisions' => [
				'label'			=> __( 'Save [note_name] revisions?', 'eazy-notes' ),
				'value'			=> 0,
				'type'			=> 'checkbox',
				'section'		=> 'general',
				'description'	=> __( 'Revisions are a history of your [note_name] edits so you can undo changes or restore previous versions.', 'eazy-notes' )
			],
			'comments' => [
				'label'			=> __( 'Enable comments for [note_name_plural]?', 'eazy-notes' ),
				'value'			=> 0,
				'type'			=> 'checkbox',
				'section'		=> 'general',
				'description'	=> __( 'Regardless of this setting you can always turn off comments for every individual [note_name].', 'eazy-notes' ),
				'premium'		=> \true
			],
			'rewrite' => [
				'label' 		=> __( "Enable user friendly URL's?", 'eazy-notes' ),
				'value'			=> 1,
				'type'			=> 'checkbox',
				'section'		=> 'visibility',
				'description' 	=> '<code>' . get_bloginfo( 'url' ) . '/<span id="post_name_slug">[note_name_slug]</span>/bla-[note_name_slug]-xyz/</code>'
			],
			'url_length' => [
				'label' 		=> __( "How long should the randomly generated URL be?", 'eazy-notes' ),
				'value'			=> 16,
				'min'			=> 6,
				'max'			=> 30,
				'type'			=> 'number',
				'section'		=> 'visibility',
				'description' 	=> __( 'The default value is pretty good, but the longer the URL, the harder it is to guess it.', 'eazy-notes' ),
				'premium'		=> \true
			],
			'show_in_rest' => [
				'label'			=> __( 'Enable Rest API?', 'eazy-notes' ),
				'value'			=> 1,
				'type'			=> 'checkbox',
				'section'		=> 'visibility',
				'description'	=> __( 'The [note_name_plural] API endpoint is only available for logged-in users with the right capabilities.', 'eazy-notes' ) . '<br><code>[rest_url][note_name_plural_slug]/</code>'
			],
			'hide_from_search_engines' => [
				'label'			=> __( 'Discourage search engines from indexing [note_name_plural]?', 'eazy-notes' ),
				'value'			=> 1,
				'type'			=> 'checkbox',
				'section'		=> 'visibility',
				'description'	=> __( "Hide [note_name_plural] from search engine results. It is up to search engines to honor this request (noindex/nofollow).", 'eazy-notes' )
			],
			'exclude_from_search' => [
				'label'			=> __( 'Exclude [note_name_plural] from search results?', 'eazy-notes' ),
				'value'			=> 1,
				'type'			=> 'checkbox',
				'section'		=> 'visibility',
				'description'	=> __( "Per default you probably don't want your [note_name_plural] to appear on your websites search results.", 'eazy-notes' )
			],
			'show_in_nav_menus' => [
				'label'			=> __( 'Display [note_name_plural] in navigation menus?', 'eazy-notes' ),
				'value'			=> 0,
				'type'			=> 'checkbox',
				'section'		=> 'visibility',
				'description'	=> __( "Per default you probably don't want to link your [note_name_plural] in any menu.", 'eazy-notes' )
			],
			'has_archive' => [
				'label'			=> __( 'Enable public archive page for your Notes?', 'eazy-notes' ),
				'value'			=> 0,
				'type'			=> 'checkbox',
				'section'		=> 'visibility',
				'description'	=> __( "Per default you probably don't want a public accessible archive page for your [note_name_plural].", 'eazy-notes' ) . '<br><code>' . get_bloginfo( 'url' ) . '/<span id="post_name_plural_slug">[note_name_plural_slug]</span>/</code>'
			],
			'must_be_logged_in' => [
				'label'			=> __( 'Restrict access to logged-in users for all [note_name_plural]?', 'eazy-notes' ),
				'value'			=> 0,
				'type'			=> 'checkbox',
				'section'		=> 'visibility',
				'description'	=> __( 'Regardless of this setting you can always restrict the access for every individual [note_name].', 'eazy-notes' ),
				'premium'		=> \true
			],
			'delete_expired' => [
				'label'			=> __( 'Delete expired/burned [note_name_plural]?', 'eazy-notes' ),
				'value'			=> [
					'delete_expired_no' => [
						'label'	=> __( 'No', 'eazy-notes' ),
						'value'	=> 'no'
					],
					'delete_expired_trash' => [
						'label'	=> __( 'Yes, but just put them in the trash bin (non permanently)', 'eazy-notes' ),
						'value'	=> 'trash'
					],
					'delete_expired_yes' => [
						'label'	=> __( 'Yes, and delete them permanently, like for real', 'eazy-notes' ),
						'value'	=> 'delete'
					]
				],
				'type'			=> 'select',
				'section'		=> 'general',
				'description'	=> __( "Choose if you want [note_name_plural] to get deleted automatically after they're expired or burned.", 'eazy-notes' ),
				'premium'		=> \true
			],
			'email_subject' => [
				'label'			=> __( 'Email subject', 'eazy-notes' ),
				'value'			=> __( '[[website_name]] A Note was sent to you', 'eazy-notes' ),
				'type'			=> 'text',
				'section'		=> 'email',
				'class'			=> 'note-email',
				'premium'		=> \true
			],
			'email_message' => [	
				'label'			=> __( 'Email message (supports basic html)', 'eazy-notes' ),
				'value'			=> __( "[website_name] just shared a Note with you:\n<a href='[permalink]' target='_blank'>[title]</a>", 'eazy-notes' ),
				'type'			=> 'textarea',
				'section'		=> 'email',
				'class'			=> 'note-email',
				'rows'			=> 6,
				'description'	=>
					__( "Customize your email subject and message.\nUse the following codes as replacement variables:", 'eazy-notes' )
					. "
					<code>[website_name] -> " . __( 'Your websites name', 'eazy-notes' ) . "</code>
					<code>[permalink] -> " . __( '[note_name] permalink', 'eazy-notes' ) . "</code>
					<code>[title] -> " . __( '[note_name] title', 'eazy-notes' ) . "</code>",
				'premium'		=> \true
			]
		];
	}

	/**
	 * Get all default values
	 * 
	 * @return array Default values.
	 */
	public function get_settings_defaults(): array
	{
		$defaults = [];
		foreach( $this->get_settings_fields() as $field_name => $atts )
		{
			$value = !\is_array( $atts['value'] ) ? $atts['value'] : array_values( $atts['value'] )[0]['value'];
			$defaults[ $field_name ] = $value;
		}
		return $defaults;
	}

	/**
	 * Main option name.
	 * 
	 * @return string Option name.
	 */
	public function get_option_name(): string
	{
		return $this->plugin_slug . '_options';
	}

	/**
	 * Prefix for each option.
	 * 
	 * @return string Option prefix.
	 */
	public function get_option_prefix(): string
	{
		return $this->plugin_slug;
	}

	/**
	 * Get allowed html for kses function.
	 * 
	 * @return array All allowed html tags and atts.
	 */
	public function get_allowed_html(): array
	{
		$default_atts = [ 
			'id' => [],
			'class' => [],
			'title' => [],
			'style' => [],
			'aria-label' => []
		];
		$input_atts = [
			'name' => [],
			'value' => [],
			'placeholder' => [],
			'label' => [],
			'type' => [],
			'checked' => [],
			'selected' => [],
			'disabled' => [],
			'required' => [],
			'readonly' => [],
			'multiple' => [],
			'min' => [],
			'max' => [],
			'minlength' => [],
			'maxlength' => [],
			'rows' => [],
			'cols' => [],
			'list' => [],
			'size' => [],
			'pattern' => [],
			'step' => [],
			'alt' => [],
			'accept' => []
		];
		return [
			'input' => \array_merge( $default_atts, $input_atts ),
			'textarea' => \array_merge( $default_atts, $input_atts ),
			'select' => \array_merge( $default_atts, $input_atts ),
			'option' => \array_merge( $default_atts, $input_atts ),
			'optgroup' => \array_merge( $default_atts, $input_atts ),
			'label' => \array_merge( $default_atts, [ 'for' => [] ] ),
			'a' => \array_merge(
				$default_atts,
				[ 'href' => [], 'target' => [], 'rel' => [] ]
			),
			'fieldset' => $default_atts,
			'legend' => $default_atts,
			'datalist' => $default_atts,
			'div' => $default_atts,
			'code' => $default_atts,
			'table' => $default_atts,
			'tr' => $default_atts,
			'td' => $default_atts,
			'p' => $default_atts,
			'span' => $default_atts,
			'strong' => $default_atts,
			'b' => $default_atts,
			'em' => $default_atts,
			'i' => $default_atts,
			'del' => $default_atts,
			'strike' => $default_atts,
			'u' => $default_atts,
			'ul' => $default_atts,
			'ol' => $default_atts,
			'li' => $default_atts,
			'br' => $default_atts,
			'hr' => $default_atts,
		];
	}
}