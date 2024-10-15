<?php
/**
 * Class managing the post type and it's features
 *
 * @package Easy_Notes
 * @subpackage Easy_Notes/includes
 * @version 1.0.0
 */

namespace Propz\Easy_Notes_Lite;

\defined( 'ABSPATH' ) || exit;

class Easy_Notes_Post
{
	/** Maintains and registers all hooks for the plugin. */
	protected Easy_Notes_Admin $admin;

	/**
	 * Core plugin functionality.
	 */
	public function __construct( Easy_Notes_Admin $admin )
	{
		$this->admin = $admin;
	}

	/**
	 * Register custom post type
	 */
	public function create_post_type()
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
			'archives'				=> \sprintf( __( '%s Archives', 'easy-notes' ), $note_name ),
			'attributes'			=> \sprintf( __( '%s Attributes', 'easy-notes' ), $note_name ),
			'all_items'				=> \sprintf( __( 'All %s', 'easy-notes' ), $note_name_plural ),
			'add_new_item'			=> \sprintf( __( 'Add New %s', 'easy-notes' ), $note_name ),
			'add_new'				=> esc_attr__( 'Add New', 'easy-notes' ),
			'new_item'				=> \sprintf( __( 'New %s', 'easy-notes' ), $note_name ),
			'edit_item'				=> \sprintf( __( 'Edit %s', 'easy-notes' ), $note_name ),
			'update_item'			=> \sprintf( __( 'Update %s', 'easy-notes' ), $note_name ),
			'view_item'				=> \sprintf( __( 'View %s', 'easy-notes' ), $note_name ),
			'view_items'			=> \sprintf( __( 'View %s', 'easy-notes' ), $note_name_plural ),
			'search_items'			=> \sprintf( __( 'Search %s', 'easy-notes' ), $note_name ),
			'not_found'			 	=> __( 'Not found', 'easy-notes' ),
			'not_found_in_trash'	=> __( 'Not found in Trash', 'easy-notes' ),
			'featured_image'		=> \sprintf( __( '%s Image', 'easy-notes' ), $note_name ),
			'set_featured_image'	=> \sprintf( __( 'Set %s image', 'easy-notes' ), $note_name ),
			'remove_featured_image' => \sprintf( __( 'Remove %s image', 'easy-notes' ), $note_name ),
			'use_featured_image'	=> \sprintf( __( 'Use as %s image', 'easy-notes' ), $note_name ),
			'insert_into_item'		=> \sprintf( __( 'Insert into %s', 'easy-notes' ), $note_name ),
			'uploaded_to_this_item' => \sprintf( __( 'Uploaded to this %s', 'easy-notes' ), $note_name ),
			'items_list'			=> \sprintf( __( '%s list', 'easy-notes' ), $note_name_plural ),
			'items_list_navigation' => \sprintf( __( '%s list navigation', 'easy-notes' ), $note_name_plural ),
			'filter_items_list'	 	=> \sprintf( __( 'Filter %s list', 'easy-notes' ), $note_name_plural ),
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
			'labels'				=> apply_filters( 'easy_notes_note_labels', $labels, $note_name, $note_name_plural ),
			'supports'				=> apply_filters( 'easy_notes_note_supports', $supports ),
			'hierarchical'			=> \false,
			'public'				=> \true,
			'show_ui'				=> \true,
			'show_in_menu'			=> \true,
			'menu_position'			=> 20,
			'menu_icon'				=> 'dashicons-format-aside',
			'show_in_admin_bar' 	=> \false,
			'show_in_nav_menus' 	=> \false,
			'show_in_rest'			=> (bool) $this->get_admin()->get_option( 'show_in_rest' ), // Needed for Block Editor to show
			'rest_base'				=> apply_filters( 'easy_notes_note_rest_base', $note_slug_plural, $note_slug ),
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'can_export'			=> \true,
			'has_archive'			=> $this->get_admin()->get_option( 'has_archive' ) ? $note_slug_plural : \false,
			'exclude_from_search'	=> (bool) $this->get_admin()->get_option( 'exclude_from_search' ),
			'publicly_queryable'	=> \true,
			'rewrite'				=> apply_filters( 'easy_notes_note_rewrite', $rewrite, $note_slug, $note_slug_plural ),
			'capability_type'		=> apply_filters( 'easy_notes_note_capability_type', 'post' )
		];
		register_post_type( $this->get_admin()->get_post_type(), $args );
	}

	/**
	 * Check if the title needs to be hidden
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
			return __( 'Password protected', 'easy-notes' );
		}

		return $title;
	}

	/**
	 * Add noindex + nofollow to our post type
	 */
	public function add_noindex_nofollow_to_head()
	{
		global $post;
		if (
			empty( $post ) ||
			empty( $post->post_type ) ||
			$post->post_type !== $this->get_admin()->get_post_type() ||
			!$this->get_admin()->get_option( 'search_engine_visibility' )
		) return;

		echo '<meta name="robots" content="noindex, nofollow" />';
	}

	/**
	 * Randomize post slug on save
	 * 
	 * @param array $data Post data.
	 * @return array Post data with new slug.
	 */
	public function randomize_slug_on_post_save( array $data ): array
	{
		if (
			$data['post_type'] !== $this->get_admin()->get_post_type() ||
			!empty( $data['post_name'] )
		) return $data;

		$data['post_name'] = \substr( \bin2hex( random_bytes( 16 ) ), \random_int( 0, 16 ), 16 );

		return $data;
	}

	/**
	 * Get admin instance
	 * 
	 * @return Easy_Notes_Admin
	 */
	private function get_admin(): Easy_Notes_Admin
	{
		return $this->admin;
	}
}