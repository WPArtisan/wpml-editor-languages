<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://ozthegreat.io/wpml-editor-languages
 * @since      1.0.0
 *
 * @package    Wpml_Editor_Languages
 * @subpackage Wpml_Editor_Languages/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wpml_Editor_Languages
 * @subpackage Wpml_Editor_Languages/admin
 * @author     OzTheGreat <edward@ozthegreat.io>
 */
class Wpml_Editor_Languages_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * This inspects the global Sitepress object then uses the
	 * ReflectionClass to override the active_languages property
	 * dependent on the allowed languages for the current user,
	 * @access public
	 * @return null
	 */
	public function set_allowed_languages() {
		// Admins can edit any language
	 	if ( current_user_can( 'manage_options' ) )
	 		return;

		global $sitepress;

		$reflection_class = new ReflectionClass('Sitepress');

		// `active_languages` property is set to private,
		// override that using the relflection class.
		$active_languages_property = $reflection_class->getProperty('active_languages');
		$active_languages_property->setAccessible(true);

		$active_languages = $active_languages_property->getValue( $sitepress );
		$user_languages    = array_flip( $this->get_user_allowed_languages( get_current_user_id() ) );
		$active_languages = array_intersect( $active_languages, $user_languages );

		$active_languages_property->setValue( $sitepress, $active_languages );

		// Will die if they try to switch surreptitiously
		if ( ! isset( $user_languages[ ICL_LANGUAGE_CODE ] ) )
		{
			// Restrict access
			do_action('admin_page_access_denied');
			$backLink = '<a href="' . admin_url() . '?lang=' . key( $user_languages ) . '">' . __('Back to home') . '</a>';

			wp_die( __('You cannot modify or delete this entry. ' . $backLink) );
			exit;
		}

	}

	/**
	 * When a User first logs in to the admin, check the default
	 * language is in their allowed languages, otherwise show an error
	 * and redirect to ther first allowed langauage
	 * @access public
	 * @param  string $redirect_to
	 * @param  array  $request     [description]
	 * @param  obj    $user        [description]
	 * @return string
	 */
	public function login_allowd_languages_redirect( $redirect_to, $request, $user ) {

		// If no $user is set or the user is an admin, continue
		if ( empty( $user->ID ) || current_user_can( 'manage_options' ) )
			return $redirect_to;

	 	if ( $userLanguage = get_user_meta( $user->ID, 'icl_admin_language', true ) )
	 	{
	 		return admin_url() . '?lang=' . $userLanguage;
	 	}

	 	return $redirect_to;
	}

	/**
	 * For Admin only users, show a form on the User profile page
	 * allowing you them to specify the languages that User can edit.
	 * @access public
	 * @param  obj    $user Standard WP User object
	 * @return null
	 */
	public function add_user_languages_persmissions($user) {
		// If not an Admin, they can't edit it
	 	if ( ! current_user_can( 'manage_options' ) )
	 		return;

		$languages = icl_get_languages('skip_missing=N&orderby=KEY&order=DIR&link_empty_to=str');
		$user_languages = array_flip( $this->get_user_allowed_languages( $user->ID ) );

		include 'partials/wpml-editor-languages-user-languages-select.php';
	}

	/**
	 * When saving a User profile as Admin, update the dba_list
	 * of languages that User is allowed to access
	 * @access public
	 * @param  int $id The ID of the User to edit
	 * @return void
	 */
	public function save_user_languages_allowed($id) {
		// If not an Admin, they can't edit it
		if ( ! current_user_can( 'manage_options' ) )
			return;

		$languages_allowed = ! empty( $_POST['languages_allowed'] ) ? $_POST['languages_allowed'] : array() ;

		update_user_meta( $id,'languages_allowed', sanitize_text_field( json_encode( $languages_allowed ) ) );

		$languages_allowed = array_flip( $languages_allowed );

		if ( ! isset( $languages_allowed[ get_user_meta( $id, 'icl_admin_language', true ) ] ) )
		{
			update_user_meta( $id,'icl_admin_language', key( $languages_allowed ) );
		}
	}

	/**
	 * Returns an array of all the languages a user is allowed to edit
	 * @param   int $user_id
	 * @return  array
	 */
	public function get_user_allowed_languages($user_id) {
		$user_languages = json_decode( get_the_author_meta( 'languages_allowed', $user_id ) );
		return ! empty( $user_languages ) && is_array( $user_languages ) ? $user_languages : array();
	}

}
