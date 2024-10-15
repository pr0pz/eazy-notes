<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package Easy_Notes
 * @subpackage Easy_Notes/admin
 * @version 1.0.0
 */

namespace Propz\Easy_Notes_Lite;

\defined( 'ABSPATH' ) || exit;

class Easy_Notes_Admin
{
	/** Plugin name. */
	protected string $plugin_name;

	/** Plugin id. */
	protected string $plugin_id;	

	/** Plugin slug. */
	protected string $plugin_slug;

	/** Plugin slug. */
	protected string $plugin_url;	

	/** Plugin path. */
	protected string $plugin_path;	

	/** PLugin Options Instance. */
	private Easy_Notes_Options $plugin_options;

	/**
	 * Constructor
	 *
	 * @param string $plugin_name Plugin name.
	 * @param string $plugin_url Plugin url.
	 * @param Easy_Notes_Options Options instance
	 */
	public function __construct(
		string $plugin_name,
		string $plugin_url,
		string $plugin_path,
		Easy_Notes_Options $plugin_options
	)
	{
		$this->plugin_name = $plugin_name;
		$this->plugin_id = sanitize_title_with_dashes( $plugin_name );
		$this->plugin_slug = \str_replace( '-', '_', $this->plugin_id );
		$this->plugin_url = $plugin_url;
		$this->plugin_path = $plugin_path;
		$this->plugin_options = $plugin_options;
	}

	/**
	 * Register settings
	 */
	public function register_settings()
	{
		register_setting(
			$this->plugin_options->get_option_name(),
			$this->plugin_options->get_option_name(),
			[
				'sanitize_callback' => [ $this, 'sanitize_options' ]
			]
		);
	}

	/**
	 * Add Submenu page for Settings Menu
	 */
	public function add_submenu_page()
	{
		add_submenu_page(
			'options-general.php',
			\sprintf( __( '%s Settings', 'easy-notes' ), $this->get_plugin_name() ),
			$this->get_plugin_name(),
			apply_filters( 'easy_notes_settings_page_capability', 'manage_options' ),
			$this->plugin_options->get_option_name(),
			[ $this, 'render_settings_page' ]
		);
	}

