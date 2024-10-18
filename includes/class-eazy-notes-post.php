<?php
/**
 * Class managing the post type and it's features.
 *
 * @package Eazy_Notes
 * @subpackage Eazy_Notes/includes
 * @version 1.0.0
 */

namespace Propz\Eazy_Notes;

\defined( 'ABSPATH' ) || exit;

class Eazy_Notes_Post
{
	/** Maintains and registers all hooks for the plugin. */
	protected Eazy_Notes_Admin $admin;

	/**
	 * Core plugin functionality.
	 */
	public function __construct( Eazy_Notes_Admin $admin )
	{
		$this->admin = $admin;
	}

	/**
	 * Register custom post type
	 * 
	 * Action: init
	 * 
	 * @return void
	 */
	public function create_post_type(): void
	{
		$note_name = $this->get_admin()->get_note_name();
		$note_name_plural = $this->get_admin()->get_note_name_plural();

		$note_slug = sanitize_title_with_dashes( $note_name );
		$note_slug_plural = sanitize_title_with_dashes( $note_name_plural );

		// Labels
		$labels = [
			'name'					=> $note_name_plural,
			'singular_name'			=> $note_name,
			'menu_name'				=> $note_name_plural,
			'name_admin_bar'		=> $note_name_plural,
			/* translators: %s: note name */
			'archives'				=> \sprintf( __( '%s Archives', 'eazy-notes' ), $note_name ),
			/* translators: %s: note name */
			'attributes'			=> \sprintf( __( '%s Attributes', 'eazy-notes' ), $note_name ),
			/* translators: %s: note name plural */
			'all_items'				=> \sprintf( __( 'All %s', 'eazy-notes' ), $note_name_plural ),
			/* translators: %s: note name */
			'add_new_item'			=> \sprintf( __( 'Add New %s', 'eazy-notes' ), $note_name ),
			/* translators: %s: note name */
			'add_new'				=> esc_attr__( 'Add New', 'eazy-notes' ),
			/* translators: %s: note name */
			'new_item'				=> \sprintf( __( 'New %s', 'eazy-notes' ), $note_name ),
			/* translators: %s: note name */
			'edit_item'				=> \sprintf( __( 'Edit %s', 'eazy-notes' ), $note_name ),
			/* translators: %s: note name */
			'update_item'			=> \sprintf( __( 'Update %s', 'eazy-notes' ), $note_name ),
			/* translators: %s: note name */
			'view_item'				=> \sprintf( __( 'View %s', 'eazy-notes' ), $note_name ),
			/* translators: %s: note name plural */
			'view_items'			=> \sprintf( __( 'View %s', 'eazy-notes' ), $note_name_plural ),
			/* translators: %s: note name */
			'search_items'			=> \sprintf( __( 'Search %s', 'eazy-notes' ), $note_name ),
			'not_found'			 	=> __( 'Not found', 'eazy-notes' ),
			'not_found_in_trash'	=> __( 'Not found in Trash', 'eazy-notes' ),
			/* translators: %s: note name */
			'featured_image'		=> \sprintf( __( '%s Image', 'eazy-notes' ), $note_name ),
			/* translators: %s: note name */
			'set_featured_image'	=> \sprintf( __( 'Set %s image', 'eazy-notes' ), $note_name ),
			/* translators: %s: note name */
			'remove_featured_image' => \sprintf( __( 'Remove %s image', 'eazy-notes' ), $note_name ),
			/* translators: %s: note name */
			'use_featured_image'	=> \sprintf( __( 'Use as %s image', 'eazy-notes' ), $note_name ),
			/* translators: %s: note name */
			'insert_into_item'		=> \sprintf( __( 'Insert into %s', 'eazy-notes' ), $note_name ),
			/* translators: %s: note name */
			'uploaded_to_this_item' => \sprintf( __( 'Uploaded to this %s', 'eazy-notes' ), $note_name ),
			/* translators: %s: note name */
			'items_list'			=> \sprintf( __( '%s list', 'eazy-notes' ), $note_name_plural ),
			/* translators: %s: note name */
			'items_list_navigation' => \sprintf( __( '%s list navigation', 'eazy-notes' ), $note_name_plural ),
			/* translators: %s: note name */
			'filter_items_list'	 	=> \sprintf( __( 'Filter %s list', 'eazy-notes' ), $note_name_plural ),
		];

		// URL format
		$rewrite = $this->get_admin()->get_option( 'rewrite' ) ? [
			'slug'			=> $note_slug,
			'with_front'	=> \false,
			'pages'			=> \true,
			'feeds'			=> \false,
		] : \false;

		// Supports
		$supports = [ 'title', 'editor' ];
		if ( $this->get_admin()->get_option( 'revisions' ) )
			$supports[] = 'revisions';

		// All options
		$args = [
			'label'					=> $note_name,
			'description'			=> $note_name,
			'labels'				=> apply_filters( 'eazy_notes_note_labels', $labels, $note_name, $note_name_plural ),
			'supports'				=> apply_filters( 'eazy_notes_note_supports', $supports ),
			'hierarchical'			=> \false,
			'public'				=> \true,
			'show_ui'				=> \true,
			'show_in_menu'			=> \true,
			'menu_position'			=> 20,
			'menu_icon'				=> 'dashicons-format-aside',
			'show_in_admin_bar' 	=> \false,
			'show_in_nav_menus' 	=> \false,
			'show_in_rest'			=> (bool) $this->get_admin()->get_option( 'show_in_rest' ), // Needed for Block Editor to show
			'rest_base'				=> apply_filters( 'eazy_notes_note_rest_base', $note_slug_plural, $note_slug ),
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'can_export'			=> \true,
			'has_archive'			=> $this->get_admin()->get_option( 'has_archive' ) ? $note_slug_plural : \false,
			'exclude_from_search'	=> (bool) $this->get_admin()->get_option( 'exclude_from_search' ),
			'publicly_queryable'	=> \true,
			'rewrite'				=> apply_filters( 'eazy_notes_note_rewrite', $rewrite, $note_slug, $note_slug_plural ),
			'capability_type'		=> apply_filters( 'eazy_notes_note_capability_type', 'post' )
		];
		register_post_type( $this->get_admin()->get_post_type(), $args );
	}

