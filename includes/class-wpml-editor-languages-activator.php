<?php

/**
 * Fired during plugin activation
 *
 * @link       https://ozthegreat.io/wordpress/wpml-editor-languages
 * @since      1.0.0
 *
 * @package    Wpml_Editor_Languages
 * @subpackage Wpml_Editor_Languages/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wpml_Editor_Languages
 * @subpackage Wpml_Editor_Languages/includes
 * @author     OzTheGreat <edward@ozthegreat.io>
 */
class Wpml_Editor_Languages_Activator {

	/**
	 * Registers any checks to run on plugin activation.
	 *
	 * @since   1.0.0
	 * @return  null
	 */
	public static function activate() {
		self::check_php_version();
		self::check_reflection_class_exists();
		self::check_user_can_activate_plugins();
		self::check_wpml_activated();
	}

	/**
	 * Does a check to make sure that the PHP version is equal or
	 * greater than the WordPress minimum.
	 *
	 * @return null
	 */
	public static function check_php_version() {
		global $required_php_version;

		if ( version_compare( PHP_VERSION, $required_php_version ) < 0 )
		{
			self::deactivate_plugin( sprintf(
				wp_kses(
					__(
						'WPML Editor Languages requires PHP %s or higher, as does WordPress 3.2 and higher.
						The plugin has now disabled itself. For more info see the WordPress
						<a href="%s">requirements page</a>',
						'wpml-editor-languages'
					),
					array(  'a' => array( 'href' => true, 'title' => true, 'target' => true ) )
				),
				$required_php_version,
				esc_url_raw( 'https://wordpress.org/about/requirements/' )
			) );
		}
	}

	/**
	 * Does a check to make sure that the PHP reflection class
	 * exists and can be used.
	 *
	 * @return null
	 */
	public static function check_reflection_class_exists() {
		if ( ! class_exists("ReflectionClass") )
		{
			self::deactivate_plugin( __( 'The PHP ReflectionClass is required to use this plugin. The plugin has now disabled itself.', 'wpml-editor-languages' ) );
		}
	}

	/**
	 * Does a check to make sure that the current user has
	 * the correct permissions to activate plugins.
	 *
	 * @return null
	 */
	public static function check_user_can_activate_plugins() {
		if ( ! current_user_can( 'activate_plugins' ) )
		{
			self::deactivate_plugin( __( 'You do not have sufficient privileges to activate this plugin.', 'wpml-editor-languages' ) );
		}
	}

	/**
	 * Does a check to make sure that WPML is installed and active.
	 *
	 * @return null
	 */
	public static function check_wpml_activated() {
		if ( ! is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) )
		{
			self::deactivate_plugin( sprintf(
				wp_kses(
					__(
						'This plugin is an extension for WPML and is usless without. You can purchase WPML <a href="%s">here</a>',
						'wpml-editor-languages'
					),
					array(  'a' => array( 'href' => true, 'title' => true, 'target' => true ) )
				),
				esc_url_raw( 'https://wpml.org/' )
			) );
		}
	}

	/**
	 * A generic function for deactivating this plugin and
	 * posting a WordPress die message.
	 *
	 * @param  string $error_message Error message to display on die page
	 * @return null
	 */
	public static function deactivate_plugin($error_message) {
		require_once ABSPATH . '/wp-admin/includes/plugin.php';
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( $error_message );
	}

}
