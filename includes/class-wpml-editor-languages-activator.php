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

    protected static $language_domain = 'wpml-editor-languages';

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
        self::check_php_version();
        self::check_wpml_activated();
	}

    public static function check_php_version() {

		global $required_php_version;

		if ( version_compare( PHP_VERSION, $required_php_version, '<' ) )
        {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
			deactivate_plugins( __FILE__ );
			wp_die( sprintf( __( 'WPML Editor Languages requires PHP % or higher, as does WordPress 3.2 and higher. The plugin has now disabled itself. For more info see the WordPress <a href="http://wordpress.org/about/requirements/">requirements page</a>', self::$language_domain ), $required_php_version ) );
		}

    }

    public static function check_user_can_activate_plugins() {

        if ( ! is_plugin_active( 'wpml/sitepress.php' ) )
        {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
			deactivate_plugins( __FILE__ );
			wp_die( __( 'You do not have sufficient privileges to activate this plugin.', self::$language_domain ) );
		}

    }

    public static function check_wpml_activated() {

        if ( ! current_user_can( 'activate_plugins' ) )
        {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
			deactivate_plugins( __FILE__ );
			wp_die( __( 'You do not have sufficient privileges to activate this plugin.', self::$language_domain ) );
		}

    }

}
