.. _configuration:

Configuration
=============

.. toctree::
   :maxdepth: 2

.. contents::
   :local:

MultiFlexi offers flexible configuration options ranging from server-level environment variables to granular per-application settings.

System Configuration
--------------------

The core system behavior is controlled via environment variables, typically loaded from ``/etc/multiflexi/multiflexi.env``.

Database Settings
~~~~~~~~~~~~~~~~~

- **DB_CONNECTION**: Database driver (e.g., ``mysql``, ``pgsql``).
- **DB_HOST**: Database host address.
- **DB_PORT**: Database port (default: ``3306``).
- **DB_DATABASE**: Database name.
- **DB_USERNAME**: Database user name.
- **DB_PASSWORD**: Database password.

Security Options
~~~~~~~~~~~~~~~~

- **ENCRYPTION_MASTER_KEY**: Master key used for data encryption (Critical: Back this up).
- **CSRF_PROTECTION_ENABLED**: Enable Cross-Site Request Forgery protection (default: ``true``).
- **BRUTE_FORCE_PROTECTION_ENABLED**: Enable protection against brute force attacks (default: ``true``).
- **BRUTE_FORCE_MAX_ATTEMPTS**: Maximum number of failed attempts allowed (default: ``5``).
- **BRUTE_FORCE_LOCKOUT_DURATION**: Duration in seconds to lock out an IP after max attempts (default: ``900``).
- **BRUTE_FORCE_TIME_WINDOW**: Time window in seconds to count attempts (default: ``300``).
- **BRUTE_FORCE_IP_LIMITING**: Enable IP-based limiting for brute force protection (default: ``true``).
- **SECURITY_LOGGING_ENABLED**: Enable security audit logging (default: ``true``).
- **DATA_ENCRYPTION_ENABLED**: Enable data encryption features (default: ``true``).
- **RATE_LIMITING_ENABLED**: Enable general rate limiting (default: ``true``).
- **IP_WHITELIST_ENABLED**: Enable IP whitelisting (default: ``false``).
- **TWO_FACTOR_AUTH_ENABLED**: Enable Two-Factor Authentication (default: ``true``).
- **RBAC_ENABLED**: Enable Role-Based Access Control (default: ``true``).

Session Management
~~~~~~~~~~~~~~~~~~

- **SESSION_TIMEOUT**: Session timeout in seconds (default: ``3600``).
- **SESSION_REGENERATION_INTERVAL**: Interval in seconds to regenerate session ID (default: ``300``).
- **SESSION_STRICT_USER_AGENT**: Enforce strict User-Agent checking for sessions (default: ``true``).
- **SESSION_STRICT_IP_ADDRESS**: Enforce strict IP address checking for sessions (default: ``false``).

API Limits
~~~~~~~~~~

- **API_DEBUG**: Enable API debug mode (default: ``false``).
- **API_RATE_LIMITING_ENABLED**: Enable API-specific rate limiting (default: ``true``).
- **API_RATE_LIMIT_REQUESTS**: Max API requests per window (default: ``100``).
- **API_RATE_LIMIT_WINDOW**: API rate limit window in seconds (default: ``3600``).

Email & Notifications
~~~~~~~~~~~~~~~~~~~~~

- **EMAIL_FROM**: Default sender address for emails (default: ``multiflexi@<SERVER_NAME>``).
- **SEND_INFO_TO**: Email address to send informational notifications to (default: ``false``).

Logging & Telemetry
~~~~~~~~~~~~~~~~~~~

- **LOG_DIRECTORY**: Directory for log files (default: ``/var/log/multiflexi``).
- **ZABBIX_SERVER**: Zabbix server address for logging to Zabbix.
- **ENABLE_GOOGLE_ANALYTICS**: Enable Google Analytics (default: ``false``).
- **LIVE_OUTPUT_SOCKET**: WebSocket URI for live output (e.g., ``ws://localhost:8080``).

Application Configuration
-------------------------

Each application installed in MultiFlexi defines its own specific configuration fields. These are managed through the web interface.

Configuration Field Types
~~~~~~~~~~~~~~~~~~~~~~~~~

MultiFlexi utilizes a typed configuration system to ensure valid data input:

- **Text**: Standard single-line input.
- **Number**: Numeric input (integer or decimal).
- **Date**: Date picker widget.
- **Email**: Validated email input.
- **Password**: Masked input for sensitive credentials.
- **Checkbox**: Boolean switch (Yes/No).
- **File**: File upload widget.
- **Directory**: Server-side directory path selector.

.. image:: appconfigfieldseditor.png
    :alt: Application Configuration Editor
    :align: center

GDPR & Compliance
-----------------

MultiFlexi includes built-in tools to assist with GDPR compliance.

- **Security Audit**: Logs all access and modification events.
- **Data Encryption**: Encrypts sensitive fields at rest using AES-256.
- **Retention Policies**: Configurable automated data cleanup schedules.
- **Anonymization**: Tools to anonymize personal data after retention periods expire.

Refer to :doc:`gdpr-compliance` for a detailed implementation guide.

OpenTelemetry
-------------

For enterprise observability, MultiFlexi supports OpenTelemetry.

- **Service Name**: Identifier for the MultiFlexi instance.
- **Collector Endpoint**: URL of your OTLP collector.
- **Protocol**: gRPC or HTTP.

See :doc:`opentelemetry` for configuration details.

Logging
-------

Logs are essential for monitoring and troubleshooting.

**Locations:**

- **File**: ``/var/log/multiflexi/multiflexi.log`` (Rotated daily).
- **System**: Syslog / Journald integration.
- **Database**: Viewable via Web UI (latest events).
- **Zabbix**: Real-time error trapping (if configured).

.. tip::
    To watch logs in real-time via CLI:
    
    .. code-block:: bash
    
        tail -f /var/log/multiflexi/multiflexi.log
