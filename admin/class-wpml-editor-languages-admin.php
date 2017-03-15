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
	 * WPML has changed how it displays the User allowed languages.
	 * There's a handy filter now. Compare the User allowed languages
	 * with all the available languages.
	 *
	 * @access public
	 * @param array $active_languages Global languages for the user.
	 * @return array
	 */
	public function set_language_switcher_languages( $active_languages ) {
		if ( ! is_user_logged_in() || current_user_can( 'manage_options' ) ) {
			return $active_languages;
		}

		// Get the allowed languages for the user.
		$user_languages = $this->get_user_allowed_languages( get_current_user_id() );

		// If the user doesn't have any language preferences set then bail.
		if ( empty( $user_languages ) ) {
			return $active_languages;
		}

		// Compare the allowed user languages with all the available languages.
		$active_languages = array_intersect_key( $active_languages, array_flip( $user_languages ) );

		// Apply the filter.
		$active_languages = apply_filters( 'wpmlel_active_languages', $active_languages );

		return $active_languages;
	}

	/**
	 * Remove the 'All Languages' options from the languages switcher if
	 * a user has specific languages set.
	 *
	 * @access public
	 * @param  array $items Items for the language swithcer dropdown.
	 * @return array
	 */
	public function remove_all_items_option( $items ) {
		if ( ! is_user_logged_in() || current_user_can( 'manage_options' ) ) {
			return $items;
		}

		// Get the allowed languages for the user.
		$user_languages = $this->get_user_allowed_languages( get_current_user_id() );

		// Unset the 'all' option if they have specific languages set.
		if ( ! empty( $user_languages ) ) {
			unset( $items['all'] );
		}

		return $items;
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
		// Admins can edit any language.
		// Make sure it's only for logged in users.
		if ( ! is_user_logged_in() || current_user_can( 'manage_options' ) )
			return;

		global $sitepress;

		// If there's no global $sitepress object there's nothing to do here.
		if ( empty( $sitepress ) ) {
			return;
		}

		// Get the allowed languages for the user.
		$user_languages = $this->get_user_allowed_languages( get_current_user_id() );

		// If the user doesn't have any language preferences set then bail.
		if ( empty( $user_languages ) ) {
			return;
		}

		$reflection_class = new ReflectionClass('Sitepress');

		// `active_languages` property is set to private,
		// override that using the relflection class.
		$active_languages_property = $reflection_class->getProperty('active_languages');
		$active_languages_property->setAccessible(true);

		$active_languages = $active_languages_property->getValue( $sitepress );
		$user_languages   = array_flip( $user_languages );
		$active_languages = array_intersect_key( $active_languages, $user_languages );
		$active_languages = apply_filters( 'wpmlel_active_languages', $active_languages );

		$active_languages_property->setValue( $sitepress, $active_languages );

		// Will die if they try to switch surreptitiously
		if ( ! isset( $user_languages[ ICL_LANGUAGE_CODE ] ) )
		{
			do_action('admin_page_access_denied');

			// Get the redirect url.
			$user_redirect_url = add_query_arg( array( 'lang' => key( $user_languages ) ), admin_url() );

			wp_die( sprintf(
				wp_kses(
					__( 'You cannot modify or delete this entry. <a href="%s">Back to home</a>', 'wpml-editor-languages' ),
					array(  'a' => array( 'href' => true, 'title' => true, 'target' => true ) )
				),
				esc_url_raw( $user_redirect_url )
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
		// If no $user is set or the user is an admin, continue.
		if ( empty( $user->ID ) || current_user_can( 'manage_options' ) ) {
			return $redirect_to;
		}

		if ( $user_language = get_user_meta( $user->ID, 'icl_admin_language', true ) ) {
			// Get the redirect url.
			$user_redirect_url = add_query_arg( array( 'lang' => $user_language ), admin_url() );

			/**
			 * Filter the user redirect URL.
			 *
			 * @var string $user_redirect_url The URL to redirect to.
			 */
			$user_redirect_url = apply_filters( 'wpmlel_admin_redirect', $user_redirect_url );

			return esc_url_raw( $user_redirect_url );
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
		// If not an Admin, they can't edit it.
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

		// Get the languages allowed and sanitize them.
		$languages_allowed = filter_input( INPUT_POST, 'languages_allowed', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY );

		/**
		 * Filter the allowed languages for this user.
		 *
		 * @var array $languages_allowed The languages allowed for this user.
		 * @var int $user_id ID of th user eing edited.
		 */
		$languages_allowed = apply_filters( 'wpmlel_save_user_languages', $languages_allowed, $user_id );

		// Update the user option if they're set, delete otherwise.
		if ( $languages_allowed ) {
			update_user_option( $user_id,'languages_allowed', json_encode( $languages_allowed ) );

			// Get the user's preferred admin language.
			$user_admin_language = get_user_meta( $user_id, 'icl_admin_language', true );

			// Check the default admin language is in the users' allowed languages.
			if ( ! in_array( $user_admin_language, $languages_allowed, true ) ) {
				update_user_option( $user_id, 'icl_admin_language', key( $languages_allowed ) );
			}

		} else {
			delete_user_option( $user_id, 'languages_allowed' );
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
		// Get the user languages.
		$user_languages = get_user_option( 'languages_allowed', $user_id );
		// Try to decode it.
		$user_languages = json_decode( $user_languages );

		/**
		 * Filter the languages allowed for this user.
		 *
		 * @var array $languages_allowed The languages allowed for this user.
		 * @var int $user_id ID of th user eing edited.
		 */
		$user_languages = apply_filters( 'wpmlel_user_languages', $user_languages, $user_id );

		return ! empty( $user_languages ) && is_array( $user_languages ) ? $user_languages : array();
	}

}
