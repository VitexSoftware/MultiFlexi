# GDPR Article 15 - Right of Access Implementation

This document describes the implementation of GDPR Article 15 (Right of Access) in MultiFlexi, allowing users to export all their personal data in a structured, machine-readable format.

## Overview

The implementation provides users with the ability to request and download a complete export of all personal data held about them, in compliance with GDPR Article 15 requirements.

## Features

- **Complete Data Export**: Exports all personal data categories including user profile, activity logs, consent records, company associations, and credential metadata
- **Multiple Formats**: Supports JSON (machine-readable) and text (human-readable) export formats
- **Security Controls**: Implements rate limiting, secure token-based downloads, and comprehensive audit logging
- **User Interface**: Intuitive dashboard widget and dedicated data export page
- **Email Notifications**: Automated notifications for export readiness and download confirmation
- **GDPR Compliance**: Full compliance with legal requirements including proper data controller information and audit trails

## Architecture

### Core Components

#### 1. DataExportApi (`MultiFlexi\Api\Server\DataExportApi`)
- Main API controller handling export requests
- Integrates with security manager and notification system
- Provides RESTful endpoints for data export operations

#### 2. UserDataExporter (`MultiFlexi\DataExport\UserDataExporter`)
- Core data collection engine
- Gathers personal data from all relevant database tables
- Formats data for export in structured format
- Excludes sensitive credentials (passwords, secrets) for security

#### 3. DataExportSecurityManager (`MultiFlexi\Security\DataExportSecurityManager`)
- Handles authentication and authorization
- Implements rate limiting (5 requests per hour per user/IP)
- Manages secure download tokens with 1-hour expiry
- Detects and prevents suspicious activity
- Maintains comprehensive audit logs

#### 4. DataExportNotifier (`MultiFlexi\Email\DataExportNotifier`)
- Sends GDPR-compliant email notifications
- Notifies users when export is ready for download
- Confirms successful downloads
- Sends security alerts for unusual activity

#### 5. DataExportWidget (`MultiFlexi\Ui\DataExportWidget`)
- User-friendly dashboard widget
- Provides export options and status information
- Shows recent export history
- Integrates with existing UI framework

### Database Schema

The implementation adds three new database tables:

#### `data_export_tokens`
Stores secure download tokens with the following fields:
- `user_id`: User who requested the export
- `token_hash`: SHA256 hash of the secure token
- `format`: Export format (json/pdf)
- `ip_address`: IP address of the requester
- `user_agent`: User agent string
- `expires_at`: Token expiration timestamp
- `used_at`: When token was used (null if unused)
- `DatCreate`: Token creation timestamp

#### `data_export_rate_limits`
Tracks rate limiting per user/IP combination:
- `user_id`: User ID
- `ip_address`: IP address
- `request_count`: Number of requests in current window
- `window_start`: Rate limit window start time
- `window_end`: Rate limit window end time
- `DatCreate`: First request in window
- `DatSave`: Last request in window

#### `gdpr_audit_log`
Comprehensive GDPR audit trail:
- `user_id`: User ID (if applicable)
- `action`: GDPR action performed
- `article`: GDPR Article reference
- `data_subject`: Data subject identifier
- `legal_basis`: Legal basis for processing
- `details`: Additional details as JSON
- `ip_address`: IP address
- `user_agent`: User agent string
- `result`: Action result (success/failure/partial)
- `error_message`: Error message if failed
- `DatCreate`: Action timestamp

## Data Categories Exported

The system exports the following categories of personal data:

### 1. Export Metadata
- Export timestamp and version
- GDPR article reference
- Data controller information
- Contact information for data protection officer

### 2. User Profile Data
- User ID and login credentials (username only)
- Email address
- First and last name
- Account status and settings
- Account creation and modification dates

### 3. Company Associations
- Companies the user has access to
- User roles and permissions
- Association timestamps and relationship types

### 4. Credential Metadata
- Names and types of stored credentials
- Associated companies
- **Note**: Actual passwords/secrets are never included for security

### 5. Activity Logs
- System activity logs and audit trails
- Application usage history
- Job execution history
- Login/logout events

### 6. Consent Records
- All consent decisions (granted/denied)
- Consent withdrawal history
- Policy versions and timestamps
- IP addresses where consent was given

### 7. Session History
- Current session information
- **Note**: Full session history may be limited based on system configuration

### 8. Audit Trails
- Data export request history
- Security events and alerts
- System access logs

## Security Measures

### Authentication & Authorization
- Users must be authenticated to request exports
- Users can only export their own data
- Session-based verification for all operations

### Rate Limiting
- Maximum 5 export requests per hour per user/IP combination
- Sliding window implementation
- Automatic cleanup of expired rate limit records

### Secure Token System
- Cryptographically secure tokens for download links
- SHA256 hashing with server-side salt
- 1-hour token expiry
- Single-use tokens (cannot be reused)
- IP address validation

### Suspicious Activity Detection
- Monitors for unusual patterns (multiple IPs, rapid requests)
- Automatic blocking of suspicious activity
- Email alerts for security events
- Comprehensive audit logging

### Data Protection
- Sensitive credentials (passwords, API keys) are never exported
- All operations are logged for audit purposes
- Secure deletion of expired tokens
- Regular cleanup of audit logs (configurable retention)

## User Interface

### Dashboard Widget
- Accessible from main dashboard
- Two export format options (JSON/Text)
- Real-time status updates
- Recent export request history
- GDPR compliance information

