<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package Eazy_Notes
 * @subpackage Eazy_Notes/admin
 * @version 1.0.0
 */

namespace Propz\Eazy_Notes;

\defined( 'ABSPATH' ) || exit;

class Eazy_Notes_Admin
{
	/** Plugin name. */
	protected string $plugin_name;

	/** Plugin id. */
	protected string $plugin_id;	

	/** Plugin slug. */
	protected string $plugin_url;	

	/** Plugin version. */
	protected string $plugin_version;	

	/** PLugin Options Instance. */
	protected Eazy_Notes_Options $plugin_options;

	/**
	 * Constructor
	 *
	 * @param string $plugin_name Plugin name.
	 * @param string $plugin_url Plugin url.
	 * @param Eazy_Notes_Options Options instance
	 */
	public function __construct(
		string $plugin_name,
		string $plugin_url,
		string $plugin_version,
		Eazy_Notes_Options $plugin_options
	)
	{
		$this->plugin_name = $plugin_name;
		$this->plugin_id = sanitize_title_with_dashes( $plugin_name );
		$this->plugin_url = $plugin_url;
		$this->plugin_version = $plugin_version;
		$this->plugin_options = $plugin_options;
	}

	/**
	 * Register settings
	 * 
	 * Action: admin_init
	 * 
	 * @return void
	 */
	public function register_settings(): void
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
	 * 
	 * Action: admin_menu
	 * 
	 * @return void
	 */
	public function add_submenu_page(): void
	{
		add_submenu_page(
			'options-general.php',
			/* translators: %s: plugin name */
			\sprintf( __( '%s Settings', 'eazy-notes' ), $this->get_plugin_name() ),
			$this->get_plugin_name(),
			apply_filters( 'eazy_notes_settings_page_capability', 'manage_options' ),
			$this->plugin_options->get_option_name(),
			[ $this, 'render_settings_page' ]
		);
	}

