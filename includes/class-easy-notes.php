<?php
/**
 * The core plugin class.
 *
 * @package Easy_Notes
 * @subpackage Easy_Notes/includes
 * @version 1.0.0
 */

namespace Propz\Easy_Notes_Lite;

\defined( 'ABSPATH' ) || exit;

class Easy_Notes
{
	/** Maintains and registers all hooks for the plugin. */
	protected Easy_Notes_Loader $loader;

	/** Maintains and registers all hooks for the plugin. */
	protected Easy_Notes_Admin $admin;

	/** Plugin name. */
	protected string $plugin_name;

	/** Plugin path on server. */
	protected string $plugin_path;

	/** Plugin url path on server. */
	protected string $plugin_url;

	/**
	 * Core plugin functionality.
	 */
	public function __construct(
		string $plugin_name,
		string $plugin_path,
		string $plugin_url
	)
	{
		$this->plugin_name = $plugin_name;
		$this->plugin_path = $plugin_path;
		$this->plugin_url = $plugin_url;

		$this->load_dependencies();
		$this->init_admin();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 */
	private function load_dependencies(): void
	{
		/**
		 * The class responsible for orchestrating the actions and filters of the core plugin.
		 */
		require_once $this->get_plugin_path() . 'includes/class-easy-notes-loader.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once $this->get_plugin_path() . 'admin/class-easy-notes-admin.php';

		/**
		 * The class responsible for managing the options.
		 */
		require_once $this->get_plugin_path() . 'admin/class-easy-notes-options.php';

		/**
		 * The class managing the post type
		 */
		require_once $this->get_plugin_path() . 'includes/class-easy-notes-post.php';

		$this->loader = new Easy_Notes_Loader();
	}

	/**
	 * Init Admin class
	 */
	private function init_admin(): void
	{
		$this->admin = new Easy_Notes_Admin(
			$this->get_plugin_name(),
			$this->get_plugin_url(),
			new Easy_Notes_Options( 'easy_notes' )
		);
	}


	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 */
	private function define_admin_hooks(): void
	{
		$this->loader->add_action( 'init', $this->get_admin(), 'load_plugin_textdomain' );
		$this->loader->add_action( 'admin_init', $this->get_admin(), 'register_settings' );
		$this->loader->add_action( 'admin_menu', $this->get_admin(), 'add_submenu_page' );

		// Styles and scripts
		$this->loader->add_action( 'admin_enqueue_scripts', $this->get_admin(), 'enqueue_styles' );

		// Gutenberg
		$this->loader->add_filter( 'use_block_editor_for_post_type', $this->get_admin(), 'maybe_enable_block_editor', 10, 2 );
		$this->loader->add_filter( 'gutenberg_can_edit_post_type', $this->get_admin(), 'maybe_enable_block_editor', 10, 2 );

		// API
		$this->loader->add_filter( 'rest_pre_dispatch', $this->get_admin(), 'restrict_rest_api_access', 10, 3 );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 */
	private function define_public_hooks(): void
	{
		$plugin_post = new Easy_Notes_Post( $this->get_admin() );

		$this->loader->add_action( 'init', $plugin_post, 'create_post_type', 10 );
		$this->loader->add_filter( 'wp_insert_post_data', $plugin_post, 'randomize_slug_on_post_save', 10 );

		$this->loader->add_filter( 'pre_get_document_title', $plugin_post, 'maybe_hide_post_title', 100, 1 );
		$this->loader->add_filter( 'the_title', $plugin_post, 'maybe_hide_post_title', 10, 1 );

		$this->loader->add_action( 'wp_head', $plugin_post, 'add_noindex_nofollow_to_head', 100 );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 */
	public function run(): void
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return string The name of the plugin.
	 */
	public function get_plugin_name(): string
	{
		return $this->plugin_name;
	}

	/**
	 * Get the plugin path
	 *
	 * @return string The name of the plugin.
	 */
	public function get_plugin_url(): string
	{
		return $this->plugin_url;
	}

	/**
	 * Get the plugin path
	 *
	 * @return string The name of the plugin.
	 */
	public function get_plugin_path(): string
	{
		return $this->plugin_path;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return Easy_Notes_Admin Orchestrates the hooks of the plugin.
	 */
	public function get_admin(): Easy_Notes_Admin
	{
		return $this->admin;
	}
}