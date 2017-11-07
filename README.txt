=== WPML Editor Languages ===
Contributors: ozthegreat
Donate link: https://wpartisan.me/plugins/wpml-editor-languages
Tags: WPML, languages, multilingual, i18n, admin
Requires at least: 4.0
Tested up to: 4.8.3
Stable tag: 1.0.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows admins to restrict users to specific languages in the WordPress admin

== Description ==

Adds a multiple select box to every non-admin user profile that allows admins to
select which languages that user can see / edit in the wp-admin.

Languages that a user cannot see are hidden. If they try to switch surreptitiously
it will throw an error message.

This plugin **REQUIRES** WPML and is useless without it.

== Installation ==

1. Upload the `wpml-editor-languages` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress, (ensure you have WPML installed and activated)
3. As an admin, go to any non-admin user and you can now select the languages
they can use.


== Screenshots ==

1. Multiple select element on a non-admin user's profile, allowing an
admin to select the languages that user can use.
2. A user's language menu only showing English as the admin has disabled
the other languages.
3. The message shown to a user when they try to switch to a language they're
not allowed to.

== Changelog ==

= 1.0.7 =
* WordPress 4.8.3 version check

= 1.0.6 =
* Fix languages showing in the language switcher when they shouldn't

= 1.0.5 =
* WordPress 4.7.1 version check

= 1.0.4 =
* WordPress 4.6 version check

= 1.0.3 =
* WordPress 4.5 version check
* Fix bug for AJAX requests & logged out users

= 1.0.2 =
* WordPress 4.4.2 version check

= 1.0.1 =
* WPMU compatible
* Added Language box to the user create page with the default language pre-populated
* WordPress 4.2.3 version check
* Fix language domain
* Covert spaces to tabs

= 1.0.0 =
* First release
