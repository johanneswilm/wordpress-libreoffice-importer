<?php
/**
 * Parse ODT (OpenDocument Text) files.
 *
 * This class handles extracting content from ODT files, including:
 * - Title (first line)
 * - Author information
 * - Abstract/summary
 * - Main content with formatting
 * - Images
 * - Footnotes
 *
 * @package    LibreOffice_Importer
 * @subpackage LibreOffice_Importer/includes
 */

class LibreOffice_Importer_ODT_Parser {

    /**
     * The path to the ODT file.
     *
     * @var string
     */
    private $file_path;

    /**
     * The ZipArchive object for reading the ODT file.
     *
     * @var ZipArchive
     */
    private $zip;

    /**
     * The parsed XML content from content.xml.
     *
     * @var SimpleXMLElement
     */
    private $content_xml;

    /**
     * The parsed XML content from meta.xml.
     *
     * @var SimpleXMLElement
     */
    private $meta_xml;

    /**
     * Extracted images from the ODT file.
     *
     * @var array
     */
    private $images = array();

    /**
     * Extracted footnotes.
     *
     * @var array
     */
    private $footnotes = array();

    /**
     * Constructor.
     *
     * @param string $file_path Path to the ODT file.
     */
    public function __construct($file_path) {
        $this->file_path = $file_path;
        $this->zip = new ZipArchive();
    }

    /**
     * Parse the ODT file and extract all content.
     *
     * @return array|WP_Error Parsed content or error.
     */
    public function parse() {
        // Open the ODT file (which is a ZIP archive)
        if ($this->zip->open($this->file_path) !== true) {
            return new WP_Error('odt_open_failed', __('Failed to open ODT file.', 'libreoffice-importer'));
        }

        // Extract and parse content.xml
        $content_xml_string = $this->zip->getFromName('content.xml');
        if ($content_xml_string === false) {
            $this->zip->close();
            return new WP_Error('odt_no_content', __('Could not find content.xml in ODT file.', 'libreoffice-importer'));
        }

        // Parse XML
        libxml_use_internal_errors(true);
        $this->content_xml = simplexml_load_string($content_xml_string);
        if ($this->content_xml === false) {
            $this->zip->close();
            return new WP_Error('odt_parse_failed', __('Failed to parse ODT content.', 'libreoffice-importer'));
        }

        // Register namespaces
        $this->register_namespaces($this->content_xml);

        // Extract meta.xml for author information
        $meta_xml_string = $this->zip->getFromName('meta.xml');
        if ($meta_xml_string !== false) {
            $this->meta_xml = simplexml_load_string($meta_xml_string);
            if ($this->meta_xml !== false) {
                $this->register_namespaces($this->meta_xml);
            }
        }

        // Extract content
        $result = array(
            'title' => $this->extract_title(),
            'author' => $this->extract_author(),
            'abstract' => $this->extract_abstract(),
            'content' => $this->extract_content(),
            'images' => $this->images,
            'footnotes' => $this->footnotes,
        );

        $this->zip->close();

        return $result;
    }

    /**
     * Register XML namespaces.
     *
     * @param SimpleXMLElement $xml The XML element.
     */
    private function register_namespaces($xml) {
        $xml->registerXPathNamespace('office', 'urn:oasis:names:tc:opendocument:xmlns:office:1.0');
        $xml->registerXPathNamespace('text', 'urn:oasis:names:tc:opendocument:xmlns:text:1.0');
        $xml->registerXPathNamespace('style', 'urn:oasis:names:tc:opendocument:xmlns:style:1.0');
        $xml->registerXPathNamespace('fo', 'urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0');
        $xml->registerXPathNamespace('draw', 'urn:oasis:names:tc:opendocument:xmlns:drawing:1.0');
        $xml->registerXPathNamespace('xlink', 'http://www.w3.org/1999/xlink');
        $xml->registerXPathNamespace('meta', 'urn:oasis:names:tc:opendocument:xmlns:meta:1.0');
        $xml->registerXPathNamespace('dc', 'http://purl.org/dc/elements/1.1/');
    }

    /**
     * Extract the title (first line of the document).
     *
     * @return string The title.
     */
    private function extract_title() {
        // Look for the first paragraph or heading
        $first_elements = $this->content_xml->xpath('//office:body//text:h[1] | //office:body//text:p[1]');
        
        if (!empty($first_elements)) {
            $title = $this->get_text_content($first_elements[0]);
            return trim($title);
        }

        return '';
    }

