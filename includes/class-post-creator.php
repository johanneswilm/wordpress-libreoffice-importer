<?php
/**
 * Create WordPress posts from parsed content.
 *
 * This class handles creating WordPress posts from parsed ODT or HTML content,
 * including:
 * - Creating the post with title and content
 * - Matching or creating authors
 * - Setting post excerpts/abstracts
 * - Uploading and attaching images
 * - Processing footnotes
 *
 * @package    LibreOffice_Importer
 * @subpackage LibreOffice_Importer/includes
 */

class LibreOffice_Importer_Post_Creator {

    /**
     * The parsed content data.
     *
     * @var array
     */
    private $parsed_data;

    /**
     * The plugin options.
     *
     * @var array
     */
    private $options;

    /**
     * Uploaded images mapping (placeholder => attachment_id).
     *
     * @var array
     */
    private $image_mapping = array();

    /**
     * Constructor.
     *
     * @param array $parsed_data The parsed content data.
     */
    public function __construct($parsed_data) {
        $this->parsed_data = $parsed_data;
        $this->options = get_option('libreoffice_importer_options', array());
    }

    /**
     * Create a WordPress post from the parsed data.
     *
     * @return int|WP_Error The post ID on success, or WP_Error on failure.
     */
    public function create_post() {
        // Validate required data
        if (empty($this->parsed_data['title'])) {
            return new WP_Error('no_title', __('No title found in the document.', 'libreoffice-importer'));
        }

        // Get or create author
        $author_id = $this->get_author_id($this->parsed_data['author']);

        // Process images first so we can replace placeholders
        $content = $this->process_images($this->parsed_data['content']);

        // Add footnotes to content if they exist
        if (!empty($this->parsed_data['footnotes']) && $this->get_option('import_footnotes', true)) {
            $content .= "\n\n" . $this->format_footnotes($this->parsed_data['footnotes']);
        }

        // Prepare post data
        $post_data = array(
            'post_title'   => wp_strip_all_tags($this->parsed_data['title']),
            'post_content' => $content,
            'post_status'  => $this->get_option('default_post_status', 'draft'),
            'post_author'  => $author_id,
            'post_type'    => 'post',
        );

        // Add excerpt if available
        if (!empty($this->parsed_data['abstract'])) {
            $post_data['post_excerpt'] = wp_strip_all_tags($this->parsed_data['abstract']);
        }

        // Insert the post
        $post_id = wp_insert_post($post_data, true);

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        // Attach images to the post
        if (!empty($this->image_mapping)) {
            foreach ($this->image_mapping as $attachment_id) {
                wp_update_post(array(
                    'ID'          => $attachment_id,
                    'post_parent' => $post_id,
                ));
            }
        }

        // Add custom meta data
        update_post_meta($post_id, '_libreoffice_import_date', current_time('mysql'));
        update_post_meta($post_id, '_libreoffice_import_version', LIBREOFFICE_IMPORTER_VERSION);

        if (!empty($this->parsed_data['author'])) {
            update_post_meta($post_id, '_libreoffice_original_author', $this->parsed_data['author']);
        }

        return $post_id;
    }

    /**
     * Get or create an author by name.
     *
     * @param string $author_name The author name.
     * @return int The user ID.
     */
    private function get_author_id($author_name) {
        // If no author name provided, use current user
        if (empty($author_name)) {
            return get_current_user_id();
        }

        // Check if auto-matching is enabled
        if (!$this->get_option('auto_extract_author', true)) {
            return get_current_user_id();
        }

        // Try to find an existing user by display name
        $user = get_user_by('login', sanitize_user($author_name, true));
        
        if ($user) {
            return $user->ID;
        }

        // Try to find by display name
        $users = get_users(array(
            'search' => $author_name,
            'search_columns' => array('display_name', 'user_nicename'),
            'number' => 1,
        ));

        if (!empty($users)) {
            return $users[0]->ID;
        }

        // Try to find by first and last name
        $name_parts = explode(' ', $author_name);
        if (count($name_parts) >= 2) {
            $first_name = $name_parts[0];
            $last_name = end($name_parts);

            $users = get_users(array(
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'first_name',
                        'value'   => $first_name,
                        'compare' => 'LIKE',
                    ),
                    array(
                        'key'     => 'last_name',
                        'value'   => $last_name,
                        'compare' => 'LIKE',
                    ),
                ),
                'number' => 1,
            ));

