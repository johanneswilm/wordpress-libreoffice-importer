<?php
/**
 * Parse HTML content from copy/paste operations.
 *
 * This class handles extracting content from HTML/rich text pasted from LibreOffice,
 * including:
 * - Title (first line)
 * - Author information
 * - Abstract/summary
 * - Main content with formatting
 * - Images (base64 encoded)
 * - Footnotes
 *
 * @package    LibreOffice_Importer
 * @subpackage LibreOffice_Importer/includes
 */

class LibreOffice_Importer_HTML_Parser {

    /**
     * The HTML content to parse.
     *
     * @var string
     */
    private $html;

    /**
     * DOMDocument for parsing HTML.
     *
     * @var DOMDocument
     */
    private $dom;

    /**
     * DOMXPath for querying the DOM.
     *
     * @var DOMXPath
     */
    private $xpath;

    /**
     * Extracted images.
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
     * @param string $html The HTML content to parse.
     */
    public function __construct($html) {
        $this->html = $html;
        $this->dom = new DOMDocument();
        
        // Suppress warnings for malformed HTML
        libxml_use_internal_errors(true);
        
        // Load HTML with UTF-8 encoding
        $this->dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        
        libxml_clear_errors();
        
        $this->xpath = new DOMXPath($this->dom);
    }

    /**
     * Parse the HTML content and extract all information.
     *
     * @return array Parsed content.
     */
    public function parse() {
        $result = array(
            'title' => $this->extract_title(),
            'author' => $this->extract_author(),
            'abstract' => $this->extract_abstract(),
            'content' => $this->extract_content(),
            'images' => $this->images,
            'footnotes' => $this->footnotes,
        );

        return $result;
    }

