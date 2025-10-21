# Changelog

All notable changes to the LibreOffice Importer plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-10-21

### Added
- Initial release of LibreOffice Importer plugin
- ODT file upload and parsing functionality
- Copy/paste import from LibreOffice Writer
- Automatic title extraction from first line of document
- Author name detection and WordPress user matching
- Abstract/summary extraction from first paragraphs
- Full preservation of text formatting (bold, italic, underline, strikethrough)
- Heading conversion (H1-H6)
- List support (ordered and unordered)
- Table import functionality
- Image extraction and automatic upload to WordPress media library
- Support for multiple image formats (PNG, JPG, GIF, BMP, WebP, SVG)
- Footnote conversion to WordPress-style footnotes
- Hyperlink preservation
- Admin interface with tabbed navigation
- Settings page with configuration options
- Real-time import progress indication
- Success modal with post details
- File upload preview
- Content preview for paste import
- Configurable post status (draft, pending, published)
- Configurable abstract extraction (enable/disable, paragraph count)
- System information display in settings
- PHP extension checks (ZIP, XML)
- Responsive admin interface
- AJAX-based file upload with progress tracking
- Comprehensive error handling and user feedback
- WordPress coding standards compliance
- Security features (nonces, capability checks, input sanitization)

### Features
- **Multiple Import Methods**: Upload ODT files or copy/paste content
- **Smart Content Detection**: Automatically identifies title, author, and abstract
- **Rich Formatting**: Preserves all LibreOffice Writer formatting
- **Media Management**: Handles images with automatic upload
- **Footnote Support**: Converts footnotes to WordPress format
- **Author Matching**: Matches author names with existing WordPress users
- **Flexible Configuration**: Extensive settings for customization
- **User-Friendly Interface**: Clean, intuitive admin interface
- **Progress Tracking**: Visual feedback during import process
- **Error Recovery**: Graceful error handling with helpful messages

### Technical
- Minimum WordPress version: 5.0
- Minimum PHP version: 7.4
- Required PHP extensions: ZIP, XML
- Uses WordPress coding standards
- Object-oriented architecture
- Modular parser design (ODT and HTML parsers)
- Secure file handling with validation
- Database optimization with WordPress API
- Proper escaping and sanitization
- Translation-ready with text domain

### Documentation
- Comprehensive README.md with usage examples
- Detailed INSTALLATION.md guide
- Inline code documentation
- PHPDoc comments for all classes and methods
- Troubleshooting section
- FAQ section
- Developer hooks and filters documentation



## Version History

### Version Numbering
- **Major version** (1.x.x): Breaking changes, major new features
- **Minor version** (x.1.x): New features, backward compatible
- **Patch version** (x.x.1): Bug fixes, minor improvements

### Release Schedule
- Major releases: Annually
- Minor releases: Quarterly
- Patch releases: As needed for critical bugs



---

For detailed information about changes, see the [commit history](https://github.com/johanneswilm/wordpress-libreoffice-importer/commits/main).

---

**Copyright © 2025 Johannes Wilm**  
**Licensed under GPL v3 or later**
