<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://ozthegreat.io/wordpress/wpml-editor-languages
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
	 * @access   public
	 * @param    string  $plugin_name  The name of this plugin.
	 * @param    string  $version      The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * This inspects the global Sitepress object then uses the
	 * ReflectionClass to override the active_languages property
	 * dependent on the allowed languages for the current user.
	 *
	 * @access  public
	 * @return  null
	 */
	public function set_allowed_languages() {
		// Admins can edit any language
		if ( current_user_can( 'manage_options' ) )
			return;

		global $sitepress;

		// If there's no global $sitepress object
		// there's nothing to do here
		if ( empty( $sitepress ) )
			return;

		$reflection_class = new ReflectionClass('Sitepress');

		// `active_languages` property is set to private,
		// override that using the relflection class.
		$active_languages_property = $reflection_class->getProperty('active_languages');
		$active_languages_property->setAccessible(true);

		$active_languages = $active_languages_property->getValue( $sitepress );
		$user_languages   = array_flip( $this->get_user_allowed_languages( get_current_user_id() ) );
		$active_languages = array_intersect_key( $active_languages, $user_languages );
		$active_languages = apply_filters( 'wpmlel_active_languages', $active_languages );

		$active_languages_property->setValue( $sitepress, $active_languages );

		// Will die if they try to switch surreptitiously
		if ( ! isset( $user_languages[ ICL_LANGUAGE_CODE ] ) )
		{
			do_action('admin_page_access_denied');

			wp_die( sprintf(
				wp_kses(
					__( 'You cannot modify or delete this entry. <a href="%s">Back to home</a>', 'wpml-editor-languages' ),
					array(  'a' => array( 'href' => true, 'title' => true, 'target' => true ) )
				),
				esc_url_raw( admin_url() . '?lang=' . key( $user_languages ) )
			) );

			exit;
		}

	}

	/**
	 * When a User first logs in to the admin, check the default
	 * language is in their allowed languages, otherwise show an error
	 * and redirect to ther first allowed langauage.
	 *
	 * @access public
	 * @param  string $redirect_to
	 * @param  array  $request
	 * @param  obj    $user
	 * @return string
	 */
	public function login_allowd_languages_redirect( $redirect_to, $request, $user ) {
		// If no $user is set or the user is an admin, continue
		if ( empty( $user->ID ) || current_user_can( 'manage_options' ) )
			return $redirect_to;

		if ( $user_language = get_user_meta( $user->ID, 'icl_admin_language', true ) )
		{
			return esc_url_raw( apply_filters( 'wpmlel_admin_redirect', admin_url() . '?lang=' . $user_language ) );
		}

		return $redirect_to;
	}

	/**
	 * For Admin users users, show a form on the User profile page
	 * allowing you them to specify the languages that User can edit.
	 *
	 * @access public
	 * @param  obj    $user Standard WP User object
	 * @return null
	 */
	public function add_user_languages_persmissions( $user ) {
		// If not an Admin, they can't edit it
		if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'icl_get_languages' ) )
			return;

		global $pagenow;

		$languages = icl_get_languages( 'skip_missing=N&orderby=KEY&order=DIR&link_empty_to=str' );

		if ( $pagenow == 'user-new.php' ) {
			global $sitepress;
			$user_languages = array( $sitepress->get_default_language() => true );
		} else {
			$user_languages = array_flip( $this->get_user_allowed_languages( $user->ID ) );
		}

		include 'partials/wpml-editor-languages-user-languages-select.php';
	}

	/**
	 * When saving a User profile as Admin, update the list
	 * of languages that User is allowed to access.
	 *
	 * @access public
	 * @param  int $id The ID of the User to edit
	 * @return void
	 */
	public function save_user_languages_allowed( $user_id ) {
		// If not an Admin, they can't edit it
		if ( ! current_user_can( 'manage_options' ) )
			return;

		$languages_allowed = ! empty( $_POST['languages_allowed'] ) ? $_POST['languages_allowed'] : array();
		$languages_allowed = (array) apply_filters( 'wpmlel_save_user_languages', $languages_allowed, $user_id );

		update_user_option( $user_id,'languages_allowed', sanitize_text_field( json_encode( $languages_allowed ) ) );

		$languages_allowed = array_flip( $languages_allowed );

		// Check the default admin language is in the Users' allowed languages
		if ( ! isset( $languages_allowed[ get_user_meta( $user_id, 'icl_admin_language', true ) ] ) )
		{
			update_user_option( $user_id, 'icl_admin_language', key( $languages_allowed ) );
		}
	}

	/**
	 * Returns an array of all the languages a user is allowed to edit.
	 *
	 * @access  public
	 * @param   int $user_id
	 * @return  array
	 */
	public function get_user_allowed_languages( $user_id ) {
		$user_languages = json_decode( get_user_option( 'languages_allowed', $user_id ) );
		$user_languages = apply_filters( 'wpmlel_user_languages', $user_languages, $user_id );
		return ! empty( $user_languages ) && is_array( $user_languages ) ? $user_languages : array();
	}

}
