<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript, as well
 * as handling the admin menu and AJAX requests.
 *
 * @package    LibreOffice_Importer
 * @subpackage LibreOffice_Importer/admin
 */

class LibreOffice_Importer_Admin {

    /**
     * The ID of this plugin.
     *
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles() {
        $screen = get_current_screen();
        
        if ($screen && strpos($screen->id, 'libreoffice-importer') !== false) {
            wp_enqueue_style(
                $this->plugin_name,
                LIBREOFFICE_IMPORTER_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                $this->version,
                'all'
            );
        }
    }

    /**
     * Register the JavaScript for the admin area.
     */
    public function enqueue_scripts() {
        $screen = get_current_screen();
        
        if ($screen && strpos($screen->id, 'libreoffice-importer') !== false) {
            wp_enqueue_script(
                $this->plugin_name,
                LIBREOFFICE_IMPORTER_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery'),
                $this->version,
                false
            );

            // Localize script with data
            wp_localize_script(
                $this->plugin_name,
                'libreofficeImporter',
                array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce'    => wp_create_nonce('libreoffice_importer_nonce'),
                    'strings'  => array(
                        'uploading'       => __('Uploading file...', 'libreoffice-importer'),
                        'processing'      => __('Processing document...', 'libreoffice-importer'),
                        'creating_post'   => __('Creating post...', 'libreoffice-importer'),
                        'success'         => __('Post created successfully!', 'libreoffice-importer'),
                        'error'           => __('An error occurred:', 'libreoffice-importer'),
                        'file_required'   => __('Please select a file to upload.', 'libreoffice-importer'),
                        'html_required'   => __('Please paste some content.', 'libreoffice-importer'),
                        'confirm_leave'   => __('Are you sure you want to leave? Unsaved changes will be lost.', 'libreoffice-importer'),
                    ),
                )
            );
        }
    }

    /**
     * Add the plugin admin menu.
     */
    public function add_plugin_admin_menu() {
        add_menu_page(
            __('LibreOffice Importer', 'libreoffice-importer'),
            __('LO Importer', 'libreoffice-importer'),
            'edit_posts',
            'libreoffice-importer',
            array($this, 'display_plugin_admin_page'),
            'dashicons-media-document',
            25
        );

        add_submenu_page(
            'libreoffice-importer',
            __('Import Document', 'libreoffice-importer'),
            __('Import Document', 'libreoffice-importer'),
            'edit_posts',
            'libreoffice-importer',
            array($this, 'display_plugin_admin_page')
        );

        add_submenu_page(
            'libreoffice-importer',
            __('Settings', 'libreoffice-importer'),
            __('Settings', 'libreoffice-importer'),
            'manage_options',
            'libreoffice-importer-settings',
            array($this, 'display_settings_page')
        );
    }

    /**
     * Render the main admin page.
     */
    public function display_plugin_admin_page() {
        // Check user capabilities
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'libreoffice-importer'));
        }

        include_once LIBREOFFICE_IMPORTER_PLUGIN_DIR . 'admin/partials/import-page.php';
    }

    /**
     * Render the settings page.
     */
    public function display_settings_page() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'libreoffice-importer'));
        }

        // Save settings if form submitted
        if (isset($_POST['libreoffice_importer_settings_submit'])) {
            check_admin_referer('libreoffice_importer_settings');
            $this->save_settings();
        }

        include_once LIBREOFFICE_IMPORTER_PLUGIN_DIR . 'admin/partials/settings-page.php';
    }

    /**
     * Save plugin settings.
     */
    private function save_settings() {
        $options = array(
            'version'                  => LIBREOFFICE_IMPORTER_VERSION,
            'auto_extract_author'      => isset($_POST['auto_extract_author']) ? 1 : 0,
            'auto_extract_abstract'    => isset($_POST['auto_extract_abstract']) ? 1 : 0,
            'abstract_max_paragraphs'  => intval($_POST['abstract_max_paragraphs']),
            'default_post_status'      => sanitize_text_field($_POST['default_post_status']),
            'import_images'            => isset($_POST['import_images']) ? 1 : 0,
            'import_footnotes'         => isset($_POST['import_footnotes']) ? 1 : 0,
            'preserve_formatting'      => isset($_POST['preserve_formatting']) ? 1 : 0,
        );

        update_option('libreoffice_importer_options', $options);

        add_settings_error(
            'libreoffice_importer_settings',
            'settings_updated',
            __('Settings saved successfully.', 'libreoffice-importer'),
            'success'
        );
    }

    /**
     * Handle ODT file upload via AJAX.
     */
    public function handle_odt_upload() {
        // Verify nonce
        check_ajax_referer('libreoffice_importer_nonce', 'nonce');

        // Check user capabilities
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to create posts.', 'libreoffice-importer'),
            ));
        }

        // Check if file was uploaded
        if (empty($_FILES['odt_file'])) {
            wp_send_json_error(array(
                'message' => __('No file was uploaded.', 'libreoffice-importer'),
            ));
        }

        $file = $_FILES['odt_file'];

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(array(
                'message' => __('File upload failed with error code: ', 'libreoffice-importer') . $file['error'],
            ));
        }

        // Verify file extension
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($file_ext !== 'odt') {
            wp_send_json_error(array(
                'message' => __('Invalid file type. Please upload an ODT file.', 'libreoffice-importer'),
            ));
        }

        // Move uploaded file to temp location
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/libreoffice-importer-temp';
        
        if (!file_exists($temp_dir)) {
            wp_mkdir_p($temp_dir);
        }

        $temp_file = $temp_dir . '/' . uniqid('odt_') . '.odt';
        
        if (!move_uploaded_file($file['tmp_name'], $temp_file)) {
            wp_send_json_error(array(
                'message' => __('Failed to save uploaded file.', 'libreoffice-importer'),
            ));
        }

        // Parse the ODT file
        $parser = new LibreOffice_Importer_ODT_Parser($temp_file);
        $parsed_data = $parser->parse();

        // Clean up temp file
        @unlink($temp_file);

        if (is_wp_error($parsed_data)) {
            wp_send_json_error(array(
                'message' => $parsed_data->get_error_message(),
            ));
        }

        // Create the post
        $post_creator = new LibreOffice_Importer_Post_Creator($parsed_data);
        $post_id = $post_creator->create_post();

        if (is_wp_error($post_id)) {
            wp_send_json_error(array(
                'message' => $post_id->get_error_message(),
            ));
        }

        // Get post edit URL
        $edit_url = get_edit_post_link($post_id, 'raw');
        $view_url = get_permalink($post_id);

        wp_send_json_success(array(
            'message'       => __('Post created successfully!', 'libreoffice-importer'),
            'post_id'       => $post_id,
            'edit_url'      => $edit_url,
            'view_url'      => $view_url,
            'post_title'    => get_the_title($post_id),
            'post_status'   => get_post_status($post_id),
            'author'        => $parsed_data['author'],
            'images_count'  => count($parsed_data['images']),
        ));
    }

    /**
     * Handle HTML content import via AJAX.
     */
    public function handle_html_import() {
        // Verify nonce
        check_ajax_referer('libreoffice_importer_nonce', 'nonce');

        // Check user capabilities
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to create posts.', 'libreoffice-importer'),
            ));
        }

        // Check if HTML content was provided
        if (empty($_POST['html_content'])) {
            wp_send_json_error(array(
                'message' => __('No content was provided.', 'libreoffice-importer'),
            ));
        }

        $html_content = wp_unslash($_POST['html_content']);

        // Parse the HTML content
        $parser = new LibreOffice_Importer_HTML_Parser($html_content);
        $parsed_data = $parser->parse();

        if (is_wp_error($parsed_data)) {
            wp_send_json_error(array(
                'message' => $parsed_data->get_error_message(),
            ));
        }

        // Create the post
        $post_creator = new LibreOffice_Importer_Post_Creator($parsed_data);
        $post_id = $post_creator->create_post();

        if (is_wp_error($post_id)) {
            wp_send_json_error(array(
                'message' => $post_id->get_error_message(),
            ));
        }

        // Get post edit URL
        $edit_url = get_edit_post_link($post_id, 'raw');
        $view_url = get_permalink($post_id);

        wp_send_json_success(array(
            'message'       => __('Post created successfully!', 'libreoffice-importer'),
            'post_id'       => $post_id,
            'edit_url'      => $edit_url,
            'view_url'      => $view_url,
            'post_title'    => get_the_title($post_id),
            'post_status'   => get_post_status($post_id),
            'author'        => $parsed_data['author'],
            'images_count'  => count($parsed_data['images']),
        ));
    }
}