            if (!empty($users)) {
                return $users[0]->ID;
            }
        }

        // No match found, use current user
        return get_current_user_id();
    }

    /**
     * Process images in the content.
     *
     * Uploads images to WordPress media library and replaces placeholders
     * with actual image URLs.
     *
     * @param string $content The content with image placeholders.
     * @return string The content with actual image URLs.
     */
    private function process_images($content) {
        if (empty($this->parsed_data['images']) || !$this->get_option('import_images', true)) {
            // Remove image placeholders if images are not being imported
            $content = preg_replace('/<img[^>]*src="{{IMAGE_\d+}}"[^>]*>/', '', $content);
            return $content;
        }

        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        foreach ($this->parsed_data['images'] as $image_id => $image_data) {
            // Create a temporary file
            $upload_dir = wp_upload_dir();
            $temp_dir = $upload_dir['basedir'] . '/libreoffice-importer-temp';
            
            if (!file_exists($temp_dir)) {
                wp_mkdir_p($temp_dir);
            }

            $filename = 'image_' . $image_id . '.' . $image_data['extension'];
            $temp_file = $temp_dir . '/' . $filename;

            // Write image data to temp file
            file_put_contents($temp_file, $image_data['data']);

            // Prepare file array for wp_handle_sideload
            $file = array(
                'name'     => sanitize_file_name($image_data['original_name']),
                'type'     => $this->get_mime_type($image_data['extension']),
                'tmp_name' => $temp_file,
                'error'    => 0,
                'size'     => filesize($temp_file),
            );

            // Upload the file
            $uploaded = wp_handle_sideload($file, array('test_form' => false));

            if (isset($uploaded['error'])) {
                // Clean up temp file
                @unlink($temp_file);
                continue;
            }

            // Create attachment
            $attachment_data = array(
                'post_mime_type' => $uploaded['type'],
                'post_title'     => sanitize_file_name(pathinfo($image_data['original_name'], PATHINFO_FILENAME)),
                'post_content'   => '',
                'post_status'    => 'inherit',
            );

            $attachment_id = wp_insert_attachment($attachment_data, $uploaded['file']);

            if (!is_wp_error($attachment_id)) {
                // Generate attachment metadata
                $attachment_metadata = wp_generate_attachment_metadata($attachment_id, $uploaded['file']);
                wp_update_attachment_metadata($attachment_id, $attachment_metadata);

                // Store mapping
                $this->image_mapping[$image_id] = $attachment_id;

                // Get the image URL
                $image_url = wp_get_attachment_url($attachment_id);

                // Replace placeholder in content
                $placeholder = '{{IMAGE_' . $image_id . '}}';
                $content = str_replace($placeholder, $image_url, $content);
            }

            // Clean up temp file
            @unlink($temp_file);
        }

        return $content;
    }

    /**
     * Get MIME type from file extension.
     *
     * @param string $extension The file extension.
     * @return string The MIME type.
     */
    private function get_mime_type($extension) {
        $mime_types = array(
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'bmp'  => 'image/bmp',
            'webp' => 'image/webp',
            'svg'  => 'image/svg+xml',
        );

        $extension = strtolower($extension);
        
        return isset($mime_types[$extension]) ? $mime_types[$extension] : 'application/octet-stream';
    }

    /**
     * Format footnotes as HTML.
     *
     * @param array $footnotes The footnotes array.
     * @return string The formatted footnotes HTML.
     */
    private function format_footnotes($footnotes) {
        if (empty($footnotes)) {
            return '';
        }

        $html = '<div class="footnotes">' . "\n";
        $html .= '<hr />' . "\n";
        $html .= '<ol>' . "\n";

        foreach ($footnotes as $id => $content) {
            $html .= '<li id="fn-' . $id . '">';
            $html .= esc_html($content);
            $html .= ' <a href="#fnref-' . $id . '" class="footnote-backref">↩</a>';
            $html .= '</li>' . "\n";
        }

        $html .= '</ol>' . "\n";
        $html .= '</div>' . "\n";

        return $html;
    }

    /**
     * Get a plugin option value.
     *
     * @param string $key     The option key.
     * @param mixed  $default The default value.
     * @return mixed The option value.
     */
    private function get_option($key, $default = null) {
        return isset($this->options[$key]) ? $this->options[$key] : $default;
    }

    /**
     * Update post content after creation.
     *
     * Useful for making additional modifications to the post.
     *
     * @param int    $post_id The post ID.
     * @param string $content The new content.
     * @return bool True on success, false on failure.
     */
    public function update_post_content($post_id, $content) {
        $result = wp_update_post(array(
            'ID'           => $post_id,
            'post_content' => $content,
        ));

        return !is_wp_error($result);
    }

    /**
     * Get the image mapping.
     *
     * @return array The image mapping array.
     */
    public function get_image_mapping() {
        return $this->image_mapping;
    }
}