	/**
	 * Check if the title needs to be hidden
	 * 
	 * Filter: pre_get_document_title, wp_title
	 * 
	 * @param string $title Post title as string.
	 * @return string Maybe edited post title.
	 */
	public function maybe_hide_post_title( string $title ): string
	{
		global $post;
		if ( empty( $post ) ) return $title;

		$post_id = $post->ID;
		$post_status = $post->post_status;
		$post_type = $post->post_type;

		if (
			$post_type !== $this->get_admin()->get_post_type() ||
			is_preview() ||
			empty( $post_status ) ||
			$post_status === 'draft'
		) return $title;
		
		if ( post_password_required( $post_id ) )
		{
			return __( 'Password protected', 'eazy-notes' );
		}

		return $title;
	}

	/**
	 * Add noindex + nofollow to our post type
	 * 
	 * Action: wp_head
	 * 
	 * @return void
	 */
	public function add_noindex_nofollow_to_head(): void
	{
		global $post;
		if (
			empty( $post ) ||
			empty( $post->post_type ) ||
			$post->post_type !== $this->get_admin()->get_post_type() ||
			!$this->get_admin()->get_option( 'hide_from_search_engines' )
		) return;

		echo '<meta name="robots" content="noindex, nofollow" />';
	}

	/**
	 * Randomize post slug on save
	 * 
	 * Filter: wp_insert_post_data
	 * 
	 * @param array $data Post data.
	 * @return array Post data with new slug.
	 */
	public function randomize_slug_on_post_save( array $data ): array
	{
		if (
			empty( $data['post_type'] ) ||
			$data['post_type'] !== $this->get_admin()->get_post_type() ||
			!empty( $data['post_name'] )
		) return $data;

		$data['post_name'] = \substr( \bin2hex( random_bytes( 16 ) ), \random_int( 0, 16 ), 16 );

		return $data;
	}

	/**
	 * Get admin instance
	 * 
	 * @return Eazy_Notes_Admin
	 */
	private function get_admin(): Eazy_Notes_Admin
	{
		return $this->admin;
	}
}