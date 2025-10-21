<?php
/**
 * Provide a admin area view for importing documents.
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @package    LibreOffice_Importer
 * @subpackage LibreOffice_Importer/admin/partials
 */

// Prevent direct access
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap libreoffice-importer-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <p class="description">
        <?php _e('Import posts from LibreOffice documents. You can either upload an ODT file or paste formatted content directly.', 'libreoffice-importer'); ?>
    </p>

    <div class="libreoffice-importer-notice" id="import-notice" style="display:none;">
        <p></p>
    </div>

    <div class="libreoffice-importer-tabs">
        <nav class="nav-tab-wrapper">
            <a href="#tab-upload" class="nav-tab nav-tab-active" data-tab="upload">
                <?php _e('Upload ODT File', 'libreoffice-importer'); ?>
            </a>
            <a href="#tab-paste" class="nav-tab" data-tab="paste">
                <?php _e('Copy & Paste', 'libreoffice-importer'); ?>
            </a>
        </nav>

        <!-- Upload Tab -->
        <div id="tab-upload" class="tab-content active">
            <h2><?php _e('Upload ODT File', 'libreoffice-importer'); ?></h2>
            <p><?php _e('Select an ODT (OpenDocument Text) file from your computer to import.', 'libreoffice-importer'); ?></p>
            
            <form id="odt-upload-form" method="post" enctype="multipart/form-data">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="odt_file"><?php _e('Select File', 'libreoffice-importer'); ?></label>
                        </th>
                        <td>
                            <input type="file" name="odt_file" id="odt_file" accept=".odt" required />
                            <p class="description">
                                <?php _e('Choose an ODT file to import. Maximum file size: ', 'libreoffice-importer'); ?>
                                <?php echo size_format(wp_max_upload_size()); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <div class="import-preview" id="odt-preview" style="display:none;">
                    <h3><?php _e('File Selected', 'libreoffice-importer'); ?></h3>
                    <p id="odt-file-name"></p>
                    <p id="odt-file-size"></p>
                </div>

                <p class="submit">
                    <button type="submit" class="button button-primary button-large" id="upload-submit">
                        <span class="dashicons dashicons-upload"></span>
                        <?php _e('Import from ODT', 'libreoffice-importer'); ?>
                    </button>
                    <span class="spinner"></span>
                </p>

                <div class="import-progress" id="upload-progress" style="display:none;">
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                    <p class="progress-message"></p>
                </div>
            </form>
        </div>

        <!-- Paste Tab -->
        <div id="tab-paste" class="tab-content">
            <h2><?php _e('Copy & Paste Content', 'libreoffice-importer'); ?></h2>
            <p><?php _e('Copy formatted content from LibreOffice Writer and paste it below. Formatting, images, and footnotes will be preserved.', 'libreoffice-importer'); ?></p>
            
            <form id="html-import-form" method="post">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="html_content"><?php _e('Paste Content', 'libreoffice-importer'); ?></label>
                        </th>
                        <td>
                            <div id="html-content-editor" class="html-content-editor" contenteditable="true" placeholder="<?php esc_attr_e('Paste your LibreOffice content here...', 'libreoffice-importer'); ?>"></div>
                            <textarea name="html_content" id="html_content" style="display:none;"></textarea>
                            <p class="description">
                                <?php _e('Press Ctrl+V (or Cmd+V on Mac) to paste formatted content from LibreOffice Writer.', 'libreoffice-importer'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <div class="import-preview" id="paste-preview" style="display:none;">
                    <h3><?php _e('Content Preview', 'libreoffice-importer'); ?></h3>
                    <div id="paste-preview-content"></div>
                </div>

                <p class="submit">
                    <button type="submit" class="button button-primary button-large" id="paste-submit">
                        <span class="dashicons dashicons-admin-post"></span>
                        <?php _e('Create Post from Content', 'libreoffice-importer'); ?>
                    </button>
                    <button type="button" class="button button-secondary" id="clear-paste">
                        <?php _e('Clear Content', 'libreoffice-importer'); ?>
                    </button>
                    <span class="spinner"></span>
                </p>

                <div class="import-progress" id="paste-progress" style="display:none;">
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                    <p class="progress-message"></p>
                </div>
            </form>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="success-modal" class="libreoffice-modal" style="display:none;">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <span class="modal-close">&times;</span>
            <div class="modal-icon">
                <span class="dashicons dashicons-yes-alt"></span>
            </div>
            <h2><?php _e('Post Created Successfully!', 'libreoffice-importer'); ?></h2>
            <div class="modal-body">
                <p><strong><?php _e('Title:', 'libreoffice-importer'); ?></strong> <span id="modal-title"></span></p>
                <p><strong><?php _e('Status:', 'libreoffice-importer'); ?></strong> <span id="modal-status"></span></p>
                <p><strong><?php _e('Author:', 'libreoffice-importer'); ?></strong> <span id="modal-author"></span></p>
                <p><strong><?php _e('Images:', 'libreoffice-importer'); ?></strong> <span id="modal-images"></span></p>
            </div>
            <div class="modal-actions">
                <a href="#" id="modal-edit-link" class="button button-primary button-large">
                    <?php _e('Edit Post', 'libreoffice-importer'); ?>
                </a>
                <a href="#" id="modal-view-link" class="button button-secondary">
                    <?php _e('View Post', 'libreoffice-importer'); ?>
                </a>
                <button type="button" class="button" id="modal-new-import">
                    <?php _e('Import Another', 'libreoffice-importer'); ?>
                </button>
            </div>
        </div>
    </div>

    <div class="libreoffice-importer-help">
        <h2><?php _e('How to Use', 'libreoffice-importer'); ?></h2>
        <div class="help-section">
            <h3><?php _e('Document Format', 'libreoffice-importer'); ?></h3>
            <ul>
                <li><?php _e('The first line of your document will be used as the post title.', 'libreoffice-importer'); ?></li>
                <li><?php _e('Include author information by adding "Author: Name" or "By Name" on a separate line.', 'libreoffice-importer'); ?></li>
                <li><?php _e('The first few paragraphs after the title may be used as the post excerpt/abstract.', 'libreoffice-importer'); ?></li>
                <li><?php _e('All formatting (bold, italic, headings, etc.) will be preserved.', 'libreoffice-importer'); ?></li>
                <li><?php _e('Images embedded in the document will be uploaded to your media library.', 'libreoffice-importer'); ?></li>
                <li><?php _e('Footnotes will be converted to WordPress-style footnotes.', 'libreoffice-importer'); ?></li>
            </ul>
        </div>

        <div class="help-section">
            <h3><?php _e('Tips', 'libreoffice-importer'); ?></h3>
            <ul>
                <li><?php _e('For best results, use standard LibreOffice Writer formatting.', 'libreoffice-importer'); ?></li>
                <li><?php _e('Author names will be matched with existing WordPress users when possible.', 'libreoffice-importer'); ?></li>
                <li><?php _e('Posts are created as drafts by default. You can change this in the settings.', 'libreoffice-importer'); ?></li>
                <li><?php _e('Large documents may take a few seconds to process.', 'libreoffice-importer'); ?></li>
            </ul>
        </div>

        <div class="help-section">
            <h3><?php _e('Troubleshooting', 'libreoffice-importer'); ?></h3>
            <ul>
                <li><?php _e('If images are not importing, check your upload file size limits.', 'libreoffice-importer'); ?></li>
                <li><?php _e('Complex formatting may not always be perfectly preserved.', 'libreoffice-importer'); ?></li>
                <li><?php _e('Make sure your server has the ZIP and XML PHP extensions enabled.', 'libreoffice-importer'); ?></li>
            </ul>
        </div>
    </div>
</div>