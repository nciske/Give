<?php
/**
 * Give Admin Settings Class
 *
 * @package     Give
 * @subpackage  Classes/Give_Admin_Settings
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Give_Admin_Settings' ) ) :

	/**
	 * Give_Admin_Settings Class.
	 *
	 * @since 1.8
	 */
	class Give_Admin_Settings {

		/**
		 * Setting pages.
		 *
		 * @since 1.8
		 * @var   array List of settings.
		 */
		private static $settings = array();

		/**
		 * Setting filter and action prefix.
		 *
		 * @since 1.8
		 * @var   string setting fileter and action anme prefix.
		 */
		private static $setting_filter_prefix = '';

		/**
		 * Error messages.
		 *
		 * @since 1.8
		 * @var   array List of errors.
		 */
		private static $errors = array();

		/**
		 * Update messages.
		 *
		 * @since 1.8
		 * @var   array List of messages.
		 */
		private static $messages = array();

		/**
		 * Include the settings page classes.
		 *
		 * @since  1.8
		 * @return array
		 */
		public static function get_settings_pages() {
			/**
			 * Filter the setting page.
			 *
			 * Note: filter dynamically fire on basis of setting page slug.
			 * For example: if you register a setting page with give-settings menu slug
			 *              then filter will be give-settings_get_settings_pages
			 *
			 * @since 1.8
			 *
			 * @param array $settings Array of settings class object.
			 */
			self::$settings = apply_filters( self::$setting_filter_prefix . '_get_settings_pages', array() );

			return self::$settings;
		}

		/**
		 * Save the settings.
		 *
		 * @since  1.8
		 * @return void
		 */
		public static function save() {
			$current_tab = give_get_current_setting_tab();

			if ( empty( $_REQUEST['_give-save-settings'] ) || ! wp_verify_nonce( $_REQUEST['_give-save-settings'], 'give-save-settings' ) ) {
				die( __( 'Action failed. Please refresh the page and retry.', 'give' ) );
			}

			/**
			 * Trigger Action.
			 *
			 * Note: action dynamically fire on basis of setting page slug and current tab.
			 * For example: if you register a setting page with give-settings menu slug and general current tab name
			 *              then action will be give-settings_save_general
			 *
			 * @since 1.8
			 */
			do_action( self::$setting_filter_prefix . '_save_' . $current_tab );

			self::add_message( 'give-setting-updated', __( 'Your settings have been saved.', 'give' ) );

			/**
			 * Trigger Action.
			 *
			 * Note: action dynamically fire on basis of setting page slug.
			 * For example: if you register a setting page with give-settings menu slug
			 *              then action will be give-settings_saved
			 *
			 * @since 1.8
			 */
			do_action( self::$setting_filter_prefix . '_saved' );
		}

		/**
		 * Add a message.
		 *
		 * @since  1.8
		 *
		 * @param  string $code    Message code (Note: This should be unique).
		 * @param  string $message Message text.
		 *
		 * @return void
		 */
		public static function add_message( $code, $message ) {
			self::$messages[ $code ] = $message;
		}

		/**
		 * Add an error.
		 *
		 * @since  1.8
		 *
		 * @param  string $code    Message code (Note: This should be unique).
		 * @param  string $message Message text.
		 *
		 * @return void
		 */
		public static function add_error( $code, $message ) {
			self::$errors[ $code ] = $message;
		}

		/**
		 * Output messages + errors.
		 *
		 * @since  1.8
		 * @return void
		 */
		public static function show_messages() {
			$notice_html = '';
			$classes     = 'give-notice settings-error notice is-dismissible';

			self::$errors   = apply_filters( self::$setting_filter_prefix . '_error_notices', self::$errors );
			self::$messages = apply_filters( self::$setting_filter_prefix . '_update_notices', self::$messages );

			if ( 0 < count( self::$errors ) ) {
				foreach ( self::$errors as $code => $message ) {
					$notice_html .= '<div id="setting-error-' . $code . '" class="' . $classes . ' error"><p><strong>' . $message . '</strong></p></div>';
				}
			}

			if ( 0 < count( self::$messages ) ) {
				foreach ( self::$messages as $code => $message ) {
					$notice_html .= '<div id="setting-error-' . $code . '" class="' . $classes . ' updated"><p><strong>' . $message . '</strong></p></div>';
				}
			}

			echo $notice_html;
		}

		/**
		 * Settings page.
		 *
		 * Handles the display of the main give settings page in admin.
		 *
		 * @since  1.8
		 *
		 * @return bool
		 */
		public static function output() {
			// Get current setting page.
			self::$setting_filter_prefix = give_get_current_setting_page();

			// Bailout: Exit if setting page is not defined.
			if ( empty( self::$setting_filter_prefix ) ) {
				return false;
			}

			/**
			 * Trigger Action.
			 *
			 * Note: action dynamically fire on basis of setting page slug
			 * For example: if you register a setting page with give-settings menu slug
			 *              then action will be give-settings_start
			 *
			 * @since 1.8
			 */
			do_action( self::$setting_filter_prefix . '_start' );

			$current_tab = give_get_current_setting_tab();

			// Include settings pages.
			self::get_settings_pages();

			// Save settings if data has been posted.
			if ( ! empty( $_POST ) ) {
				self::save();
			}

			/**
			 * Filter the tabs for current setting page.
			 *
			 * Note: filter dynamically fire on basis of setting page slug.
			 * For example: if you register a setting page with give-settings menu slug and general current tab name
			 *              then action will be give-settings_tabs_array
			 *
			 * @since 1.8
			 */
			$tabs = apply_filters( self::$setting_filter_prefix . '_tabs_array', array() );

			include 'views/html-admin-settings.php';

			return true;
		}

		/**
		 * Get a setting from the settings API.
		 *
		 * @since  1.8
		 *
		 * @param  string $option_name
		 * @param  string $field_id
		 * @param  mixed  $default
		 *
		 * @return array|string|bool
		 */
		public static function get_option( $option_name = '', $field_id = '', $default = false ) {
			// Bailout.
			if ( empty( $option_name ) && empty( $field_id ) ) {
				return false;
			}

			if ( ! empty( $field_id ) && ! empty( $option_name ) ) {
				// Get field value if any.
				$option_value = get_option( $option_name );

				$option_value = ( is_array( $option_value ) && array_key_exists( $field_id, $option_value ) )
					? $option_value[ $field_id ]
					: $default;
			} else {
				// If option name is empty but not field name then this means, setting is direct store to option table under there field name.
				$option_name = ! $option_name ? $field_id : $option_name;

				// Get option value if any.
				$option_value = get_option( $option_name, $default );
			}

			return $option_value;
		}

		/**
		 * Output admin fields.
		 *
		 * Loops though the give options array and outputs each field.
		 *
		 * @since  1.8
		 *
		 * @param  array  $options     Opens array to output
		 * @param  string $option_name Opens array to output
		 *
		 * @return void
		 */
		public static function output_fields( $options, $option_name = '' ) {
			$current_tab = give_get_current_setting_tab();

			// Field Default values.
			$defaults = array(
				'id'         => '',
				'name'       => '',
				'class'      => '',
				'css'        => '',
				'default'    => '',
				'desc'       => '',
				'table_html' => true,
			);

			foreach ( $options as $value ) {
				if ( ! isset( $value['type'] ) ) {
					continue;
				}

				// Set default setting.
				$value = wp_parse_args( $value, $defaults );


				// Custom attribute handling.
				$custom_attributes = array();

				if ( ! empty( $value['attributes'] ) && is_array( $value['attributes'] ) ) {
					foreach ( $value['attributes'] as $attribute => $attribute_value ) {
						$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
					}
				}

				// Description handling.
				$description          = self::get_field_description( $value );

				// Switch based on type.
				switch ( $value['type'] ) {

					// Section Titles
					case 'title':
						if ( self::get_field_title( $value ) ) {
							echo '<div class="give-setting-tab-header give-setting-tab-header-' . $current_tab . '"><h2>' . self::get_field_title( $value ) . '</h2><hr></div>';
						}

						if ( ! empty( $value['desc'] ) ) {
							echo wpautop( wptexturize( wp_kses_post( $value['desc'] ) ) );
						}

						if ( $value['table_html'] ) {
							echo '<table class="form-table give-setting-tab-body give-setting-tab-body-' . $current_tab . '">' . "\n\n";
						}

						if ( ! empty( $value['id'] ) ) {

							/**
							 * Trigger Action.
							 *
							 * Note: action dynamically fire on basis of field id.
							 *
							 * @since 1.8
							 */
							do_action( 'give_settings_' . sanitize_title( $value['id'] ) );
						}

						break;

					// Section Ends.
					case 'sectionend':
						if ( ! empty( $value['id'] ) ) {

							/**
							 * Trigger Action.
							 *
							 * Note: action dynamically fire on basis of field id.
							 *
							 * @since 1.8
							 */
							do_action( 'give_settings_' . sanitize_title( $value['id'] ) . '_end' );
						}

						if ( $value['table_html'] ) {
							echo '</table>';
						}

						if ( ! empty( $value['id'] ) ) {

							/**
							 * Trigger Action.
							 *
							 * Note: action dynamically fire on basis of field id.
							 *
							 * @since 1.8
							 */
							do_action( 'give_settings_' . sanitize_title( $value['id'] ) . '_after' );
						}

						break;

					// Standard text inputs and subtypes like 'number'.
					case 'colorpicker':
						$value['field_attributes']['class'] = trim( $value['class'] ) . ' give-colorpicker';
						$value['type'] = 'text';

					case 'api_key' :
						$value['value']  = self::get_option( $option_name, $value['id'], $value['default'] );
						$value['type'] = ! empty( $value['value'] ) ? 'password' : 'text';

					case 'text':
					case 'email':
					case 'number':
					case 'password' :
						$value = give_backward_compatibility_setting_api_1_8( $value );

						// Set layout.
						$value = array_merge( $value, self::get_field_wrapper( $value, $option_name ) );

						// Add input specific class.
						$value['field_attributes']['class'] = empty( $value['field_attributes']['class'] )
							? 'give-input-field'
							: trim( $value['field_attributes']['class'] ) . ' give-input-field';

						// Render function.
						echo Give_Fields_API::render_tag( $value );

						break;

					// Textarea.
					case 'textarea':
						$value = give_backward_compatibility_setting_api_1_8( $value );

						// Set field value.
						$value['value'] = esc_textarea( self::get_option( $option_name, $value['id'], $value['default'] ) );

						// Set layout.
						$value = array_merge( $value, self::get_field_wrapper( $value, $option_name ) );

						// Add rows and cols for textarea.
						$value['field_attributes']['rows'] = 10;
						$value['field_attributes']['cols'] = 60;

						echo Give_Fields_API::render_tag( $value );
						break;

					// Select boxes.
					case 'select' :
					case 'multiselect' :
						$value = give_backward_compatibility_setting_api_1_8( $value );

						// Set layout.
						$value = array_merge( $value, self::get_field_wrapper( $value, $option_name ) );

						// Update td wrapper.
						$value['before_field'] = '<td class="give-forminp give-forminp-' . sanitize_title( $value['type'] ) . '"><fieldset>';
						$value['after_field']  = "{$description}</fieldset></td>";


						// Update param for radio_inline field type.
						if( 'radio_inline' === $value['type'] ) {
							$value['type']  = 'radio';
							$value['wrapper_attributes']['class'] = empty( $value['wrapper_attributes']['class'] )
								? 'give-radio-inline'
								: trim( $value['wrapper_attributes']['class'] ) . ' give-radio-inline';
						}

						echo Give_Fields_API::render_tag( $value );
						break;

					// Radio inputs.
					case 'radio_inline' :
					case 'radio' :
						$value = give_backward_compatibility_setting_api_1_8( $value );

						// Set layout.
						$value = array_merge( $value, self::get_field_wrapper( $value, $option_name ) );

						// Update td wrapper.
						$value['before_field'] = '<td class="give-forminp give-forminp-' . sanitize_title( $value['type'] ) . '"><fieldset>';
						$value['after_field']  = "{$description}</fieldset></td>";


						// Update param for radio_inline field type.
						if( 'radio_inline' === $value['type'] ) {
							$value['type']  = 'radio';
							$value['wrapper_attributes']['class'] = empty( $value['wrapper_attributes']['class'] )
								? 'give-radio-inline'
								: trim( $value['wrapper_attributes']['class'] ) . ' give-radio-inline';
						}

						echo Give_Fields_API::render_tag( $value );
						break;

					// Checkbox input.
					case 'checkbox' :
						$value = give_backward_compatibility_setting_api_1_8( $value );

						// Set layout.
						$value = array_merge( $value, self::get_field_wrapper( $value, $option_name ) );

						echo Give_Fields_API::render_tag( $value );
						break;

					// Multi Checkbox input.
					case 'multicheck' :
						$value = give_backward_compatibility_setting_api_1_8( $value );

						// Set layout.
						$value = array_merge( $value, self::get_field_wrapper( $value, $option_name ) );

						echo Give_Fields_API::render_tag( $value );
						break;

					// File input field.
					case 'file' :
						$value = give_backward_compatibility_setting_api_1_8( $value );

						// Set layout.
						$value = array_merge( $value, self::get_field_wrapper( $value, $option_name ) );

						echo Give_Fields_API::render_tag( $value );
						break;

					// WordPress Editor.
					case 'wysiwyg' :
						$value = give_backward_compatibility_setting_api_1_8( $value );

						// Set field value.
						$value['value'] = wp_kses_post( self::get_option( $option_name, $value['id'], $value['default'] ) );

						// Set layout.
						$value = array_merge( $value, self::get_field_wrapper( $value, $option_name ) );
						
						echo Give_Fields_API::render_tag( $value );
						break;

					// Custom: Give Docs Link field type.
					case 'give_docs_link' :
						$value = give_backward_compatibility_setting_api_1_8( $value );

						// Set layout.
						$value = array_merge( $value, self::get_field_wrapper( $value, $option_name ) );
						$value['before_field'] = '<td class="give-forminp give-forminp-' . sanitize_title( $value['type'] ) . ' give-docs-link" colspan="2">';

						echo Give_Fields_API::render_tag( $value );
						break;

					case 'group':
						$value = give_backward_compatibility_setting_api_1_8( $value );

						// Set field value.
						$value['value'] = self::get_option( $option_name, $value['id'], $value['default'] );

						// Set layout.
						$value = array_merge( $value, self::get_field_wrapper( $value, $option_name ) );
						
						echo Give_Fields_API::render_tag( $value );
						break;

					// Default: run an action
					// You can add or handle your custom field action.
					default:
						$value = give_backward_compatibility_setting_api_1_8( $value );

						// Get option value.
						$option_value = self::get_option( $option_name, $value['id'], $value['default'] );
						do_action( 'give_admin_field_' . $value['type'], $value, $option_value );
						break;
				}
			}
		}

		/**
		 * Helper function to get the formatted description for a given form field.
		 * Plugins can call this when implementing their own custom settings types.
		 *
		 * @since  1.8
		 *
		 * @param  array $value The form field value array
		 *
		 * @return string The HTML description of the field.
		 */
		public static function get_field_description( $value ) {
			$description = '';

			// Support for both 'description' and 'desc' args.
			$description_key = isset( $value['description'] ) ? 'description' : 'desc';
			$value           = ( isset( $value[ $description_key ] ) && ! empty( $value[ $description_key ] ) ) ? $value[ $description_key ] : '';

			if ( ! empty( $value ) ) {
				$description = '<p class="give-field-description">' . wp_kses_post( $value ) . '</p>';
			}

			return $description;
		}


		/**
		 * Helper function to get the formated title.
		 * Plugins can call this when implementing their own custom settings types.
		 *
		 * @since  1.8
		 *
		 * @param  array $value The form field value array
		 *
		 * @return array The description and tip as a 2 element array
		 */
		public static function get_field_title( $value ) {
			// Backward compatibility: version 1.8
			$title = ! empty( $value['id'] )
				? ( ! empty( $value['title'] ) ? $value['title'] : $value['name'] )
				: ( ! empty( $value['label'] ) ? $value['label'] : '' );

			// If html tag detected then allow them to print.
			if ( ! strip_tags( $title ) ) {
				$title = esc_html( $title );
			}

			return $title;
		}

		/**
		 * Save admin fields.
		 *
		 * Loops though the give options array and outputs each field.
		 *
		 * @since  1.8
		 *
		 * @param  array  $options     Options array to output
		 * @param  string $option_name Option name to save output. If empty then option will be store in there own option name i.e option id.
		 *
		 * @return bool
		 */
		public static function save_fields( $options, $option_name = '' ) {
			if ( empty( $_POST ) ) {
				return false;
			}

			// Options to update will be stored here and saved later.
			$update_options = array();

			// Loop options and get values to save.
			foreach ( $options as $option ) {
				if ( ! isset( $option['id'] ) || ! isset( $option['type'] ) ) {
					continue;
				}


				// Get posted value.
				if ( strstr( $option['id'], '[' ) ) {
					parse_str( $option['id'], $option_name_array );
					$field_option_name = current( array_keys( $option_name_array ) );
					$setting_name      = key( $option_name_array[ $field_option_name ] );
					$raw_value         = isset( $_POST[ $field_option_name ][ $setting_name ] ) ? wp_unslash( $_POST[ $field_option_name ][ $setting_name ] ) : null;
				} else {
					$field_option_name = $option['id'];
					$setting_name      = '';
					$raw_value         = isset( $_POST[ $option['id'] ] ) ? wp_unslash( $_POST[ $option['id'] ] ) : null;
				}

				// Format the value based on option type.
				switch ( $option['type'] ) {
					case 'checkbox' :
						$value = is_null( $raw_value )
							? ''
							: ( ! empty( $option['cbvalue'] ) ? $option['cbvalue'] : 'on' );
						break;
					case 'wysiwyg'  :
					case 'textarea' :
						$value = wp_kses_post( trim( $raw_value ) );
						break;
					case 'group' :
						if( ! empty( $raw_value ) ) {
							foreach ( $raw_value as $index => $single_value ) {
								foreach ( $option['fields'] as $single_field ) {
									if(
										! isset( $single_field[ 'type' ] )
										|| ! isset( $single_field[ 'id' ] )
										|| ! isset( $single_value[$single_field['id']] )
									) {
										continue;
									}

									switch ( $single_field[ 'type' ] ) {
										case 'checkbox' :
											$value[$index][$single_field['id']] = is_null( $single_value[$single_field['id']] ) ? '' : 'on';
											break;
										case 'wysiwyg'  :
										case 'textarea' :
											$value[$index][$single_field['id']] = wp_kses_post( trim( $single_value[$single_field['id']] ) );
											break;
										default :
											$value[$index][$single_field['id']] = give_clean( $single_value[$single_field['id']] );
											break;
									}
								}
							}
						}
						break;
					default :
						$value = give_clean( $raw_value );
						break;
				}

				/**
				 * Sanitize the value of an option.
				 *
				 * @since 1.8
				 */
				$value = apply_filters( 'give_admin_settings_sanitize_option', $value, $option, $raw_value );

				/**
				 * Sanitize the value of an option by option name.
				 *
				 * @since 1.8
				 */
				$value = apply_filters( "give_admin_settings_sanitize_option_{$field_option_name}", $value, $option, $raw_value );

				if ( is_null( $value ) ) {
					continue;
				}

				// Check if option is an array and handle that differently to single values.
				if ( $field_option_name && $setting_name ) {
					if ( ! isset( $update_options[ $field_option_name ] ) ) {
						$update_options[ $field_option_name ] = get_option( $field_option_name, array() );
					}
					if ( ! is_array( $update_options[ $field_option_name ] ) ) {
						$update_options[ $field_option_name ] = array();
					}
					$update_options[ $field_option_name ][ $setting_name ] = $value;
				} else {
					$update_options[ $field_option_name ] = $value;
				}
			}

			// Save all options in our array or there own option name i.e. option id.
			if ( empty( $option_name ) ) {
				foreach ( $update_options as $name => $value ) {
					update_option( $name, $value );

					/**
					 * Trigger action.
					 *
					 * Note: This is dynamically fire on basis of option name.
					 *
					 * @since 1.8
					 */
					do_action( "give_save_option_{$name}", $value, $name );
				}
			} else {
				$old_options    = ( $old_options = get_option( $option_name ) ) ? $old_options : array();
				$update_options = array_merge( $old_options, $update_options );

				update_option( $option_name, $update_options );

				/**
				 * Trigger action.
				 *
				 * Note: This is dynamically fire on basis of setting name.
				 *
				 * @since 1.8
				 */
				do_action( "give_save_settings_{$option_name}", $update_options, $option_name );
			}

			return true;
		}


		/**
		 * Get field wrapper.
		 *
		 * @since  1.9
		 * @access private
		 *
		 * @param array  $field
		 * @param string $option_name
		 *
		 * @return array
		 */
		public static function get_field_wrapper( $field, $option_name = '' ) {
			$field_args = array(
				'before_field_label' => ! empty( $field['before_field_label'] )
					? "<th scope=\"row\" class=\"titledesc\">{$field['before_field_label']}"
					: '<th scope="row" class="titledesc">',

				'after_field_label' => ! empty( $field['after_field_label'] )
					? "{$field['after_field_label']}</th>"
					: '</th>',

				'value' => ! empty( $field['value'] )
					? $field['value']
					: give_clean( self::get_option( $option_name, $field['id'], $field['default'] ) ),

				'wrapper_type' => 'tr',

				'before_field' => ! empty( $field['before_field'] )
					? '<td class="give-forminp give-forminp-' . sanitize_title( $field['type'] ) . '">' . $field['before_field']
					: '<td class="give-forminp give-forminp-' . sanitize_title( $field['type'] ) . '">',

				'after_field' => ! empty( $field['after_field'] )
					? self::get_field_description( $field ) . $field['after_field'] . '</td>'
					: self::get_field_description( $field ) . '</td>',
			);

			return $field_args;
		}
	}

endif;
