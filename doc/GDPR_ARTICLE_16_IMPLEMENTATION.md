# GDPR Article 16 - Right of Rectification Implementation

This document describes the implementation of GDPR Article 16 (Right of Rectification) in MultiFlexi, allowing users to update and correct their personal data.

## Overview

Article 16 of the GDPR grants data subjects the right to obtain from the controller the rectification of inaccurate personal data concerning them. This implementation provides:

- **User-friendly interface** for requesting data corrections
- **Approval workflow** for sensitive data changes  
- **Comprehensive audit logging** of all data modifications
- **Email notifications** for request status updates
- **Administrative interface** for reviewing requests

## Architecture

### Core Components

1. **UserDataAuditLogger** (`MultiFlexi\Audit\UserDataAuditLogger`)
   - Logs all personal data modifications
   - Tracks who made changes, when, and why
   - Provides audit statistics and reporting

2. **UserDataCorrectionRequest** (`MultiFlexi\GDPR\UserDataCorrectionRequest`)
   - Manages correction request lifecycle
   - Handles approval/rejection workflow
   - Integrates with audit logging

3. **UserDataCorrectionForm** (`MultiFlexi\Ui\UserDataCorrectionForm`)
   - Enhanced form with GDPR compliance features
   - Validation and approval workflow handling
   - Displays pending request status

4. **DataCorrectionNotifier** (`MultiFlexi\Notifications\DataCorrectionNotifier`)
   - Email notifications for users and administrators
   - Daily digest of pending requests
   - Status change notifications

### Database Schema

#### User Data Audit Table (`user_data_audit`)
```sql
CREATE TABLE `user_data_audit` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `field_name` varchar(100) NOT NULL,
    `old_value` text,
    `new_value` text,
    `change_type` enum('direct','pending_approval','approved','rejected'),
    `changed_by_user_id` int(11) NULL,
    `ip_address` varchar(45) NULL,
    `user_agent` text NULL,
    `reason` text NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE
);
```

#### Data Correction Requests Table (`user_data_correction_requests`)
```sql
CREATE TABLE `user_data_correction_requests` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `field_name` varchar(100) NOT NULL,
    `current_value` text,
    `requested_value` text,
    `justification` text,
    `status` enum('pending','approved','rejected','cancelled'),
    `requested_by_ip` varchar(45) NULL,
    `requested_by_user_agent` text NULL,
    `reviewed_by_user_id` int(11) NULL,
    `reviewed_at` timestamp NULL,
    `reviewer_notes` text NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE
);
```

## Installation

1. **Run the migration script:**
   ```bash
   cd /path/to/multiflexi/src
   php gdpr-article16-migration.php
   ```

2. **Optional: Add sample data for testing:**
   ```bash
   php gdpr-article16-migration.php --with-sample-data
   ```

3. **Configure email settings** in your MultiFlexi configuration for notifications.

## Usage

### For Users

#### Accessing Your Profile
- Navigate to `profile.php` to view and manage your personal data
- View current profile information and data change history
- Submit correction requests for inaccurate data

#### Requesting Data Corrections

1. **Direct Changes** (no approval needed):
   - First Name
   - Last Name

2. **Approval Required** (sensitive fields):
   - Email Address
   - Username (login)

#### Process Flow
1. User submits correction request through profile form
2. System validates the request
3. For sensitive fields: Admin approval required
4. For direct fields: Changes applied immediately
5. User receives email notifications about request status
6. All changes are logged in audit trail

### For Administrators

#### Admin Interface
Access the admin panel at `admin-data-corrections.php` to:
- View pending correction requests
- Review request details and user justifications
- Approve or reject requests with notes
- View statistics and recent activity

#### Daily Operations
- Check for pending requests regularly
- Review justifications for sensitive data changes
- Monitor audit logs for suspicious activity
- Receive daily digest emails of pending requests

## API Usage

### Creating Correction Requests
```php
$correctionRequest = new \MultiFlexi\GDPR\UserDataCorrectionRequest();
$success = $correctionRequest->createRequest(
    $userId,
    'email',  // field name
    'old@example.com',  // current value
    'new@example.com',  // requested value
    'Email address changed due to company merger'  // justification
);
```

### Approving Requests
```php
$success = $correctionRequest->approveRequest(
    $requestId,
    $adminUserId,
    'Approved - documentation provided'
);
```

### Audit Logging
```php
$auditLogger = new \MultiFlexi\Audit\UserDataAuditLogger();
$auditLogger->logDataChange(
    $userId,
    'firstname',
    'Old Name',
    'New Name',
    'direct',
    $changedByUserId
);
```