    /**
     * Extract author information from metadata or document content.
     *
     * @return string The author name.
     */
    private function extract_author() {
        $author = '';

        // First, try to get author from meta.xml
        if ($this->meta_xml !== null) {
            $author_nodes = $this->meta_xml->xpath('//dc:creator');
            if (!empty($author_nodes)) {
                $author = trim((string)$author_nodes[0]);
            }

            // Also check initial-creator
            if (empty($author)) {
                $initial_creator = $this->meta_xml->xpath('//meta:initial-creator');
                if (!empty($initial_creator)) {
                    $author = trim((string)$initial_creator[0]);
                }
            }
        }

        // If not found in metadata, look for "Author:" or "By:" in the first few paragraphs
        if (empty($author)) {
            $early_paragraphs = $this->content_xml->xpath('//office:body//text:p[position() <= 5]');
            
            foreach ($early_paragraphs as $paragraph) {
                $text = trim($this->get_text_content($paragraph));
                
                // Look for patterns like "Author: John Doe" or "By John Doe"
                if (preg_match('/^(?:Author|By|Written by):\s*(.+)$/i', $text, $matches)) {
                    $author = trim($matches[1]);
                    break;
                }
                
                // Look for patterns like "By John Doe" at the start of a paragraph
                if (preg_match('/^By\s+([A-Z][a-z]+(?:\s+[A-Z][a-z]+)+)$/i', $text, $matches)) {
                    $author = trim($matches[1]);
                    break;
                }
            }
        }

        return $author;
    }

    /**
     * Extract abstract/summary from the first few paragraphs after title and author.
     *
     * @return string The abstract.
     */
    private function extract_abstract() {
        $options = get_option('libreoffice_importer_options', array());
        $auto_extract = isset($options['auto_extract_abstract']) ? $options['auto_extract_abstract'] : true;
        $max_paragraphs = isset($options['abstract_max_paragraphs']) ? $options['abstract_max_paragraphs'] : 3;

        if (!$auto_extract) {
            return '';
        }

        // Get the first few paragraphs (skip title and potential author line)
        $paragraphs = $this->content_xml->xpath('//office:body//text:p');
        
        $abstract_parts = array();
        $count = 0;
        $skip = 0;

        foreach ($paragraphs as $paragraph) {
            $text = trim($this->get_text_content($paragraph));
            
            // Skip empty paragraphs
            if (empty($text)) {
                continue;
            }

            // Skip title (first non-empty paragraph)
            if ($skip === 0) {
                $skip++;
                continue;
            }

            // Skip author line if it looks like one
            if ($skip === 1 && preg_match('/^(?:Author|By|Written by):/i', $text)) {
                $skip++;
                continue;
            }

            // Skip if paragraph is very short (likely not abstract)
            if (strlen($text) < 20) {
                continue;
            }

            $abstract_parts[] = $text;
            $count++;

            if ($count >= $max_paragraphs) {
                break;
            }
        }

        // Only return as abstract if we found at least one substantial paragraph
        if (!empty($abstract_parts)) {
            // Check if these paragraphs are clearly marked as abstract/summary
            $first_para = $abstract_parts[0];
            if (preg_match('/^(?:Abstract|Summary|Overview):/i', $first_para)) {
                // Remove the "Abstract:" prefix
                $abstract_parts[0] = preg_replace('/^(?:Abstract|Summary|Overview):\s*/i', '', $first_para);
            }
            
            return implode("\n\n", $abstract_parts);
        }

        return '';
    }

    /**
     * Extract the main content from the document.
     *
     * @return string The HTML-formatted content.
     */
    private function extract_content() {
        $body = $this->content_xml->xpath('//office:body/office:text');
        
        if (empty($body)) {
            return '';
        }

        $html = '';
        $skip_elements = 1; // Skip title
        $element_count = 0;

        // Get all child elements
        foreach ($body[0]->children('urn:oasis:names:tc:opendocument:xmlns:text:1.0') as $element) {
            // Skip title and author/abstract if configured
            if ($element_count === 0) {
                $element_count++;
                continue; // Skip first element (title)
            }

            $html .= $this->process_element($element);
            $element_count++;
        }

        return $html;
    }

