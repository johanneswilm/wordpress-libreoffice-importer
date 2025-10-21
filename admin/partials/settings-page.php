<?php
/**
 * Provide a admin area view for plugin settings.
 *
 * This file is used to markup the settings page of the plugin.
 *
 * @package    LibreOffice_Importer
 * @subpackage LibreOffice_Importer/admin/partials
 */

// Prevent direct access
if (!defined('WPINC')) {
    die;
}

$options = get_option('libreoffice_importer_options', array());
?>

<div class="wrap libreoffice-importer-settings-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <p class="description">
        <?php _e('Configure how LibreOffice documents are imported into WordPress.', 'libreoffice-importer'); ?>
    </p>

    <?php settings_errors('libreoffice_importer_settings'); ?>

    <form method="post" action="">
        <?php wp_nonce_field('libreoffice_importer_settings'); ?>

        <h2 class="title"><?php _e('Content Extraction', 'libreoffice-importer'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <?php _e('Extract Author Information', 'libreoffice-importer'); ?>
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="auto_extract_author" value="1" <?php checked(isset($options['auto_extract_author']) ? $options['auto_extract_author'] : true, 1); ?> />
                        <?php _e('Automatically detect and match author names with WordPress users', 'libreoffice-importer'); ?>
                    </label>
                    <p class="description">
                        <?php _e('When enabled, the plugin will look for author information in the document metadata or content (e.g., "Author: John Doe") and match it with existing WordPress users.', 'libreoffice-importer'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <?php _e('Extract Abstract/Summary', 'libreoffice-importer'); ?>
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="auto_extract_abstract" value="1" <?php checked(isset($options['auto_extract_abstract']) ? $options['auto_extract_abstract'] : true, 1); ?> />
                        <?php _e('Automatically extract abstract from first few paragraphs', 'libreoffice-importer'); ?>
                    </label>
                    <p class="description">
                        <?php _e('When enabled, the plugin will use the first few paragraphs as the post excerpt.', 'libreoffice-importer'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="abstract_max_paragraphs"><?php _e('Abstract Maximum Paragraphs', 'libreoffice-importer'); ?></label>
                </th>
                <td>
                    <input type="number" name="abstract_max_paragraphs" id="abstract_max_paragraphs" value="<?php echo esc_attr(isset($options['abstract_max_paragraphs']) ? $options['abstract_max_paragraphs'] : 3); ?>" min="1" max="10" class="small-text" />
                    <p class="description">
                        <?php _e('Maximum number of paragraphs to include in the abstract. Default: 3', 'libreoffice-importer'); ?>
                    </p>
                </td>
            </tr>
        </table>

        <h2 class="title"><?php _e('Import Options', 'libreoffice-importer'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <?php _e('Import Images', 'libreoffice-importer'); ?>
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="import_images" value="1" <?php checked(isset($options['import_images']) ? $options['import_images'] : true, 1); ?> />
                        <?php _e('Upload images to WordPress media library', 'libreoffice-importer'); ?>
                    </label>
                    <p class="description">
                        <?php _e('When enabled, images embedded in the document will be extracted and uploaded to your media library.', 'libreoffice-importer'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <?php _e('Import Footnotes', 'libreoffice-importer'); ?>
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="import_footnotes" value="1" <?php checked(isset($options['import_footnotes']) ? $options['import_footnotes'] : true, 1); ?> />
                        <?php _e('Convert footnotes to WordPress-style footnotes', 'libreoffice-importer'); ?>
                    </label>
                    <p class="description">
                        <?php _e('When enabled, footnotes from the document will be converted and added to the end of the post.', 'libreoffice-importer'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <?php _e('Preserve Formatting', 'libreoffice-importer'); ?>
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="preserve_formatting" value="1" <?php checked(isset($options['preserve_formatting']) ? $options['preserve_formatting'] : true, 1); ?> />
                        <?php _e('Preserve text formatting (bold, italic, underline, etc.)', 'libreoffice-importer'); ?>
                    </label>
                    <p class="description">
                        <?php _e('When enabled, all text formatting from the document will be preserved in the WordPress post.', 'libreoffice-importer'); ?>
                    </p>
                </td>
            </tr>
        </table>

        <h2 class="title"><?php _e('Post Settings', 'libreoffice-importer'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="default_post_status"><?php _e('Default Post Status', 'libreoffice-importer'); ?></label>
                </th>
                <td>
                    <select name="default_post_status" id="default_post_status">
                        <option value="draft" <?php selected(isset($options['default_post_status']) ? $options['default_post_status'] : 'draft', 'draft'); ?>>
                            <?php _e('Draft', 'libreoffice-importer'); ?>
                        </option>
                        <option value="pending" <?php selected(isset($options['default_post_status']) ? $options['default_post_status'] : 'draft', 'pending'); ?>>
                            <?php _e('Pending Review', 'libreoffice-importer'); ?>
                        </option>
                        <option value="publish" <?php selected(isset($options['default_post_status']) ? $options['default_post_status'] : 'draft', 'publish'); ?>>
                            <?php _e('Published', 'libreoffice-importer'); ?>
                        </option>
                    </select>
                    <p class="description">
                        <?php _e('The status that will be assigned to imported posts. Recommended: Draft', 'libreoffice-importer'); ?>
                    </p>
                </td>
            </tr>
        </table>

        <h2 class="title"><?php _e('System Information', 'libreoffice-importer'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <?php _e('Plugin Version', 'libreoffice-importer'); ?>
                </th>
                <td>
                    <code><?php echo esc_html(LIBREOFFICE_IMPORTER_VERSION); ?></code>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <?php _e('PHP ZIP Extension', 'libreoffice-importer'); ?>
                </th>
                <td>
                    <?php if (extension_loaded('zip')): ?>
                        <span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
                        <?php _e('Enabled', 'libreoffice-importer'); ?>
                    <?php else: ?>
                        <span class="dashicons dashicons-dismiss" style="color: #dc3232;"></span>
                        <?php _e('Not available (required for ODT import)', 'libreoffice-importer'); ?>
                    <?php endif; ?>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <?php _e('PHP XML Extension', 'libreoffice-importer'); ?>
                </th>
                <td>
                    <?php if (extension_loaded('xml')): ?>
                        <span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
                        <?php _e('Enabled', 'libreoffice-importer'); ?>
                    <?php else: ?>
                        <span class="dashicons dashicons-dismiss" style="color: #dc3232;"></span>
                        <?php _e('Not available (required for ODT import)', 'libreoffice-importer'); ?>
                    <?php endif; ?>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <?php _e('Max Upload Size', 'libreoffice-importer'); ?>
                </th>
                <td>
                    <code><?php echo esc_html(size_format(wp_max_upload_size())); ?></code>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <?php _e('PHP Version', 'libreoffice-importer'); ?>
                </th>
                <td>
                    <code><?php echo esc_html(phpversion()); ?></code>
                    <?php if (version_compare(phpversion(), '7.4', '<')): ?>
                        <span style="color: #dc3232;">
                            <?php _e('(Warning: PHP 7.4 or higher is recommended)', 'libreoffice-importer'); ?>
                        </span>
                    <?php endif; ?>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <?php _e('WordPress Version', 'libreoffice-importer'); ?>
                </th>
                <td>
                    <code><?php echo esc_html(get_bloginfo('version')); ?></code>
                </td>
            </tr>
        </table>

        <?php submit_button(__('Save Settings', 'libreoffice-importer'), 'primary', 'libreoffice_importer_settings_submit'); ?>
    </form>

    <hr />

    <div class="libreoffice-importer-info">
        <h2><?php _e('About LibreOffice Importer', 'libreoffice-importer'); ?></h2>
        <p>
            <?php _e('This plugin allows you to easily import content from LibreOffice Writer documents into WordPress posts. It supports ODT files and copy/paste operations, preserving formatting, images, and footnotes.', 'libreoffice-importer'); ?>
        </p>
        <p>
            <strong><?php _e('Features:', 'libreoffice-importer'); ?></strong>
        </p>
        <ul>
            <li><?php _e('Automatic title extraction from first line', 'libreoffice-importer'); ?></li>
            <li><?php _e('Author name detection and matching', 'libreoffice-importer'); ?></li>
            <li><?php _e('Abstract/excerpt extraction', 'libreoffice-importer'); ?></li>
            <li><?php _e('Image import with automatic upload', 'libreoffice-importer'); ?></li>
            <li><?php _e('Footnote conversion', 'libreoffice-importer'); ?></li>
            <li><?php _e('Formatting preservation (headings, bold, italic, lists, tables)', 'libreoffice-importer'); ?></li>
        </ul>
    </div>
</div>