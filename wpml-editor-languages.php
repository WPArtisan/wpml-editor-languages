<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://ozthegreat.io/wordpress/wpml-editor-languages
 * @since             1.0.0
 * @package           wpml-editor-languages
 *
 * @wordpress-plugin
 * Plugin Name:       WPML Editor Languages
 * Plugin URI:        https://ozthegreat.io/wordpress/wpml-editor-languages
 * Description:       Allows editiors to be restricted to languages with WPML
 * Version:           1.0.0
 * Author:            OzTheGreat
 * Author URI:        http://ozthegreat.io
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wpml-editor-language
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define plugin specifc constants used throughout
DEFINE('WPML_EDITOR_LANGUAGES_VERSION', '1.0.0');
DEFINE('WPML_EDITOR_LANGUAGES_PLUGIN_NAME', 'wpml-editor-languages');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wpml-editor-languages-activator.php
 */
function activate_wpml_editor_languages() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpml-editor-languages-activator.php';
	Wpml_Editor_Languages_Activator::activate();
}

register_activation_hook( __FILE__, 'activate_wpml_editor_languages' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wpml-editor-languages.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wpml_editor_languages() {

	$plugin = new Wpml_Editor_Languages();
	$plugin->run();

}
run_wpml_editor_languages();
