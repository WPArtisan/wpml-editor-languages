=== WPML Editor Languages ===
Contributors: ozthegreat
Donate link: https://ozthegreat.io/wpml-editor-languages
Tags: WPML, languages, multilingual, i18n, admin
Requires at least: 3.0.1
Tested up to: 4.2.2
Stable tag: 1.0.0
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

1. Upload the `wpml-editory-languages` directory to the `/wp-content/plugins/` directory
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

= 1.0.0 =
* First release
