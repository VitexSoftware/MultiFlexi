# MultiFlexi Development Guidelines for GitHub Copilot

<!-- Custom workspace instructions for GitHub Copilot to ensure consistent code quality and project standards -->

## 🌍 Language Requirements

- **Code comments**: Always write in English
- **Error messages**: Always write in English  
- **Documentation**: Always write in English
- **Commit messages**: Use imperative mood and keep concise (e.g., "Fix login bug", "Add user validation")

## 🐘 PHP Standards & Requirements

### Version & Standards
- **PHP Version**: Use PHP 8.4 or later
- **Coding Standard**: Follow PSR-12 coding standard strictly
- **Type Safety**: Always include type hints for function parameters and return types

### Documentation Requirements
- **Docblocks**: Include for all functions and classes with purpose, parameters, and return types
- **Code Comments**: Use complete sentences and proper grammar
- **Variable Names**: Use meaningful, descriptive names that explain their purpose

### Code Quality Rules
- **Constants**: Define constants instead of magic numbers or strings
- **Exception Handling**: Always handle exceptions properly with meaningful error messages
- **Security**: Ensure code doesn't expose sensitive information
- **Performance**: Consider and optimize performance where necessary
- **Compatibility**: Ensure compatibility with latest PHP version and project libraries

## 🧪 Testing Requirements

- **Framework**: Use PHPUnit for all tests
- **Standard**: Follow PSR-12 in test files
- **Coverage**: Include unit tests for all applicable code
- **Class Updates**: When creating or updating classes, always create or update corresponding PHPUnit test files

## 📁 Project Structure & Paths

### Development Execution
Always run main scripts from their designated directories:

```bash
# Web application
cd src/
php index.php

# JSON to app conversion
cd lib/
php json2app.php
```

**Why**: Ensures relative paths (`../vendor/autoload.php` and `../.env`) work correctly during development.

### Asset Management Philosophy
The project uses a dual-path system for JavaScript and CSS files:

#### Development Environment
- **Local files**: Use relative paths (e.g., `js/summernote-bs4.min.js`, `css/font-awesome.min.css`)
- **Purpose**: Enables intranet/offline capability during development
- **Location**: Files stored in local `js/` and `css/` directories

#### Production Environment (Debian Package)
- **System files**: Automatically converted to system paths (e.g., `/javascript/jquery-datatables/jquery.dataTables.js`)
- **Conversion**: Handled automatically by sed commands in `debian/rules` during packaging
- **Detection**: Use `apt-file search <filename>` to verify system package availability
- **Fallback**: Uses bundled local files if system packages unavailable

#### ⚠️ Important Rules
- **NO manual path changes** needed in source code
- **Keep relative paths** in development - automation handles production conversion
- **Don't modify** paths for production manually

## 🔧 Development Workflow

### Mandatory PHP Validation
After **every single edit** to a PHP file:
```bash
php -l path/to/edited/file.php
```
This syntax check is **mandatory** before proceeding with further changes.

### Internationalization
- **i18n Library**: Use `_()` functions for all user-facing strings that need translation
- **Example**: `echo _('Welcome to MultiFlexi');` instead of `echo 'Welcome to MultiFlexi';`

## 📋 Schema Validation

### Application Definition Files
- **Pattern**: `*.app.json`
- **Schema**: https://raw.githubusercontent.com/VitexSoftware/php-vitexsoftware-multiflexi-core/refs/heads/main/multiflexi.app.schema.json
- **Requirement**: All app.json files must conform to this schema

### Credential Type Files  
- **Pattern**: `*.credential-type.json`
- **Schema**: https://raw.githubusercontent.com/VitexSoftware/php-vitexsoftware-multiflexi-core/refs/heads/main/multiflexi.credential-type.schema.json
- **Requirement**: All credential-type.json files must conform to this schema

## 📖 Documentation Standards

### Code Documentation
- **In-code docs**: Follow reStructuredText (reST) format for docs/ folder
- **Docblocks**: Use standard PHPDoc format for classes and functions
- **Comments**: Explain complex logic and business rules

### Best Practices Summary
1. **Security First**: Always validate input and sanitize output
2. **Type Safety**: Use strict typing throughout the codebase
3. **Error Handling**: Implement comprehensive exception handling
4. **Testing**: Write tests for new features and bug fixes
5. **Performance**: Consider performance implications of code changes
6. **Maintainability**: Write clean, readable, well-documented code

---
*This file ensures GitHub Copilot follows MultiFlexi project standards and conventions.*