	/**
	 * Render Settings page
	 */
	public function render_settings_page()
	{
		if ( ! current_user_can( apply_filters( 'easy_notes_settings_page_capability', 'manage_options' ) ) ) return;
		?>
		<div id="<?php echo esc_attr( $this->plugin_options->get_option_name() ); ?>" class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php settings_fields( $this->plugin_options->get_option_name() ); ?>
				<?php $this->render_settings_sections(); ?>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render Settings section
	 */
	public function render_settings_sections()
	{
		foreach( $this->plugin_options->get_settings_sections() as $section_id => $section )
		{
			// Open section
			echo '
			<table class="form-table easy-notes-section section-' . esc_attr( $section_id ) . '" role="presentation">
				<tbody>
					<tr>
						<th scope="row">' . esc_html( $section['title'] ) . '</th>
						<td>
							<fieldset>
								<legend class="screen-reader-text">' . esc_html( $section['title'] ) . '</legend>';

			foreach( $this->plugin_options->get_settings_fields() as $option_name => $field )
			{
				if ( $field['section'] !== $section_id ) continue;
				$field['name'] = $option_name;
				$this->render_settings_field( $field );
			}

			// Close section
			echo '
							</fieldset>
						</td>
					</tr>
				</tbody>
			</table>';
		}
	}

	/**
	 * Render single settings field
	 */
	public function render_settings_field( array $field )
	{
		// Setup and sanitize all vars
		$name = esc_attr( $field['name'] );
		$input_type = esc_attr( $field['type'] );
	
		// ID, Name, Label, Description
		$input_id = $this->plugin_options->get_option_prefix() . '_' . $name;
		$input_name = $this->plugin_options->get_option_name() . '[' . $name . ']';
		$input_label = wp_kses_post( \nl2br( $this->replace_settings_field_placeholders( $field['label'] ) ) );
		$input_description = !empty( $field['description'] ) ? '<p class="description">' . wp_kses_post( \nl2br( $this->replace_settings_field_placeholders( $field['description'] ) ) ) . '</p>' : '';

		// Values, min, max
		$input_value = $field['value'];
		$input_min = !empty( $field['min'] ) ? ' min="' . esc_attr( $field['min'] ) . '"' : '';
		$input_max = !empty( $field['max'] ) ? ' max="' . esc_attr( $field['max'] ) . '"' : '';
		$current_value = $this->get_option( $name );

		// Classes
		$input_class = !empty( $field['class'] ) ? ' ' . sanitize_html_class( $field['class'] ) : '';
		$input_wrapper_class = !empty( $field['premium'] ) ? ' premium' : '';

		$output = '';

		switch ( $input_type )
		{
			case 'checkbox':
				$output = '
				<div class="form-check form-input-wrapper' . $input_wrapper_class . '">
					<input type="' . $input_type . '" id="' . $input_id . '" name="' . $input_name . '" class="form-check-input' . $input_class . '" value="1"' . checked( 1, $current_value, \false ) . '>
					<label for="' . $input_id . '" class="form-check-label">' . $input_label . '</label>
					' . $input_description . '
				</div>';
				break;

			case 'textarea':
				$output = '
				<div class="form-group form-input-wrapper' . $input_wrapper_class . '">
					<label for="' . $input_id . '" class="form-label">' . $input_label . '</label>
					<textarea id="' . $input_id . '" name="' . $input_name . '" rows="' . ( !empty( $field['rows'] ) ? $field['rows'] : 4 ) . '" class="form-control' . $input_class . '">'
						. wp_kses_post( $current_value ) . '
					</textarea>
					' . $input_description . '
				</div>';
				break;

			case 'radio':
				$output = '
				<div class="form-group form-input-wrapper' . $input_wrapper_class . '">
					<fieldset>
						<legend>' . $input_label . '</legend>
				';
				foreach( $input_value as $radio_id => $radio )
				{
					$output .= '
					<div class="form-check">
						<input type="' . $input_type . '" id="' . $radio_id . '" name="' . $input_name .'" value="' . esc_attr( $radio['value'] ) . '" class="form-check-input' . $input_class . '"' . checked( $radio['value'], $current_value, \false ) . '>
						<label for="' . $radio_id . '" class="form-check-label">' . wp_kses_post( \nl2br( $this->replace_settings_field_placeholders( $radio['label'] ) ) ) . '</label>
					</div>';
				}
				$output .= $input_description . '
					</fieldset>
				</div>';
				break;

			case 'select':
				$output = '
				<div class="form-group form-input-wrapper' . $input_wrapper_class . '">
					<label for="' . $input_id . '" class="form-select-label">' . $input_label . '</label>
					<select id="' . $input_id . '" name="' . $input_name . '" class="form-select' . $input_class . '">';

				foreach( $input_value as $option )
				{
					$output .= '<option value="' . esc_attr( $option['value'] ) . '" ' . selected( $current_value, $option['value'], \false ) . '>' . esc_html( $option['label'] ) . '</option>';
				}

				$output .= '
					</select>
					' . $input_description . '
				</div>';
				break;

			case 'number':
			case 'date':
				$output = '
				<div class="form-group form-input-wrapper' . $input_wrapper_class . '">
					<label for="' . $input_id . '" class="form-label">' . $input_label . '</label>
					<input type="' . $input_type . '" id="' . $input_id . '" name="' . $input_name . '" value="' . esc_attr( $current_value ) . '" class="form-control' . $input_class . '"' . $input_min . $input_max . '>
					' . $input_description . '
				</div>';
				break;
					
			case 'hidden':
				$output = '';
				break;

			default:
				$output = '
				<div class="form-group form-input-wrapper' . $input_wrapper_class . '">
					<label for="' . $input_id . '" class="form-label">' . $input_label . '</label>
					<input type="' . $input_type . '" id="' . $input_id . '" name="' . $input_name . '" value="' . esc_attr( $current_value ) . '" class="form-control' . $input_class . '">
					' . $input_description . '
				</div>';
				break;
		}

		echo $output;
	}

	/**
	 * Sanitize the submitted options.
	 * This method sanitizes the user input before the Settings API saves it to the database.
	 *
	 * @param array $input The raw input from the form.
	 * @return array The sanitized options.
	 */
	public function sanitize_options( $input ): array
	{
		$sanitized	= [];
		$fields		= $this->plugin_options->get_settings_fields();
		$options	= $this->get_options();

		foreach ( $fields as $option_name => $field )
		{
			if ( isset( $input[ $option_name ] ) )
			{
				switch( $field['type'] )
				{
					case 'checkbox':
					case 'select':
					case 'radio':
					case 'range':
						$sanitized[ $option_name ] = esc_attr( $input[ $option_name ] );
						break;

					case 'textarea':
						$sanitized[ $option_name ] = wp_kses_post( $input[ $option_name ] );
						break;

					case 'email':
						$sanitized[ $option_name ] = sanitize_email( $input[ $option_name ] );
						break;

					case 'url':
						$sanitized[ $option_name ] = sanitize_url( $input[ $option_name ] );
						break;
						
					default:
						$sanitized[ $option_name ] = sanitize_text_field( $input[ $option_name ] );
						break;
				}
			}
			else
			{
				$sanitized[ $option_name ] = ( $field['type'] === 'checkbox' ) ? 0 : esc_attr( $field['value'] );
			}

			// Check if flush_rewrite_rules is needed
			if (
				(
					$option_name === 'note_name' ||
					$option_name === 'note_name_plural'
				) &&
				$options[ $option_name ] !== $sanitized[ $option_name ]
			) set_transient( 'flush_rewrite_rules_needed', true, 60 );
		}			

		return $sanitized;
	}

	/**
	 * Get the plugin name
	 * 
	 * @return string Plugin name.
	 */
	public function get_plugin_name(): string
	{
		return $this->plugin_name;
	}

	/**
	 * Get the plugin ud
	 * 
	 * @return string Plugin id.
	 */
	public function get_plugin_id(): string
	{
		return $this->plugin_id;
	}

	/**
	 * Get the plugin slug
	 * 
	 * @return string Plugin slug.
	 */
	public function get_plugin_slug(): string
	{
		return $this->plugin_slug;
	}

	/**
	 * Get the plugin url
	 * 
	 * @return string Plugin slug.
	 */
	public function get_plugin_url(): string
	{
		return $this->plugin_url;
	}

	/**
	 * Get the plugin path
	 * 
	 * @return string Plugin path.
	 */
	public function get_plugin_path(): string
	{
		return $this->plugin_path;
	}

	/**
	 * Get the saved plugin options
	 * 
	 * Combine wp_parse_args with get_option to load new defaults:
	 * https://stackoverflow.com/a/27516495/4371770
	 * 
	 * @return array All saved plugin options.
	 */
	public function get_options(): array
	{
		$options = wp_parse_args(
			get_option(
				$this->plugin_options->get_option_name(),  $this->plugin_options->get_settings_defaults()
			),
			$this->plugin_options->get_settings_defaults()
		);
		return $options;
	}

	/**
	 * Get a single options value
	 * 
	 * @param string $option_name Name of single option to retrieve
	 * @return mixed Single options value
	 */
	public function get_option( string $option_name ): mixed
	{
		$options = $this->get_options();
		if (
			empty( $options ) ||
			!isset( $options[ $option_name ] )
		) return \null;

		return $options[ $option_name ];
	}

	/**
	 * Get Note name singular
	 */
	public function get_post_type(): string
	{
		return $this->get_option( 'post_type' );
	}

	/**
	 * Get Note name singular
	 */
	public function get_note_name(): string
	{
		return $this->get_option( 'note_name' );
	}

	/**
	 * Get Note name singular
	 */
	public function get_note_name_plural(): string
	{
		return $this->get_option( 'note_name_plural' );
	}

	/**
	 * Enable Gutenberg Editor
	 * 
	 * @param bool $current_status Current status if block editor is enable.
	 * @param string $post_type Post type.
	 * @return bool
	 */
	public function maybe_enable_block_editor( bool $current_status, string $post_type )
	{
		if (
			!$this->get_option( 'enable_block_editor' ) &&
			$post_type === $this->get_post_type()
		) return \false;
	
		return $current_status;
	}

	/**
	 * Restrict API access for logged in users
	 * 
	 * @param mixed $result API request result.
	 * @param WP_REST_Server $server
	 * @param WP_REST_Request $request
	 * @return mixed Result if logged in
	 */
	public function restrict_rest_api_access( mixed $result, \WP_REST_Server $server, \WP_REST_Request $request )
	{
		if ( !\str_contains( $request->get_route(), '/' . sanitize_title_with_dashes( $this->get_note_name_plural() ) ) ) return $result;

		// Überprüfe, ob der Benutzer eingeloggt ist
		if ( !is_user_logged_in() )
		{
			return new \WP_Error(
				'rest_forbidden',
				\sprintf( __( '%s are only available for logged in users.', 'easy-notes' ), $this->get_note_name_plural() ),
				[ 'status' => 401 ]
			);
		}

		// Überprüfe, ob der Benutzer die erforderlichen Fähigkeiten hat
		$edit_note_capability = apply_filters( 'easy_notes_edit_note_capability', 'edit_posts' );
		if ( !current_user_can( $edit_note_capability ) )
		{
			return new \WP_Error(
				'rest_forbidden',
				\sprintf( __( '%s are only available for roles with `%s` capability.', 'easy-notes' ), $this->get_note_name_plural(), $edit_note_capability ),
				[ 'status' => 403 ]
			);
		}
	
		return $result;
	}

	/**
	 * Replace all settings fiel label and description placeholders
	 * 
	 * @param string $text Text to search replace
	 * @return string Replaced text
	 */
	private function replace_settings_field_placeholders( string $text ): string
	{
		if ( empty( $text ) ) return '';

		$text = \str_replace(
			[
				'[note_name]',
				'[note_name_plural]',
				'[note_name_slug]',
				'[note_name_plural_slug]',
				'[rest_url]'
			],
			[
				$this->get_note_name(),
				$this->get_note_name_plural(),
				\strtolower( $this->get_note_name() ),
				\strtolower( $this->get_note_name_plural() ),
				rest_url( 'wp/v2/' )
			],
			$text
		);

		return $text;
	}

	/**
	 * Load in the translate folder.
	 */
	public function load_plugin_textdomain()
	{
		load_plugin_textdomain(
			'easy-notes',
			\false,
			$this->get_plugin_id() . '/languages'
		);
	}

	/**
	 * Register the stylesheets for the admin area.
	 */
	public function enqueue_styles()
	{
		$screen = get_current_screen();
		if (
			empty( $screen ) ||
			empty( $screen->id )
		) return;

		$screens_for_admin_css = [
			// In single post
			$this->get_post_type(),
			// Settings page
			'settings_page_' . $this->plugin_options->get_option_name(),
			// Post list
			'edit-' . $this->get_post_type()
		];

		if ( \in_array( $screen->id, $screens_for_admin_css ) )
		{
			wp_enqueue_style( 
				$this->get_plugin_id() . '-admin',
				$this->get_plugin_url() . 'admin/css/' . $this->get_plugin_id() . '-admin.css'
			);
		}
	}
}