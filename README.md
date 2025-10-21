# LibreOffice Importer for WordPress

[![License](https://img.shields.io/badge/license-GPL%20v3-green.svg)](LICENSE.txt)
[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://www.php.net/)

A powerful WordPress plugin that allows you to easily import posts from LibreOffice Writer documents. Import via ODT files or copy/paste formatted content directly, with automatic extraction of title, author, abstract, images, and footnotes.

## Features

- **Multiple Import Methods**
  - Upload ODT (OpenDocument Text) files
  - Copy and paste formatted content directly from LibreOffice Writer

- **Intelligent Content Extraction**
  - Automatic title detection from the first line
  - Author name extraction and matching with WordPress users
  - Abstract/summary extraction from first few paragraphs
  - Preservation of all text formatting (headings, bold, italic, underline, etc.)

- **Rich Media Support**
  - Automatic image extraction and upload to WordPress media library
  - Image embedding with proper linking
  - Support for various image formats (PNG, JPG, GIF, etc.)

- **Advanced Features**
  - Footnote conversion to WordPress-style footnotes
  - List preservation (ordered and unordered)
  - Table support
  - Hyperlink preservation
  - Configurable post status (draft, pending, published)

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- PHP ZIP extension (for ODT file support)
- PHP XML extension (for content parsing)

## Installation

### Method 1: WordPress Admin

1. Download the plugin ZIP file or create it using `./build.sh`
2. Go to Plugins > Add New > Upload Plugin
3. Choose the ZIP file and click 'Install Now'
4. Click 'Activate Plugin'
5. Navigate to 'LO Importer' in the WordPress admin menu

### Method 2: Manual Installation (FTP/SSH)

```bash
# Via SSH
cd /path/to/wordpress/wp-content/plugins/
git clone https://github.com/johanneswilm/wordpress-libreoffice-importer.git libreoffice-importer
chown -R www-data:www-data libreoffice-importer
chmod -R 755 libreoffice-importer
```

Then activate via WordPress admin.

## Quick Start

### Method 1: Upload ODT File
1. Go to **LO Importer** in WordPress admin
2. Click **Upload ODT File** tab
3. Select your `.odt` file
4. Click **Import from ODT**
5. Your post is created as a draft

### Method 2: Copy & Paste
1. In LibreOffice: Select all (Ctrl+A) and copy (Ctrl+C)
2. In WordPress: Go to **LO Importer**
3. Click **Copy & Paste** tab
4. Paste in the editor (Ctrl+V)
5. Click **Create Post from Content**
6. Your post is created as a draft

## Usage

### Importing from ODT Files

1. Go to **LO Importer > Import Document** in your WordPress admin
2. Click the **Upload ODT File** tab
3. Select your `.odt` file
4. Click **Import from ODT**
5. Wait for processing to complete
6. View or edit your newly created post

### Copy & Paste Import

1. Open your document in LibreOffice Writer
2. Select all content (Ctrl+A / Cmd+A)
3. Copy (Ctrl+C / Cmd+C)
4. Go to **LO Importer > Import Document** in WordPress
5. Click the **Copy & Paste** tab
6. Click in the editor area and paste (Ctrl+V / Cmd+V)
7. Click **Create Post from Content**
8. Your post will be created with all formatting preserved

## Document Format Guidelines

For best results, structure your LibreOffice documents as follows:

### Title
The **first line** of your document will be used as the post title. Make it short and descriptive.

### Author Information
Include author information in one of these formats:
- `Author: John Doe`
- `By John Doe`
- `Written by: John Doe`

The plugin will attempt to match the author name with existing WordPress users.

### Abstract/Summary
The first 2-3 paragraphs after the title and author information will be considered as the post excerpt/abstract (configurable in settings).

Alternatively, you can explicitly mark an abstract:
```
Abstract: This is the summary of my post...
```

### Main Content
Write your main content using standard LibreOffice formatting:
- **Headings** (Heading 1-6) will be converted to HTML h1-h6
- **Bold**, *italic*, and <u>underline</u> formatting will be preserved
- Bulleted and numbered lists
- Tables
- Images (embedded inline)
- Hyperlinks
- Footnotes

## Configuration

Navigate to **LO Importer > Settings** to configure:

### Content Extraction
- **Extract Author Information**: Automatically detect and match authors
- **Extract Abstract/Summary**: Use first paragraphs as excerpt
- **Abstract Maximum Paragraphs**: Number of paragraphs to include (default: 3)

### Import Options
- **Import Images**: Upload images to media library
- **Import Footnotes**: Convert footnotes to WordPress format
- **Preserve Formatting**: Keep all text styling

### Post Settings
- **Default Post Status**: Set to Draft, Pending Review, or Published

## Examples

### Example 1: Simple Blog Post

```
My Amazing Blog Post

Author: Jane Smith

This is the introduction paragraph that will be used as the excerpt.

This is the main content of the post. It can include **bold text**, 
*italic text*, and [hyperlinks](https://example.com).

## Subheading

More content here with:
- Bullet points
- Multiple items
- Well formatted

### Another Subheading

Final thoughts and conclusions.
```

### Example 2: Academic Paper

```
Research on WordPress Import Methods

Author: Dr. John Doe

Abstract: This paper explores various methods for importing content into 
WordPress content management systems, with a focus on LibreOffice 
document formats and their conversion efficiency.

# Introduction

The need for efficient content migration...

# Methodology

We analyzed several approaches...

# Results

The findings indicate[1] that automated imports significantly 
reduce manual work.

# Conclusion

In conclusion, this research demonstrates...

---
[1] Based on a study of 100 documents
```

## Configuration

Navigate to **LO Importer > Settings** to configure:

### Content Extraction
- **Extract Author Information**: Automatically detect and match authors with WordPress users
- **Extract Abstract/Summary**: Use first paragraphs as excerpt
- **Abstract Maximum Paragraphs**: Number of paragraphs to include (default: 3)

### Import Options
- **Import Images**: Upload images to media library
- **Import Footnotes**: Convert footnotes to WordPress format
- **Preserve Formatting**: Keep all text styling

### Post Settings
- **Default Post Status**: Set to Draft, Pending Review, or Published (recommended: Draft)

## Troubleshooting

### ODT Files Won't Upload
- Check that the PHP ZIP extension is enabled
- Verify your WordPress upload file size limit
- Ensure the file is a valid ODT format

### Images Not Importing
- Check your server's upload file size limits
- Verify you have write permissions to the uploads directory
- Ensure the 'Import Images' option is enabled in settings

### Formatting Not Preserved
- Make sure 'Preserve Formatting' is enabled in settings
- Use standard LibreOffice styles rather than manual formatting
- Complex custom styles may not translate perfectly

### Author Not Matched
- Ensure the author name matches a WordPress user's display name or login
- Check that 'Extract Author Information' is enabled
- The plugin will use the current user if no match is found



## Credits

Developed by Johannes Wilm
- GitHub: https://github.com/johanneswilm
- Email: mail@johanneswilm.org

---

**Copyright © 2025 Johannes Wilm**  
**Licensed under GPL v3 or later**  
**Thank you for using LibreOffice Importer!**