    /**
     * Process an XML element and convert it to HTML.
     *
     * @param SimpleXMLElement $element The XML element.
     * @return string The HTML representation.
     */
    private function process_element($element) {
        $name = $element->getName();
        $html = '';

        switch ($name) {
            case 'h':
                // Heading
                $level = (string)$element->attributes('urn:oasis:names:tc:opendocument:xmlns:text:1.0')->{'outline-level'};
                if (empty($level) || $level < 1 || $level > 6) {
                    $level = 1;
                }
                $content = $this->process_inline_content($element);
                $html = "<h{$level}>{$content}</h{$level}>\n";
                break;

            case 'p':
                // Paragraph
                $style_name = (string)$element->attributes('urn:oasis:names:tc:opendocument:xmlns:text:1.0')->{'style-name'};
                $content = $this->process_inline_content($element);
                
                // Skip empty paragraphs
                if (trim(strip_tags($content)) === '') {
                    break;
                }
                
                $html = "<p>{$content}</p>\n";
                break;

            case 'list':
                // Ordered or unordered list
                $html = $this->process_list($element);
                break;

            case 'table':
                // Table
                $html = $this->process_table($element);
                break;

            default:
                // For unknown elements, try to process children
                foreach ($element->children('urn:oasis:names:tc:opendocument:xmlns:text:1.0') as $child) {
                    $html .= $this->process_element($child);
                }
                break;
        }

        return $html;
    }

    /**
     * Process inline content (spans, formatting, etc.).
     *
     * @param SimpleXMLElement $element The XML element.
     * @return string The HTML representation.
     */
    private function process_inline_content($element) {
        $html = '';

        foreach ($element->children('urn:oasis:names:tc:opendocument:xmlns:text:1.0') as $child) {
            $name = $child->getName();

            switch ($name) {
                case 'span':
                    // Formatted text (bold, italic, etc.)
                    $style_name = (string)$child->attributes('urn:oasis:names:tc:opendocument:xmlns:text:1.0')->{'style-name'};
                    $content = $this->process_inline_content($child);
                    
                    // Detect formatting based on style name
                    $formatted = $this->apply_formatting($content, $style_name);
                    $html .= $formatted;
                    break;

                case 'a':
                    // Hyperlink
                    $href = (string)$child->attributes('http://www.w3.org/1999/xlink')->href;
                    $content = $this->get_text_content($child);
                    $html .= '<a href="' . esc_url($href) . '">' . esc_html($content) . '</a>';
                    break;

                case 'note':
                    // Footnote
                    $note_class = (string)$child->attributes('urn:oasis:names:tc:opendocument:xmlns:text:1.0')->{'note-class'};
                    if ($note_class === 'footnote') {
                        $footnote_id = count($this->footnotes) + 1;
                        $footnote_content = $this->extract_footnote_content($child);
                        $this->footnotes[$footnote_id] = $footnote_content;
                        $html .= '<sup><a href="#fn-' . $footnote_id . '" id="fnref-' . $footnote_id . '">[' . $footnote_id . ']</a></sup>';
                    }
                    break;

                case 'line-break':
                    $html .= '<br />';
                    break;

                case 's':
                    // Whitespace
                    $count = (int)$child->attributes('urn:oasis:names:tc:opendocument:xmlns:text:1.0')->c;
                    $html .= str_repeat(' ', $count > 0 ? $count : 1);
                    break;

                case 'tab':
                    $html .= '&nbsp;&nbsp;&nbsp;&nbsp;';
                    break;

                default:
                    // Process text content
                    $html .= $this->process_inline_content($child);
                    break;
            }
        }

        // Also check for images
        foreach ($element->children('urn:oasis:names:tc:opendocument:xmlns:drawing:1.0') as $draw_element) {
            if ($draw_element->getName() === 'frame') {
                $html .= $this->process_image($draw_element);
            }
        }

        // Add direct text content
        $text = (string)$element;
        if (!empty($text)) {
            $html .= esc_html($text);
        }

        return $html;
    }