### Dedicated Export Page
- Comprehensive GDPR rights information
- Detailed export process explanation
- Contact information for data protection officer
- Educational content about user rights

### Navigation Integration
- Added to Privacy dropdown menu in main navigation
- Consistent with existing GDPR/privacy features
- Intuitive user experience

## API Endpoints

### POST/GET `/api/data-export.php?action=export`
Request a new data export
- **Parameters**: `format` (json|pdf)
- **Response**: Download URL and expiration info
- **Security**: Authenticated users only, rate limited

### GET `/api/data-export.php?action=status`
Get export request status
- **Response**: Recent export request history
- **Security**: Authenticated users only

### GET `/data-export.php?token={token}`
Download exported data
- **Parameters**: `token` (secure download token)
- **Response**: File download
- **Security**: Token validation, single-use, IP verification

## Email Notifications

### Export Ready Notification
Sent when export is prepared:
- Download link with expiration time
- Security information and instructions
- GDPR compliance details
- What's included in the export

### Download Confirmation
Sent after successful download:
- Confirmation of download completion
- Security reminders
- Audit trail notation

### Security Alerts
Sent for unusual activity:
- Alert type and details
- Recommended actions
- Automatic security measures applied

## Installation & Setup

### 1. Database Migration
Run the database migration to create required tables:
```bash
cd /home/vitex/Projects/Multi/multiflexi-database
# Run the migration (adjust command based on your setup)
./vendor/bin/phinx migrate
```

### 2. File Deployment
Copy the following new files to your MultiFlexi installation:
- `src/MultiFlexi/Api/Server/DataExportApi.php`
- `src/MultiFlexi/DataExport/UserDataExporter.php`
- `src/MultiFlexi/Security/DataExportSecurityManager.php`
- `src/MultiFlexi/Email/DataExportNotifier.php`
- `src/MultiFlexi/Ui/DataExportWidget.php`
- `src/api/data-export.php`
- `src/data-export.php`
- `src/data-export-page.php`

### 3. Configuration
Configure email settings in your environment:
```env
MAIL_FROM=noreply@your-domain.com
MAIL_FROM_NAME=Your Organization GDPR System
```

### 4. Menu Integration
The main navigation menu is automatically updated to include the data export option in the Privacy dropdown.

### 5. Validation
Run the validation script to ensure proper installation:
```bash
cd /home/vitex/Projects/Multi/MultiFlexi
php tools/validate-gdpr-implementation.php
```

## Usage

### For End Users
1. Log into MultiFlexi
2. Navigate to Privacy → Export My Data
3. Choose export format (JSON or Text)
4. Click export button
5. Check email for download notification
6. Click secure download link within 1 hour
7. Download and review your personal data

### For Administrators
- Monitor GDPR audit logs in the `gdpr_audit_log` table
- Review data export requests in system logs
- Configure retention policies for audit data
- Ensure email notifications are working properly
- Run regular cleanup of expired tokens:
  ```php
  $securityManager = new \MultiFlexi\Security\DataExportSecurityManager();
  $stats = $securityManager->cleanupExpiredRecords();
  ```

## Compliance Notes

### GDPR Article 15 Requirements ✅
- **Right to confirmation**: Users can confirm what data is processed
- **Right to access**: Complete data export provided
- **Right to copies**: Downloadable copies in multiple formats
- **Structured format**: JSON provides machine-readable structure
- **Free of charge**: No cost to users for basic exports
- **Without undue delay**: Automated, immediate processing
- **Within one month**: Immediate processing exceeds requirement

### Data Protection Principles
- **Lawfulness**: Based on user consent (Article 6(1)(a))
- **Purpose limitation**: Only exports user's own data
- **Data minimization**: No unnecessary data included
- **Accuracy**: Real-time data from authoritative sources
- **Storage limitation**: Download tokens expire after 1 hour
- **Security**: Multiple security layers implemented
- **Accountability**: Comprehensive audit trails maintained

### Additional Considerations
- Regular review of exported data categories
- Training for staff on GDPR compliance
- Incident response procedures for security breaches
- Regular testing of the export functionality
- Documentation updates as system evolves

## Maintenance

### Regular Tasks
- Run cleanup script monthly to remove expired tokens and rate limits
- Monitor audit logs for unusual activity
- Review and update data categories as system changes
- Test email notification delivery
- Validate GDPR compliance with legal team

### Performance Considerations
- Large user bases may require background processing for exports
- Consider archiving old audit logs to maintain performance
- Monitor database growth from audit tables
- Implement additional caching if needed for frequent exports

### Security Updates
- Regularly review security measures
- Update rate limiting rules based on abuse patterns
- Monitor for new security vulnerabilities
- Keep audit trails for compliance requirements (typically 3-7 years)

## Support

For issues with the GDPR implementation:
1. Check validation script output for common issues
2. Review audit logs for error details
3. Verify database migrations completed successfully
4. Ensure proper file permissions for email functionality
5. Contact system administrator or data protection officer

## Legal Disclaimer

This implementation is designed to comply with GDPR Article 15 requirements as understood at the time of development. Organizations should:
- Consult with qualified legal counsel
- Conduct regular compliance assessments
- Stay updated on regulatory changes
- Maintain documentation of compliance efforts
- Implement additional measures as needed for specific jurisdictions

The implementation should be reviewed and validated by legal and compliance teams before production deployment.