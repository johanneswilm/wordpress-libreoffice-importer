# WordPress LibreOffice Importer

**Version**: 1.0.0  
**Release Date**: October 21, 2025  
**License**: GPL v3 or later  
**Author**: Johannes Wilm (mail@johanneswilm.org)  
**Repository**: https://github.com/johanneswilm/wordpress-libreoffice-importer

## Overview

A WordPress plugin that enables seamless import of LibreOffice Writer documents into WordPress posts. Supports ODT file uploads and copy/paste operations with automatic extraction of title, author, abstract, images, and footnotes.

## Key Features

- **Two Import Methods**: Upload ODT files or copy/paste from LibreOffice
- **Smart Content Extraction**: Automatic title, author, and abstract detection
- **Image Handling**: Extracts and uploads images to WordPress media library
- **Footnote Conversion**: Converts LibreOffice footnotes to WordPress format
- **Formatting Preservation**: Maintains bold, italic, headings, lists, and tables
- **Author Matching**: Matches author names with existing WordPress users
- **Configurable Settings**: Control extraction, import options, and post defaults

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- PHP Extensions: ZIP, XML, DOM, SimpleXML

## Installation

1. Upload plugin to WordPress via Admin or FTP
2. Activate the plugin
3. Navigate to **LO Importer** in WordPress admin
4. Configure settings as needed
5. Start importing documents

## Quick Start

**Upload ODT File:**
1. Go to LO Importer → Upload ODT File tab
2. Select your .odt file
3. Click Import from ODT

**Copy & Paste:**
1. Copy content from LibreOffice (Ctrl+A, Ctrl+C)
2. Go to LO Importer → Copy & Paste tab
3. Paste in editor (Ctrl+V)
4. Click Create Post from Content

## Documentation

- **README.md** - Complete user documentation and examples
- **CONTRIBUTING.md** - Developer contribution guidelines
- **CHANGELOG.md** - Version history and release notes
- **LICENSE.txt** - GPL v3 license text

## Project Structure

```
wordpress-libreoffice-importer/
├── libreoffice-importer.php        # Main plugin file
├── includes/                        # Core classes (7 files)
│   ├── class-odt-parser.php        # ODT file parser
│   ├── class-html-parser.php       # HTML content parser
│   └── class-post-creator.php      # WordPress post creator
├── admin/                          # Admin interface
│   ├── class-admin.php             # Admin controller
│   └── partials/                   # UI templates
├── assets/                         # CSS and JavaScript
└── docs/                           # Documentation files
```

## Statistics

- **PHP Code**: 2,677 lines across 7 core classes
- **JavaScript**: 400+ lines
- **CSS**: 500+ lines
- **Documentation**: Complete user and developer guides

## Support

- **Issues**: https://github.com/johanneswilm/wordpress-libreoffice-importer/issues
- **Email**: mail@johanneswilm.org
- **Documentation**: See README.md

## Contributing

Contributions are welcome! See CONTRIBUTING.md for guidelines.

## License

Licensed under GPL v3 or later. Free to use, modify, and distribute.

---

**Copyright © 2025 Johannes Wilm**