    /**
     * Apply formatting to content based on style name.
     *
     * @param string $content The content to format.
     * @param string $style_name The style name.
     * @return string The formatted content.
     */
    private function apply_formatting($content, $style_name) {
        $style_lower = strtolower($style_name);

        // Common LibreOffice style names
        if (strpos($style_lower, 'bold') !== false || strpos($style_lower, 'strong') !== false) {
            return '<strong>' . $content . '</strong>';
        }

        if (strpos($style_lower, 'italic') !== false || strpos($style_lower, 'emphasis') !== false) {
            return '<em>' . $content . '</em>';
        }

        if (strpos($style_lower, 'underline') !== false) {
            return '<u>' . $content . '</u>';
        }

        if (strpos($style_lower, 'code') !== false || strpos($style_lower, 'monospace') !== false) {
            return '<code>' . $content . '</code>';
        }

        // Default: no special formatting
        return $content;
    }

    /**
     * Process an image element.
     *
     * @param SimpleXMLElement $frame The frame element containing the image.
     * @return string The HTML img tag or placeholder.
     */
    private function process_image($frame) {
        $image_elements = $frame->children('urn:oasis:names:tc:opendocument:xmlns:drawing:1.0');
        
        foreach ($image_elements as $image) {
            if ($image->getName() === 'image') {
                $href = (string)$image->attributes('http://www.w3.org/1999/xlink')->href;
                
                // Extract image from ODT
                $image_data = $this->zip->getFromName($href);
                if ($image_data !== false) {
                    $image_id = count($this->images) + 1;
                    $extension = pathinfo($href, PATHINFO_EXTENSION);
                    
                    $this->images[$image_id] = array(
                        'data' => $image_data,
                        'extension' => $extension,
                        'original_name' => basename($href),
                    );
                    
                    return '<img src="{{IMAGE_' . $image_id . '}}" alt="Image ' . $image_id . '" />';
                }
            }
        }

        return '';
    }

    /**
     * Process a list element.
     *
     * @param SimpleXMLElement $list The list element.
     * @return string The HTML list.
     */
    private function process_list($list) {
        // Determine if it's ordered or unordered (would need to check styles)
        // For simplicity, we'll default to unordered lists
        $html = "<ul>\n";

        foreach ($list->children('urn:oasis:names:tc:opendocument:xmlns:text:1.0') as $item) {
            if ($item->getName() === 'list-item') {
                $html .= "<li>";
                foreach ($item->children('urn:oasis:names:tc:opendocument:xmlns:text:1.0') as $child) {
                    if ($child->getName() === 'p') {
                        $html .= $this->process_inline_content($child);
                    } elseif ($child->getName() === 'list') {
                        $html .= $this->process_list($child);
                    }
                }
                $html .= "</li>\n";
            }
        }

        $html .= "</ul>\n";
        return $html;
    }

    /**
     * Process a table element.
     *
     * @param SimpleXMLElement $table The table element.
     * @return string The HTML table.
     */
    private function process_table($table) {
        $html = "<table>\n";

        foreach ($table->children('urn:oasis:names:tc:opendocument:xmlns:table:1.0') as $row) {
            if ($row->getName() === 'table-row') {
                $html .= "<tr>\n";
                
                foreach ($row->children('urn:oasis:names:tc:opendocument:xmlns:table:1.0') as $cell) {
                    if ($cell->getName() === 'table-cell') {
                        $html .= "<td>";
                        foreach ($cell->children('urn:oasis:names:tc:opendocument:xmlns:text:1.0') as $child) {
                            $html .= $this->process_inline_content($child);
                        }
                        $html .= "</td>\n";
                    }
                }
                
                $html .= "</tr>\n";
            }
        }

        $html .= "</table>\n";
        return $html;
    }

    /**
     * Extract footnote content.
     *
     * @param SimpleXMLElement $note The note element.
     * @return string The footnote content.
     */
    private function extract_footnote_content($note) {
        $content = '';
        
        foreach ($note->children('urn:oasis:names:tc:opendocument:xmlns:text:1.0') as $child) {
            if ($child->getName() === 'note-body') {
                foreach ($child->children('urn:oasis:names:tc:opendocument:xmlns:text:1.0') as $para) {
                    $content .= $this->get_text_content($para) . ' ';
                }
            }
        }

        return trim($content);
    }

    /**
     * Get plain text content from an element and its children.
     *
     * @param SimpleXMLElement $element The XML element.
     * @return string The text content.
     */
    private function get_text_content($element) {
        $text = '';

        // Get direct text
        $text .= (string)$element;

        // Get text from all children recursively
        foreach ($element->children() as $child) {
            $text .= $this->get_text_content($child);
        }

        return $text;
    }
}
