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

Importing Credential Types
-------------------------

Credential types can be imported using the CLI command:

.. code-block:: bash

    multiflexi-cli credtype import --file example.credential-type.json

This command reads a credential type definition from the specified JSON file and imports it into the MultiFlexi system. The imported credential type will be available for use in app and integration configurations.

See Also:
---------
- :doc:`multiflexi-cli`
- :doc:`commandline`
