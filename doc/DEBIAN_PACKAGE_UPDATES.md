# Debian Package Updates for GDPR Consent Management

## Overview
This document summarizes the changes made to the MultiFlexi Debian package configuration to include the new GDPR-compliant consent management system.

## Version Update
- **Previous version**: 1.29.0
- **New version**: 1.30.0

## Files Modified

### 1. `/debian/changelog`
**Added new changelog entry for version 1.30.0:**
- Added GDPR-compliant consent management system
- Cookie consent banner with persistent storage across sessions
- Privacy policy and cookie policy pages
- ConsentManager class for backend consent operations
- ConsentHelper utility functions for template integration
- Database migration for consent and consent_log tables
- Configurable Google Analytics based on ENABLE_GOOGLE_ANALYTICS setting
- Cookie-based consent persistence for anonymous users

### 2. `/debian/control` 
**Updated multiflexi-web package description:**
- Added mention of "GDPR-compliant consent management system with cookie policy support"

### 3. `/debian/multiflexi-web.install`
**Added explicit inclusion of Consent directory:**
- Added `src/MultiFlexi/Consent` to ensure ConsentManager and ConsentHelper classes are packaged

### 4. `/debian/README.Debian`
**Updated documentation:**
- Changed version reference from 1.29.0 to 1.30.0
- Added comprehensive "GDPR Consent Management" section explaining:
  - Cookie consent banner functionality
  - Privacy policy and cookie policy pages
  - Google Analytics configuration (disabled by default)
  - Database-backed consent tracking
  - Session-persistent consent storage
  - Instructions for accessing consent preferences
  - Configuration instructions for ENABLE_GOOGLE_ANALYTICS

### 5. `/debian/conf/.env.template`
**Already included (no changes needed):**
- `ENABLE_GOOGLE_ANALYTICS=false` setting already present
- Includes helpful comment about European privacy compliance

## New Files Included in Package

The following consent management files are automatically included via existing wildcard patterns:

### PHP Files (`src/*.php` pattern):
- `src/consent-api.php` - AJAX API for consent operations
- `src/consent-preferences.php` - User consent preferences page
- `src/cookie-policy.php` - Cookie policy page
- `src/privacy-policy.php` - Privacy policy page

### JavaScript Files (`src/js/*.js` pattern):
- `src/js/consent-banner.js` - Client-side consent banner with cookie persistence

### PHP Classes (`src/MultiFlexi/Consent` pattern):
- `src/MultiFlexi/Consent/ConsentManager.php` - Backend consent management
- `src/MultiFlexi/Consent/ConsentHelper.php` - Template helper functions

## Database Integration

The consent system integrates with the existing database migration system:
- Migration file: `../multiflexi-database/db/migrations/20241020100500_consent_management.php`
- Creates `consent` and `consent_log` tables
- Uses existing phinx migration system (`phinx-adapter.php`)
- No additional database configuration needed

## Key Features Included

1. **GDPR Compliance**: Complete consent management system
2. **Cookie Persistence**: User choices persist across browser sessions
3. **Privacy by Design**: Google Analytics disabled by default
4. **European-friendly**: Promotes self-hosted analytics (Matomo, AWStats)
5. **Audit Trail**: Complete logging of consent changes
6. **User-friendly**: Easy-to-use consent preferences interface

## Installation Notes

After package installation:
1. Database migrations run automatically
2. Consent banner appears on first visit
3. Users can manage preferences at `/consent-preferences.php`
4. Default configuration complies with European privacy regulations
5. Optional Google Analytics can be enabled via `ENABLE_GOOGLE_ANALYTICS=true`

## Package Dependencies

No new dependencies added - uses existing:
- PHP database libraries (already in multiflexi-web)
- Bootstrap 4 widgets (already in multiflexi-web) 
- Database migration system (multiflexi-database dependency)

## Testing

The package includes test files for development:
- `src/test-consent.php` - Comprehensive consent system test page
- `src/test-consent.html` - Simple HTML test page

## Backward Compatibility

- Fully backward compatible with existing installations
- New consent tables added without affecting existing data
- Existing users will see consent banner on first visit after upgrade
- No breaking changes to existing functionality