## Configuration

### Field Classification
Edit `UserDataCorrectionRequest.php` to modify which fields require approval:

```php
// Fields requiring admin approval
public const SENSITIVE_FIELDS = [
    'login' => 'Username',
    'email' => 'Email Address'
];

// Fields that can be changed directly
public const DIRECT_FIELDS = [
    'firstname' => 'First Name',
    'lastname' => 'Last Name'
];
```

### Admin Role Detection
Update the `isAdmin()` method in `UserDataCorrectionForm.php` to match your role system:

```php
private function isAdmin(User $user): bool
{
    return $user->getSettingValue('admin') === true || 
           $user->getDataValue('role') === 'admin';
}
```

### Email Configuration
The notification system uses PHP's built-in `mail()` function. For production, consider integrating with:
- SMTP servers
- Email service providers (SendGrid, Mailgun, etc.)
- Queue systems for reliable delivery

## Security Considerations

### Input Validation
- All user input is validated and sanitized
- Email addresses verified with `filter_var()`
- Names restricted to letters, spaces, and common punctuation
- Usernames limited to alphanumeric and safe characters

### Audit Trail
- Complete logging of all data modifications
- IP addresses and user agents recorded
- Immutable audit log entries
- Administrative actions separately logged

### Access Control
- Users can only modify their own data
- Sensitive fields require admin approval
- Admin interface restricted to authorized users
- Request cancellation limited to original requestor

### Data Protection
- Justifications optional but logged
- Old values preserved for audit purposes
- Secure storage of personal data
- GDPR compliance for data retention

## Monitoring and Reporting

### Audit Reports
```php
$auditLogger = new \MultiFlexi\Audit\UserDataAuditLogger();

// Get statistics for last 30 days
$stats = $auditLogger->getAuditStatistics(
    date('Y-m-d', strtotime('-30 days')),
    date('Y-m-d')
);

// Get user-specific audit log
$userLog = $auditLogger->getUserAuditLog($userId, 50);

// Get pending approvals
$pending = $auditLogger->getPendingApprovals(20);
```

### Email Notifications
- User confirmation when request submitted
- Admin notification for new requests
- User notification when request approved/rejected
- Daily digest for administrators

## Troubleshooting

### Common Issues

1. **Database Connection Errors**
   - Verify database credentials
   - Check table creation permissions
   - Ensure foreign key constraints are satisfied

2. **Email Delivery Problems**
   - Test PHP mail() configuration
   - Check spam filters
   - Verify DNS records for email server

3. **Permission Denied**
   - Check user role configuration
   - Verify admin detection logic
   - Review session management

4. **Form Validation Errors**
   - Check input patterns and requirements
   - Verify JavaScript validation
   - Review server-side validation logic

### Debugging

Enable debug logging by adding to your configuration:
```php
// Enable audit logging debug
define('GDPR_DEBUG', true);

// View detailed error messages
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

## Compliance Features

### GDPR Article 16 Requirements ✓
- **Right to rectification** - Users can request corrections
- **Without undue delay** - Automated processing and notifications  
- **Communicate to recipients** - Audit trail maintains change history
- **Free of charge** - No cost to users for corrections
- **Burden of proof** - Justification field for complex requests

### Additional GDPR Support
- **Article 15** - Data export functionality already exists
- **Audit logging** - Comprehensive change tracking
- **Data minimization** - Only collect necessary justifications
- **Security** - Encrypted storage and secure transmission

## Integration with Existing Features

This implementation works alongside existing MultiFlexi GDPR features:
- Data export system (Article 15)
- Consent management
- Privacy policy pages
- Cookie management

## Future Enhancements

Potential improvements to consider:
- **Bulk corrections** - Multiple field changes in one request
- **Document attachments** - Supporting evidence for requests
- **API endpoints** - REST API for external integrations
- **Advanced workflows** - Multi-step approval processes
- **Data validation** - Integration with external verification services
- **Reporting dashboard** - Analytics and compliance reporting

## Support and Maintenance

### Regular Tasks
- Monitor pending requests
- Review audit logs for anomalies
- Update field classifications as needed
- Test email delivery regularly
- Backup audit data per retention policy

### Updates
When updating the system:
1. Test with sample data first
2. Backup audit and request tables
3. Review any schema changes
4. Update administrator documentation
5. Notify users of new features

---

This implementation provides comprehensive GDPR Article 16 compliance while maintaining security, usability, and administrative oversight. For questions or issues, refer to the MultiFlexi documentation or support channels.