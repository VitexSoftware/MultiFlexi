.. _configuration:

Configuration
=============

.. toctree::
   :maxdepth: 2

.. contents::

Each application in MultiFlexi defines its own configuration fields. This is of a specific type and has additional properties.

Configuration Fields
--------------------

- Configuration ``Field Type`` - one of the following:
    - **Text**: A single line text input.
    - **Number**: A numeric input.
    - **Date**: A date input.
    - **Email**: An email input.
    - **Password**: A password input.
    - **Checkbox**: A yes/no checkbox.
    - **File**: A file upload input.
    - **Directory**: A directory path input.
- ``Keyword`` - The name of configuration field (capital letters)
- ``Default Value`` - (used unless otherwise specified)
- Configuration ``Field Description``
- ``required`` yes/no

.. image:: appconfigfieldseditor.png
    :alt: Configuration fields of an application in an editor


JSON Configuration
------------------

To configure the MultiFlexi project, you can also create a configuration file (e.g., `config.yaml`) with the necessary fields. Below is an example configuration file:

GDPR Configuration
------------------

MultiFlexi includes comprehensive GDPR compliance configuration options:

**Security Configuration (Phase 3)**

.. code-block:: bash

   # Security settings
   SECURITY_AUDIT_ENABLED=true           # Enable comprehensive security event logging
   DATA_ENCRYPTION_ENABLED=true          # Enable AES-256 data encryption
   RATE_LIMITING_ENABLED=true            # Enable API rate limiting
   IP_WHITELIST_ENABLED=false            # Enable IP whitelisting for admin access
   ENCRYPTION_MASTER_KEY=<secret_key>    # Master encryption key (required for data encryption)

**Data Retention Configuration (Phase 4)**

.. code-block:: bash

   # Data retention and cleanup settings
   DATA_RETENTION_ENABLED=true                    # Enable automated data retention and cleanup
   RETENTION_GRACE_PERIOD_DAYS=30                 # Default grace period before final deletion
   RETENTION_ARCHIVE_PATH=/var/lib/multiflexi/archives  # Path for archived data storage
   RETENTION_CLEANUP_SCHEDULE="0 2 * * *"         # Cron expression for automated cleanup

**Environment Variable Configuration Types**

When defining GDPR-related configuration fields in application JSON:

.. code-block:: json

   {
       "environment": {
           "GDPR_LAWFUL_BASIS": {
               "type": "set",
               "description": "GDPR lawful basis for processing",
               "options": ["consent", "contract", "legal_obligation", "vital_interests", "public_task", "legitimate_interests"],
               "defval": "legitimate_interests",
               "required": true
           },
           "DATA_RETENTION_PERIOD": {
               "type": "integer",
               "description": "Data retention period in days",
               "defval": "365",
               "required": false
           },
           "PRIVACY_NOTICE_URL": {
               "type": "url",
               "description": "URL to privacy notice",
               "required": false
           }
       }
   }

For complete GDPR implementation details, see :doc:`gdpr-compliance`.