    /**
     * Extract the title (first significant text element).
     *
     * @return string The title.
     */
    private function extract_title() {
        // Look for first heading or first paragraph
        $headings = $this->xpath->query('//h1 | //h2 | //h3 | //h4 | //h5 | //h6');
        
        if ($headings->length > 0) {
            return trim($headings->item(0)->textContent);
        }

        // If no heading, use first paragraph
        $paragraphs = $this->xpath->query('//p');
        if ($paragraphs->length > 0) {
            $first_para = trim($paragraphs->item(0)->textContent);
            // If first paragraph is short, it's likely a title
            if (strlen($first_para) < 200) {
                return $first_para;
            }
        }

        // Fallback: get first non-empty text node
        $body = $this->dom->getElementsByTagName('body')->item(0);
        if ($body) {
            $text = trim($body->textContent);
            $lines = explode("\n", $text);
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    return substr($line, 0, 200); // Limit title length
                }
            }
        }

        return '';
    }

    /**
     * Extract author information from the content.
     *
     * @return string The author name.
     */
    private function extract_author() {
        $author = '';

        // Look for meta tags first
        $meta_author = $this->xpath->query('//meta[@name="author"]');
        if ($meta_author->length > 0) {
            $author = $meta_author->item(0)->getAttribute('content');
            return trim($author);
        }

        // Look for author in first few paragraphs
        $paragraphs = $this->xpath->query('//p');
        $count = 0;

        foreach ($paragraphs as $paragraph) {
            if ($count >= 5) {
                break;
            }

            $text = trim($paragraph->textContent);

            // Look for patterns like "Author: John Doe" or "By John Doe"
            if (preg_match('/^(?:Author|By|Written by):\s*(.+)$/i', $text, $matches)) {
                $author = trim($matches[1]);
                break;
            }

            // Look for "By John Doe" pattern
            if (preg_match('/^By\s+([A-Z][a-z]+(?:\s+[A-Z][a-z]+)+)$/i', $text, $matches)) {
                $author = trim($matches[1]);
                break;
            }

            $count++;
        }

        return $author;
    }

    /**
     * Extract abstract/summary from the first few paragraphs.
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

        $paragraphs = $this->xpath->query('//p');
        $abstract_parts = array();
        $count = 0;
        $skip = 0;

        foreach ($paragraphs as $paragraph) {
            $text = trim($paragraph->textContent);

            // Skip empty paragraphs
            if (empty($text)) {
                continue;
            }

            // Skip title (first non-empty paragraph if it's short)
            if ($skip === 0) {
                if (strlen($text) < 200) {
                    $skip++;
                    continue;
                }
            }

            // Skip author line if it looks like one
            if ($skip <= 1 && preg_match('/^(?:Author|By|Written by):/i', $text)) {
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
     * Extract and clean the main content.
     *
     * @return string The HTML-formatted content.
     */
    private function extract_content() {
        $body = $this->dom->getElementsByTagName('body')->item(0);
        
        if (!$body) {
            return '';
        }

        // Process the body content
        $html = $this->process_node($body, true);

        // Clean up the HTML
        $html = $this->clean_html($html);

        // Append footnotes if any
        if (!empty($this->footnotes)) {
            $html .= "\n\n" . $this->format_footnotes();
        }

        return $html;
    }

    /**
     * Process a DOM node and convert it to clean HTML.
     *
     * @param DOMNode $node The DOM node to process.
     * @param bool $is_root Whether this is the root node.
     * @return string The HTML representation.
     */
    private function process_node($node, $is_root = false) {
        $html = '';

        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $text = $child->textContent;
                if (!$is_root && trim($text) !== '') {
                    $html .= esc_html($text);
                }
            } elseif ($child->nodeType === XML_ELEMENT_NODE) {
                $tag = strtolower($child->nodeName);
                
                switch ($tag) {
                    case 'h1':
                    case 'h2':
                    case 'h3':
                    case 'h4':
                    case 'h5':
                    case 'h6':
                        $content = $this->process_node($child);
                        if (!empty(trim(strip_tags($content)))) {
                            $html .= "<{$tag}>{$content}</{$tag}>\n";
                        }
                        break;

                    case 'p':
                        $content = $this->process_node($child);
                        if (!empty(trim(strip_tags($content)))) {
                            $html .= "<p>{$content}</p>\n";
                        }
                        break;

                    case 'strong':
                    case 'b':
                        $content = $this->process_node($child);
                        $html .= "<strong>{$content}</strong>";
                        break;

                    case 'em':
                    case 'i':
                        $content = $this->process_node($child);
                        $html .= "<em>{$content}</em>";
                        break;

                    case 'u':
                        $content = $this->process_node($child);
                        $html .= "<u>{$content}</u>";
                        break;

                    case 'strike':
                    case 's':
                    case 'del':
                        $content = $this->process_node($child);
                        $html .= "<del>{$content}</del>";
                        break;

                    case 'code':
                        $content = $this->process_node($child);
                        $html .= "<code>{$content}</code>";
                        break;

                    case 'pre':
                        $content = $this->process_node($child);
                        $html .= "<pre>{$content}</pre>\n";
                        break;

                    case 'a':
                        $href = $child->getAttribute('href');
                        $content = $this->process_node($child);
                        if (!empty($href)) {
                            $html .= '<a href="' . esc_url($href) . '">' . $content . '</a>';
                        } else {
                            $html .= $content;
                        }
                        break;

                    case 'img':
                        $html .= $this->process_image($child);
                        break;

                    case 'ul':
                        $html .= "<ul>\n" . $this->process_node($child) . "</ul>\n";
                        break;

                    case 'ol':
                        $html .= "<ol>\n" . $this->process_node($child) . "</ol>\n";
                        break;

                    case 'li':
                        $content = $this->process_node($child);
                        $html .= "<li>{$content}</li>\n";
                        break;

                    case 'table':
                        $html .= "<table>\n" . $this->process_node($child) . "</table>\n";
                        break;

                    case 'thead':
                        $html .= "<thead>\n" . $this->process_node($child) . "</thead>\n";
                        break;

                    case 'tbody':
                        $html .= "<tbody>\n" . $this->process_node($child) . "</tbody>\n";
                        break;

                    case 'tr':
                        $html .= "<tr>\n" . $this->process_node($child) . "</tr>\n";
                        break;

                    case 'th':
                        $content = $this->process_node($child);
                        $html .= "<th>{$content}</th>\n";
                        break;

                    case 'td':
                        $content = $this->process_node($child);
                        $html .= "<td>{$content}</td>\n";
                        break;

                    case 'br':
                        $html .= "<br />\n";
                        break;

                    case 'hr':
                        $html .= "<hr />\n";
                        break;

                    case 'blockquote':
                        $content = $this->process_node($child);
                        $html .= "<blockquote>{$content}</blockquote>\n";
                        break;

                    case 'sup':
                        $content = $this->process_node($child);
                        // Check if this is a footnote reference
                        if ($this->is_footnote_reference($child)) {
                            $footnote_id = count($this->footnotes) + 1;
                            $this->footnotes[$footnote_id] = $content;
                            $html .= '<sup><a href="#fn-' . $footnote_id . '" id="fnref-' . $footnote_id . '">[' . $footnote_id . ']</a></sup>';
                        } else {
                            $html .= "<sup>{$content}</sup>";
                        }
                        break;

                    case 'sub':
                        $content = $this->process_node($child);
                        $html .= "<sub>{$content}</sub>";
                        break;

                    case 'div':
                    case 'span':
                        // For div and span, just process children
                        $html .= $this->process_node($child);
                        break;

                    case 'script':
                    case 'style':
                    case 'meta':
                    case 'link':
                    case 'head':
                        // Skip these elements
                        break;

                    default:
                        // For unknown elements, process children
                        $html .= $this->process_node($child);
                        break;
                }
            }
        }

        return $html;
    }

    /**
     * Process an image element.
     *
     * @param DOMElement $img The img element.
     * @return string The HTML img tag or placeholder.
     */
    private function process_image($img) {
        $src = $img->getAttribute('src');
        
        if (empty($src)) {
            return '';
        }

        // Check if it's a base64 encoded image
        if (strpos($src, 'data:image/') === 0) {
            $image_id = count($this->images) + 1;
            
            // Extract image data
            if (preg_match('/^data:image\/([a-z]+);base64,(.+)$/i', $src, $matches)) {
                $extension = $matches[1];
                $data = base64_decode($matches[2]);
                
                $this->images[$image_id] = array(
                    'data' => $data,
                    'extension' => $extension,
                    'original_name' => 'image.' . $extension,
                );
                
                $alt = $img->getAttribute('alt');
                return '<img src="{{IMAGE_' . $image_id . '}}" alt="' . esc_attr($alt) . '" />';
            }
        } elseif (filter_var($src, FILTER_VALIDATE_URL)) {
            // External URL - keep as is
            $alt = $img->getAttribute('alt');
            return '<img src="' . esc_url($src) . '" alt="' . esc_attr($alt) . '" />';
        }

        return '';
    }

    /**
     * Check if a sup element is a footnote reference.
     *
     * @param DOMElement $sup The sup element.
     * @return bool True if it's a footnote reference.
     */
    private function is_footnote_reference($sup) {
        $text = trim($sup->textContent);
        
        // Check if it's a number in brackets or just a number
        if (preg_match('/^\[?\d+\]?$/', $text)) {
            return true;
        }

        // Check if it contains a link to a footnote
        $links = $sup->getElementsByTagName('a');
        if ($links->length > 0) {
            $href = $links->item(0)->getAttribute('href');
            if (strpos($href, '#fn') !== false || strpos($href, '#footnote') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Format footnotes as HTML.
     *
     * @return string The formatted footnotes.
     */
    private function format_footnotes() {
        if (empty($this->footnotes)) {
            return '';
        }

        $html = '<div class="footnotes"><hr /><ol>';

        foreach ($this->footnotes as $id => $content) {
            $html .= '<li id="fn-' . $id . '">' . $content . ' <a href="#fnref-' . $id . '">↩</a></li>';
        }

        $html .= '</ol></div>';

        return $html;
    }

    /**
     * Clean up HTML by removing unwanted attributes and normalizing.
     *
     * @param string $html The HTML to clean.
     * @return string The cleaned HTML.
     */
    private function clean_html($html) {
        // Remove multiple consecutive newlines
        $html = preg_replace('/\n{3,}/', "\n\n", $html);

        // Remove empty paragraphs
        $html = preg_replace('/<p>\s*<\/p>/', '', $html);

        // Remove inline styles (LibreOffice adds a lot of these)
        $html = preg_replace('/ style="[^"]*"/', '', $html);

        // Remove class attributes (except for specific ones we want to keep)
        $html = preg_replace('/ class="(?!footnotes)[^"]*"/', '', $html);

        // Normalize whitespace
        $html = preg_replace('/[ \t]+/', ' ', $html);

        return trim($html);
    }
}