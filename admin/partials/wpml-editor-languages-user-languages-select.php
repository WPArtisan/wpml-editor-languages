<?php

/**
 * Provides the select box view for allowing Admins to restrict Users to
 * certain languages.
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://ozthegreat.io/wpml-editor-languages
 * @since      1.0.0
 *
 * @package    Wpml_Editor_Languages
 * @subpackage Wpml_Editor_Languages/admin/partials
 */
?>

<h3><?php _e( 'Allowed Languages', WPML_EDITOR_LANGUAGES_TEXT_DOMAIN ); ?></h3>
<table class="form-table">
    <tr>
        <th><label for="languages_allowed"><?php _e( 'Languages allowed to edit', WPML_EDITOR_LANGUAGES_TEXT_DOMAIN ); ?></label></th>
        <td>
            <select name="languages_allowed[]" multiple="multiple">
            <?php foreach ( (array) $languages as $language ) : ?>
                <option value="<?php echo $language['language_code']; ?>" <?php if ( isset( $user_languages[ $language['language_code'] ] ) ) echo 'selected ' ?>>
                    <?php echo $language['translated_name']; ?>
                </option>
            <?php endforeach; ?>
            </select>
        </td>
    </tr>
</table>