	/**
	 * Render Settings page
	 * 
	 * Context: add_submenu_page render callback
	 * 
	 * @return void
	 */
	public function render_settings_page(): void
	{
		if ( ! current_user_can( apply_filters( 'eazy_notes_settings_page_capability', 'manage_options' ) ) ) return;
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
	 * 
	 * Context: render_settings_page
	 * 
	 * @return void
	 */
	public function render_settings_sections(): void
	{
		foreach( $this->plugin_options->get_settings_sections() as $section_id => $section )
		{
			// Open section
			echo '
			<table class="form-table eazy-notes-section section-' . esc_attr( $section_id ) . '" role="presentation">
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
	 * 
	 * Context: render_settings_sections
	 * 
	 * @return void
	 */
	public function render_settings_field( array $field ): void
	{
		// Setup and sanitize all vars
		$name = esc_attr( $field['name'] );
		$field_type = !empty( $field['type'] ) ? esc_attr( $field['type'] ) : 'checkbox';

		// ID, Name, Label, Description
		$field_id = esc_attr( $this->plugin_options->get_option_prefix() . '_' . $name );
		$field_name = esc_attr( $this->plugin_options->get_option_name() . '[' . $name . ']' );

		// Label and description
		$field_label_classes = esc_attr( 'form-label form-label-' . $field_type );
		$field_label = !empty( $field['label'] ) ? '<label for="' . $field_id . '" class="' . $field_label_classes . '">' . \nl2br( $this->replace_settings_field_placeholders( $field['label'] ) ) . '</label>' : '';
		$field_description = !empty( $field['description'] ) ? '<p class="description">' . \nl2br( $this->replace_settings_field_placeholders( $field['description'] ) ) . '</p>' : '';

		// Values (escaped later)
		$field_value = $field_type === 'checkbox' ? 1 : $field['value'];
		$field_value_current = $this->get_option( $name );

		// Classes
		$field_classes = esc_attr( 'form-control form-control-' . $field_type . ( !empty( $field['class'] ) ? ' ' . $field['class'] : '' ) );
		$field_wrapper_classes = esc_attr( 'form-group form-group-' . $field_type . ( !empty( $field['premium'] ) ? ' premium' : '' ) );

		// Build Default Input
		$field_input = '<input type="' . $field_type . '" id="' . $field_id . '" name="' . $field_name . '" class="' . $field_classes . '"';
		// Input value
		// If value is Array: select options, radio groups
		$field_input .= !\is_array( $field_value ) ? ' value="' . esc_attr( $field_value ) . '"' : '';
		// Input Specials
		$field_input .= $field_type === 'checkbox' ? checked( 1, $field_value_current, \false ) : '';
		$field_input .= disabled( 1, !empty( $field['disabled'] ), \false );
		$field_input .= !empty( $field['min'] ) ? ' min="' . esc_attr( $field['min'] ) . '"' : '';
		$field_input .= !empty( $field['max'] ) ? ' max="' . esc_attr( $field['max'] ) . '"' : '';
		// Wrap it up
		$field_input .= '>';

		$output = '';

		switch ( $field_type )
		{
			case 'textarea':
				$output .= $field_label;
				$output .= '<textarea id="' . $field_id . '" name="' . $field_name . '" class="' . $field_classes . '"';
				// Input Specials
				$output .= !empty( $field['rows'] ) ? ' rows="' . esc_attr( $field['rows'] ) . '"' : '';
				$output .= !empty( $field['cols'] ) ? ' cols="' . esc_attr( $field['cols'] ) . '"' : '';
				$output .= disabled( 1, !empty( $field['disabled'] ), \false );
				// Wrap it up
				$output .= '>' . $field_value_current . '</textarea>' . $field_description;
				break;

			case 'radio':
				$output .= '<fieldset><legend>' . $field_label . '</legend>';
				foreach( $field_value as $radio_id => $radio )
				{
					// Open it up
					$output .= '<div class="' . $field_wrapper_classes . '">';
					// Input
					$output .= '<input type="' . $field_type . '" id="' . esc_attr( $radio_id ) . '" name="' . $field_name .'" value="' . esc_attr( $radio['value'] ) . '" class="' . $field_classes . '"';
					// Input Specials
					$output .= checked( $radio['value'], $field_value_current, \false );
					$output .= disabled( 1, !empty( $radio['disabled'] ), \false );
					$output .= '>';
					// Label for subelement
					$output .= '<label for="' . $radio_id . '" class="' . $field_label_classes . '">' . \nl2br( $this->replace_settings_field_placeholders( $radio['label'] ) ) . '</label>';
					// Wrap it up
					$output .= '</div>';
				}
				$output .= $field_description . '</fieldset>';
				break;

			case 'select':
				// Label
				$output .= $field_label;
				// Input
				$output .= '<select id="' . $field_id . '" name="' . $field_name . '" class="' . $field_classes . '">';
				foreach( $field_value as $option )
				{
					// Input
					$output .= '<option value="' . esc_attr( $option['value'] ) . '"';
					// Input Specials
					$output .= selected( $field_value_current, $option['value'], \false );
					$output .= disabled( 1, !empty( $option['disabled'] ), \false );
					// Wrap it up
					$output .= '>' . esc_html( $option['label'] ) . '</option>';
				}
				// Wrap it up
				$output .= '</select>' . $field_description;
				break;

			case 'hidden':
				$output = '';
				break;

			case 'checkbox':
				$output .= $field_input;
				$output .= $field_label;
				$output .= $field_description;
				break;

			default:
				$output .= $field_label;
				$output .= $field_input;
				$output .= $field_description;
				break;
		}

		$premium = !empty( $field['premium'] ) ? '<a href="https://propz.de/plugin-tools/eazy-notes/" class="premium-button" title="' . __( 'Get the pro version baby!', 'eazy-notes' ) . '" target="_blank" rel="noopener">' . __( 'Pro Feature', 'eazy-notes' ) . '</a>' : '';

		// Wrap it up
		$output = !empty( $output ) ? '<div class="' . $field_wrapper_classes . '">' . $premium . $output . '</div>' : '';

		echo wp_kses( $output, $this->plugin_options->get_allowed_html() );
	}

	/**
	 * Sanitize the submitted options.
	 * 
	 * This method sanitizes the user input before the Settings API saves it to the database.
	 * 
	 * Context: sanitize_callback for register_setting
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
	 * Get the plugin url
	 * 
	 * @return string Plugin url.
	 */
	public function get_plugin_url(): string
	{
		return $this->plugin_url;
	}

	/**
	 * Get the saved plugin options
	 * 
	 * Combine wp_parse_args with get_option to load new defaults:
	 * https://stackoverflow.com/a/27516495/4371770
	 * 
	 * @return array All saved options.
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
	public function get_option( string $option_name )
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
	 * 
	 * @return string Post type name.
	 */
	public function get_post_type(): string
	{
		return $this->get_option( 'post_type' );
	}

	/**
	 * Get Note name singular
	 * 
	 * @return string Note name singular.
	 */
	public function get_note_name(): string
	{
		return $this->get_option( 'note_name' );
	}

	/**
	 * Get Note name singular
	 * 
	 * @return string Note name plural.
	 */
	public function get_note_name_plural(): string
	{
		return $this->get_option( 'note_name_plural' );
	}

	/**
	 * Enable Gutenberg Editor
	 * 
	 * Filter: use_block_editor_for_post_type, gutenberg_can_edit_post_type
	 * 
	 * @param bool $current_status Current status if block editor is enable.
	 * @param string $post_type Post type.
	 * @return bool Enable or not.
	 */
	public function maybe_enable_block_editor( bool $current_status, string $post_type ): bool
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
	 * Filter: rest_pre_dispatch
	 * 
	 * @param mixed $result API request result.
	 * @param WP_REST_Server $server
	 * @param WP_REST_Request $request
	 * @return mixed Result if logged in
	 */
	public function restrict_rest_api_access( $result, \WP_REST_Server $server, \WP_REST_Request $request )
	{
		if ( !\str_contains( $request->get_route(), '/' . sanitize_title_with_dashes( $this->get_note_name_plural() ) ) ) return $result;

		// Überprüfe, ob der Benutzer eingeloggt ist
		if ( !is_user_logged_in() )
		{
			return new \WP_Error(
				'rest_forbidden',
				/* translators: %s: note name plural */
				\sprintf( __( '%s are only available for logged in users.', 'eazy-notes' ), $this->get_note_name_plural() ),
				[ 'status' => 401 ]
			);
		}

		// Überprüfe, ob der Benutzer die erforderlichen Fähigkeiten hat
		$edit_note_capability = apply_filters( 'eazy_notes_edit_note_capability', 'edit_posts' );
		if ( !current_user_can( $edit_note_capability ) )
		{
			return new \WP_Error(
				'rest_forbidden',
				/* translators: %s: note name plural */
				\sprintf( __( '%1$s are only available for roles with `%2$s` capability.', 'eazy-notes' ), $this->get_note_name_plural(), $edit_note_capability ),
				[ 'status' => 403 ]
			);
		}
	
		return $result;
	}

	/**
	 * Replace all settings fiel label and description placeholders
	 * 
	 * Context: render_settings_field
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
	 * 
	 * Action: init
	 * 
	 * @return void
	 */
	public function load_plugin_textdomain(): void
	{
		$plugin_rel_path = $this->get_plugin_id() . '/languages';
		load_plugin_textdomain( 'eazy-notes', false, $plugin_rel_path );
	}

	/**
	 * Register the stylesheets for the admin area.
	 * 
	 * Action: admin_enqueue_scripts
	 * 
	 * @return void
	 */
	public function enqueue_styles(): void
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
				$this->get_plugin_url() . 'admin/css/' . $this->get_plugin_id() . '-admin.css',
				[],
				$this->plugin_version
			);
		}
	}
}