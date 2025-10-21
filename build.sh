#!/bin/bash
# Build script for WordPress LibreOffice Importer Plugin
# Author: Johannes Wilm
# License: GPL v3 or later

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
PLUGIN_SLUG="libreoffice-importer"
PLUGIN_VERSION="1.0.0"
BUILD_DIR="build"
DIST_DIR="dist"

# Print colored message
print_message() {
    echo -e "${GREEN}[BUILD]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

# Check if we're in the right directory
if [ ! -f "libreoffice-importer.php" ]; then
    print_error "This script must be run from the plugin root directory"
    exit 1
fi

print_message "Starting build process for $PLUGIN_SLUG v$PLUGIN_VERSION"

# Clean previous builds
print_message "Cleaning previous builds..."
rm -rf "$BUILD_DIR"
rm -rf "$DIST_DIR"
mkdir -p "$BUILD_DIR"
mkdir -p "$DIST_DIR"

# Create plugin directory in build
PLUGIN_DIR="$BUILD_DIR/$PLUGIN_SLUG"
mkdir -p "$PLUGIN_DIR"

print_message "Copying plugin files..."

# Copy files and directories
rsync -av --progress . "$PLUGIN_DIR" \
    --exclude .git \
    --exclude .github \
    --exclude .gitignore \
    --exclude .editorconfig \
    --exclude node_modules \
    --exclude vendor \
    --exclude build \
    --exclude dist \
    --exclude tests \
    --exclude .DS_Store \
    --exclude Thumbs.db \
    --exclude composer.json \
    --exclude composer.lock \
    --exclude package.json \
    --exclude package-lock.json \
    --exclude phpunit.xml \
    --exclude .phpunit.result.cache \
    --exclude "*.log" \
    --exclude "*.swp" \
    --exclude "*.swo" \
    --exclude "*~" \
    --exclude build.sh \
    --exclude README-GITHUB.md \
    --exclude PROJECT-OVERVIEW.md

print_message "Files copied successfully"

# Optimize PHP files (remove comments, whitespace)
if command -v php &> /dev/null; then
    print_message "Optimizing PHP files..."
    # Note: This is optional and commented out by default
    # Uncomment if you want to strip comments and optimize
    # find "$PLUGIN_DIR" -name "*.php" -type f -exec php -w {} > {}.tmp \; -exec mv {}.tmp {} \;
fi

# Create ZIP archive
print_message "Creating ZIP archive..."
cd "$BUILD_DIR"
ZIP_FILE="../$DIST_DIR/${PLUGIN_SLUG}-${PLUGIN_VERSION}.zip"

if command -v zip &> /dev/null; then
    zip -r "$ZIP_FILE" "$PLUGIN_SLUG" -q
    print_message "ZIP archive created: $ZIP_FILE"
else
    print_error "zip command not found. Please install zip utility."
    exit 1
fi

cd ..

# Calculate file size
FILE_SIZE=$(du -h "$DIST_DIR/${PLUGIN_SLUG}-${PLUGIN_VERSION}.zip" | cut -f1)
print_message "Archive size: $FILE_SIZE"

# Generate checksum
print_message "Generating checksums..."
if command -v sha256sum &> /dev/null; then
    sha256sum "$DIST_DIR/${PLUGIN_SLUG}-${PLUGIN_VERSION}.zip" > "$DIST_DIR/${PLUGIN_SLUG}-${PLUGIN_VERSION}.sha256"
    print_message "SHA256 checksum created"
elif command -v shasum &> /dev/null; then
    shasum -a 256 "$DIST_DIR/${PLUGIN_SLUG}-${PLUGIN_VERSION}.zip" > "$DIST_DIR/${PLUGIN_SLUG}-${PLUGIN_VERSION}.sha256"
    print_message "SHA256 checksum created"
else
    print_warning "sha256sum/shasum not found. Skipping checksum generation."
fi

# Generate MD5 checksum
if command -v md5sum &> /dev/null; then
    md5sum "$DIST_DIR/${PLUGIN_SLUG}-${PLUGIN_VERSION}.zip" > "$DIST_DIR/${PLUGIN_SLUG}-${PLUGIN_VERSION}.md5"
    print_message "MD5 checksum created"
elif command -v md5 &> /dev/null; then
    md5 "$DIST_DIR/${PLUGIN_SLUG}-${PLUGIN_VERSION}.zip" > "$DIST_DIR/${PLUGIN_SLUG}-${PLUGIN_VERSION}.md5"
    print_message "MD5 checksum created"
fi

# Create release notes
print_message "Creating release notes..."
cat > "$DIST_DIR/RELEASE-NOTES.txt" << EOF
WordPress LibreOffice Importer - Version $PLUGIN_VERSION
========================================================

Release Date: $(date +%Y-%m-%d)

Installation Instructions:
--------------------------
1. Download ${PLUGIN_SLUG}-${PLUGIN_VERSION}.zip
2. Log in to WordPress admin
3. Go to Plugins → Add New → Upload Plugin
4. Choose the ZIP file and click Install Now
5. Activate the plugin
6. Go to LO Importer in the admin menu

Requirements:
------------
- WordPress 5.0 or higher
- PHP 7.4 or higher
- PHP Extensions: ZIP, XML

What's New in $PLUGIN_VERSION:
------------------------------
- Initial release
- ODT file upload support
- Copy/paste import functionality
- Automatic title, author, and abstract extraction
- Image import with media library integration
- Footnote conversion
- Comprehensive settings panel

Documentation:
-------------
- README.md - Complete documentation
- QUICK-START.md - Quick start guide
- INSTALLATION.md - Installation guide

Support:
-------
- GitHub: https://github.com/johanneswilm/wordpress-libreoffice-importer
- Issues: https://github.com/johanneswilm/wordpress-libreoffice-importer/issues
- Email: mail@johanneswilm.org

License: GPL v3 or later
EOF

print_message "Release notes created"

# List files in dist directory
print_message "Build complete! Files in dist directory:"
ls -lh "$DIST_DIR"

# Summary
echo ""
echo "=========================================="
echo "Build Summary"
echo "=========================================="
echo "Plugin: $PLUGIN_SLUG"
echo "Version: $PLUGIN_VERSION"
echo "Archive: $DIST_DIR/${PLUGIN_SLUG}-${PLUGIN_VERSION}.zip"
echo "Size: $FILE_SIZE"
echo "=========================================="
echo ""

print_message "To install, upload the ZIP file via WordPress admin or extract to wp-content/plugins/"

# Optional: Clean build directory
read -p "$(echo -e ${YELLOW}[BUILD]${NC} Clean build directory? [y/N]:) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    rm -rf "$BUILD_DIR"
    print_message "Build directory cleaned"
fi

print_message "Done!"