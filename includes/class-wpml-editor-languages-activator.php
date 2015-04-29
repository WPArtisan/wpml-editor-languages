<?php

/**
 * Fired during plugin activation
 *
 * @link       http://ozthegreat.io/wpml-editor-languages
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
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
        self::check_php_version();
        self::check_reflection_class_exists();
        self::check_user_can_activate_plugins();
        self::check_wpml_activated();
	}

    public static function check_php_version() {
		global $required_php_version;

		if ( version_compare( PHP_VERSION, $required_php_version, '<' ) )
        {
            self::deactivate_plugin( sprintf(
                __(
                    'WPML Editor Languages requires PHP % or higher, as does WordPress 3.2 and higher.
                    The plugin has now disabled itself. For more info see the WordPress
                    <a href="http://wordpress.org/about/requirements/">requirements page</a>',
                    WPML_EDITOR_LANGUAGES_TEXT_DOMAIN
                ),
            $required_php_version ) );
		}
    }

    public static function check_reflection_class_exists() {
		if ( ! class_exists("ReflectionClass") )
        {
            self::deactivate_plugin( __( 'The PHP ReflectionClass is required to use this plugin. The plugin has now disabled itself.', WPML_EDITOR_LANGUAGES_TEXT_DOMAIN ) );
		}
    }

    public static function check_user_can_activate_plugins() {
        if ( ! current_user_can( 'activate_plugins' ) )
        {
            self::deactivate_plugin( __( 'You do not have sufficient privileges to activate this plugin.', WPML_EDITOR_LANGUAGES_TEXT_DOMAIN ) );
		}
    }

    public static function check_wpml_activated() {
        if ( ! is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) )
        {
            self::deactivate_plugin( __( 'This plugin is an extension for WPML and is usless without. You can purchase WPML <a href="https://wpml.org/">here</a>', WPML_EDITOR_LANGUAGES_TEXT_DOMAIN ) );
		}
    }

    public static function deactivate_plugin($error_message) {
        require_once ABSPATH . '/wp-admin/includes/plugin.php';
        deactivate_plugins( plugin_basename( __FILE__ ) );
        wp_die( $error_message );
    }

}
