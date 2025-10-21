# Contributing to LibreOffice Importer

Thank you for considering contributing to LibreOffice Importer! This document provides guidelines and instructions for contributing to the project.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [How to Contribute](#how-to-contribute)
- [Coding Standards](#coding-standards)
- [Testing](#testing)
- [Submitting Changes](#submitting-changes)
- [Reporting Bugs](#reporting-bugs)
- [Feature Requests](#feature-requests)

## Code of Conduct

### Our Pledge

We are committed to providing a welcoming and inspiring community for all. Please be respectful and constructive in all interactions.

### Expected Behavior

- Be respectful of differing viewpoints and experiences
- Accept constructive criticism gracefully
- Focus on what is best for the community
- Show empathy towards other community members

### Unacceptable Behavior

- Harassment, discriminatory language, or personal attacks
- Trolling or insulting/derogatory comments
- Publishing others' private information
- Other conduct which could reasonably be considered inappropriate

## Getting Started

1. Fork the repository on GitHub
2. Clone your fork locally
3. Set up the development environment
4. Create a branch for your changes
5. Make your changes
6. Test thoroughly
7. Submit a pull request

## Development Setup

### Prerequisites

- WordPress development environment (Local, XAMPP, Docker, etc.)
- PHP 7.4 or higher
- Composer (optional, for dependency management)
- Git
- Text editor or IDE (VS Code, PHPStorm, etc.)

### Local Development Environment

1. **Clone the repository:**
   ```bash
   git clone https://github.com/johanneswilm/wordpress-libreoffice-importer.git
   cd wordpress-libreoffice-importer
   ```

2. **Copy to WordPress plugins directory:**
   ```bash
   cp -r . /path/to/wordpress/wp-content/plugins/libreoffice-importer/
   ```

3. **Enable WordPress debugging:**
   Add to `wp-config.php`:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   define('SCRIPT_DEBUG', true);
   ```

4. **Activate the plugin:**
   - Log in to WordPress admin
   - Go to Plugins → Activate LibreOffice Importer

### Development Tools

**Recommended VS Code Extensions:**
- PHP Intelephense
- WordPress Snippets
- ESLint
- EditorConfig

**Recommended PHPStorm Plugins:**
- WordPress Support
- PHP Inspections (EA Extended)
- .ignore

## How to Contribute

### Types of Contributions

We welcome many types of contributions:

- **Bug fixes** - Help us squash bugs
- **New features** - Add functionality
- **Documentation** - Improve or translate docs
- **Code optimization** - Performance improvements
- **Testing** - Write unit or integration tests
- **UI/UX** - Improve user interface
- **Translations** - Localize the plugin

### Contribution Workflow

1. **Find or create an issue** discussing what you want to work on
2. **Fork the repository** to your GitHub account
3. **Create a feature branch** from `main`:
   ```bash
   git checkout -b feature/your-feature-name
   ```
4. **Make your changes** following our coding standards
5. **Test thoroughly** with various WordPress versions
6. **Commit your changes** with clear messages
7. **Push to your fork**
8. **Submit a pull request** with a clear description

## Coding Standards

### WordPress Coding Standards

This project follows [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/):

- Use tabs for indentation, not spaces
- Follow WordPress naming conventions
- Use proper escaping and sanitization
- Add proper PHPDoc comments

### PHP Standards

```php
<?php
/**
 * Class description.
 *
 * @package    LibreOffice_Importer
 * @subpackage LibreOffice_Importer/includes
 */

class Example_Class {
    
    /**
     * Property description.
     *
     * @var string
     */
    private $property;
    
    /**
     * Method description.
     *
     * @param string $param Parameter description.
     * @return bool True on success, false on failure.
     */
    public function example_method($param) {
        // Code here
        return true;
    }
}
```

### JavaScript Standards

```javascript
/**
 * Function description.
 *
 * @param {string} param - Parameter description.
 * @return {boolean} True on success, false on failure.
 */
function exampleFunction(param) {
    // Use single quotes for strings
    const variable = 'value';
    
    // Use meaningful variable names
    const isValid = true;
    
    return isValid;
}
```

### CSS Standards

```css
/* Component name */
.libreoffice-importer-component {
    display: block;
    margin: 0;
    padding: 20px;
}

/* Use BEM-like naming for sub-elements */
.libreoffice-importer-component__element {
    color: #333;
}

/* Modifier classes */
.libreoffice-importer-component--active {
    background: #0073aa;
}
```

### File Organization

- One class per file
- Use appropriate file names matching class names
- Follow the existing directory structure
- Keep files focused and modular

### Security Best Practices

Always follow these security practices:

1. **Sanitize Input:**
   ```php
   $clean_value = sanitize_text_field($_POST['value']);
   ```

2. **Escape Output:**
   ```php
   echo esc_html($variable);
   echo esc_url($url);
   echo esc_attr($attribute);
   ```

3. **Check Capabilities:**
   ```php
   if (!current_user_can('edit_posts')) {
       wp_die('Permission denied');
   }
   ```

4. **Verify Nonces:**
   ```php
   check_ajax_referer('nonce_name', 'nonce_field');
   ```

5. **Validate File Uploads:**
   ```php
   $allowed_types = array('odt');
   $file_type = wp_check_filetype($file_name, $allowed_types);
   ```

## Testing

### Manual Testing

Before submitting a pull request:

1. **Test on multiple WordPress versions:**
   - Latest stable
   - Previous major version
   - Minimum supported version (5.0)

2. **Test on different PHP versions:**
   - PHP 7.4
   - PHP 8.0
   - PHP 8.1+

3. **Test all features:**
   - ODT upload
   - Copy/paste import
   - Image handling
   - Footnote conversion
   - Author matching
   - Settings changes

4. **Test edge cases:**
   - Empty documents
   - Very large documents
   - Special characters
   - Various image formats
   - Malformed ODT files

### Browser Testing

Test the admin interface in:
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

### Creating Test Cases

When adding new features, include:

1. **Test documents** in the `/tests/fixtures/` directory
2. **Documentation** of expected behavior
3. **Screenshots** if UI is affected

## Submitting Changes

### Pull Request Process

1. **Update documentation** if needed
2. **Add yourself to contributors** in README.md
3. **Ensure all tests pass**
4. **Write a clear PR description:**

```markdown
## Description
Brief description of what this PR does.

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
Describe how you tested your changes.

## Screenshots (if applicable)
Add screenshots for UI changes.

## Checklist
- [ ] Code follows WordPress coding standards
- [ ] Tested on WordPress 5.0+
- [ ] Tested on PHP 7.4+
- [ ] Documentation updated
- [ ] No new warnings or errors
```

### Commit Message Guidelines

Use clear, descriptive commit messages:

**Good:**
```
Add support for nested lists in ODT parser

- Implement recursive list processing
- Add test cases for nested lists
- Update documentation
```

**Bad:**
```
Fixed stuff
```

**Commit Message Format:**
```
<type>: <subject>

<body>

<footer>
```

**Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting)
- `refactor`: Code refactoring
- `test`: Adding tests
- `chore`: Maintenance tasks

### Code Review Process

1. Maintainers will review your PR
2. Address any requested changes
3. Once approved, your PR will be merged
4. Your contribution will be included in the next release

## Reporting Bugs

### Before Submitting a Bug Report

1. **Check existing issues** - Someone may have already reported it
2. **Test with default theme** - Rule out theme conflicts
3. **Disable other plugins** - Rule out plugin conflicts
4. **Try latest version** - Bug may already be fixed

### Bug Report Template

```markdown
**Describe the bug**
A clear description of what the bug is.

**To Reproduce**
Steps to reproduce the behavior:
1. Go to '...'
2. Click on '...'
3. See error

**Expected behavior**
What you expected to happen.

**Screenshots**
If applicable, add screenshots.

**Environment:**
 - WordPress version: [e.g. 6.4]
 - PHP version: [e.g. 8.1]
 - Plugin version: [e.g. 1.0.0]
 - Browser: [e.g. Chrome 120]

**Additional context**
Any other relevant information.
```

## Feature Requests

We welcome feature requests! Please provide:

1. **Clear description** of the feature
2. **Use case** - Why is this needed?
3. **Proposed implementation** (if you have ideas)
4. **Willingness to contribute** - Can you help implement it?

### Feature Request Template

```markdown
**Is your feature request related to a problem?**
A clear description of the problem.

**Describe the solution you'd like**
A clear description of what you want to happen.

**Describe alternatives you've considered**
Alternative solutions or features you've considered.

**Additional context**
Any other context or screenshots.
```

## Communication

- **GitHub Issues** - Bug reports and feature requests: https://github.com/johanneswilm/wordpress-libreoffice-importer/issues
- **GitHub Discussions** - General questions and ideas
- **Pull Requests** - Code contributions: https://github.com/johanneswilm/wordpress-libreoffice-importer/pulls
- **Email** - Private security issues: mail@johanneswilm.org

## Recognition

Contributors will be recognized:
- Listed in CHANGELOG.md
- Mentioned in release notes
- Added to contributors list in README.md

## License

By contributing, you agree that your contributions will be licensed under the GPL v3 or later license.

## Questions?

Don't hesitate to ask questions! We're here to help:
- Open a GitHub Discussion
- Comment on an existing issue
- Reach out to maintainers

---

**Thank you for contributing to LibreOffice Importer!** 🎉

Your contributions help make WordPress better for everyone.

---

**Copyright © 2025 Johannes Wilm**  
**Licensed under GPL v3 or later**