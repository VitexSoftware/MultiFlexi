.. _credential-type-schema:

MultiFlexi Credential Type Schema
=================================

The MultiFlexi Credential Type Schema (`multiflexi.credential-type.schema.json`) defines the structure for credential types used in MultiFlexi. Credential types describe the required fields and metadata for authentication and integration with external systems (e.g., databases, cloud services, APIs).

Schema Location:
----------------

- File: `php-vitexsoftware-multiflexi-core/multiflexi.credential-type.schema.json`

Schema Overview:
----------------

- **uuid**: Universally unique identifier for the credential type.
- **id**: Unique identifier (string) for the credential type.
- **code**: Requirement name code (e.g., AbraFlexi, SQL, Office365).
- **name**: Human-readable name (localized string or object with language keys).
- **description**: Detailed description (localized string or object).
- **fields**: Array of field definitions, each with:
  - **keyword**: Internal reference name (e.g., DATABASE_PASSWORD)
  - **name**: Field name (localized)
  - **type**: Field type (string, password, number, boolean, secret, select)
  - **description**: Field description (localized)
  - **required**: Boolean, whether the field is required
  - **default**: Default value
  - **options**: Array of options (for select fields)

Localization Support:
---------------------

Fields like `name` and `description` can be either a string or an object with language keys (e.g., `{ "en": "Name", "cs": "Jméno" }`). This allows credential types to be presented in multiple languages.

Example Credential Type JSON:
-----------------------------

.. code-block:: json

    {
      "uuid": "123e4567-e89b-12d3-a456-426614174000",
      "id": "sql",
      "code": "SQL",
      "name": { "en": "SQL Database", "cs": "SQL Databáze" },
      "description": { "en": "Credentials for SQL DB", "cs": "Přihlašovací údaje pro SQL DB" },
      "fields": [
        {
          "keyword": "DATABASE_PASSWORD",
          "name": { "en": "Password", "cs": "Heslo" },
          "type": "password",
          "description": { "en": "Database password", "cs": "Heslo do databáze" },
          "required": true
        }
      ]
    }

Managing Credential Types with CLI
----------------------------------

Credential types can be managed using the MultiFlexi CLI with various operations including validation, import, export, and management.

**CLI Commands:**

.. code-block:: bash

    # List all credential types
    multiflexi-cli credtype list
    
    # Get specific credential type details
    multiflexi-cli credtype get --id=1
    multiflexi-cli credtype get --uuid="123e4567-e89b-12d3-a456-426614174000"
    
    # Validate credential type JSON before import
    multiflexi-cli credtype validate-json --file example.credential-type.json
    
    # Import credential type from JSON file
    multiflexi-cli credtype import-json --file example.credential-type.json
    multiflexi-cli credtype import-json --file example.credential-type.json --format json
    
    # Export credential type to JSON file
    multiflexi-cli credtype export-json --id=1 --file exported-credtype.json
    
    # Update existing credential type
    multiflexi-cli credtype update --id=1 --name="Updated Name"

**Import Features:**

- **Schema Validation**: All JSON files are automatically validated against the MultiFlexi credential type schema before import
- **Duplicate Detection**: The system prevents importing credential types with existing UUIDs
- **Localization Support**: Full support for multi-language names and descriptions
- **Field Definition Import**: Automatically creates field definitions with proper types, validation, and requirements
- **Error Reporting**: Comprehensive error messages for validation failures and import issues
- **Output Formats**: Support for both human-readable text and JSON output formats

**Validation Process:**

Before importing, you can validate your credential type JSON:

.. code-block:: bash

    multiflexi-cli credtype validate-json --file new-credtype.json

This command will check:

- JSON syntax and structure
- Compliance with MultiFlexi credential type schema
- Required field presence and format
- Field type validity and constraints
- UUID format and uniqueness

**Standalone Import Script:**

For direct import operations, you can also use the standalone script:

.. code-block:: bash

    php /path/to/MultiFlexi/lib/json2credential-type.php example.credential-type.json

This script provides:

- Schema validation with detailed error reporting
- WYSIWYG-style output showing what will be imported
- Duplicate detection and prevention
- Comprehensive success and error messages

See Also:
---------
- :doc:`multiflexi-cli`
- :doc:`commandline`
