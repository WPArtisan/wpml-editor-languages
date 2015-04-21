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

		global $sitepress;

		$reflection_class = new ReflectionClass('Sitepress');

		// `active_languages` is set to private, override that
		// using the relflection class.
		$active_languages_property = $reflection_class->getProperty('active_languages');
		$active_languages_property->setAccessible(true);

		$active_languages = $active_languages_property->getValue( $sitepress );
		$userLanguages    = array_flip( get_user_allowed_languages( get_current_user_id() ) );
		$active_languages = array_intersect( $active_languages, $userLanguages );

		$active_languages_property->setValue( $sitepress, $active_languages );

		// Will die if they try to switch surreptitiously
		if ( ! isset( $userLanguages[ ICL_LANGUAGE_CODE ] ) )
		{
			// Restrict access
			do_action('admin_page_access_denied');
			$backLink = '<a href="' . admin_url() . '?lang=' . key( $userLanguages ) . '">' . __('Back to home') . '</a>';

			wp_die( __('You cannot modify or delete this entry. ' . $backLink) );
			exit;
		}

	}

	/**
	 * When a User first logs in to the admin, check the default
	 * language is in their allowed languages, otherwise show and error
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
	 		return get_admin_url( FALSE, '?lang=' . $userLanguage );
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
		$userLanguages = array_flip( get_user_allowed_languages( $user->ID ) );
		?>
			<h3><?php _e( 'Allowed Languages', 'cleanipedia_theme' ); ?></h3>
			<table class="form-table">
				<tr>
					<th><label for="languages_allowed"><?php _e('Languages allowed to edit', 'cleanipedia_theme' ); ?></label></th>
					<td>
					<select name="languages_allowed[]" multiple="multiple">
					<?php foreach( $languages as $language ) : ?>
						<option value="<?php echo $language['language_code']; ?>" <?php if ( isset( $userLanguages[ $language['language_code'] ] )) echo 'selected ' ?>><?php echo $language['translated_name']; ?></option>
					<?php endforeach; ?>
					</select>
				</tr>
			</table>
		<?php
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

		$languages_allowed = $_POST['languages_allowed'];

		update_user_meta( $id,'languages_allowed', sanitize_text_field( json_encode( $languages_allowed ) ) );

		$languages_allowed = array_flip( $languages_allowed );

		if ( ! isset( $languages_allowed[ get_user_meta( $id, 'icl_admin_language', true ) ] ) )
		{
			update_user_meta( $id,'icl_admin_language', key( $languages_allowed ) );
		}
	}

}
