.. _credential-management:

MultiFlexi Credential Management System
=======================================

MultiFlexi implements a sophisticated three-tier credential management architecture that provides secure, flexible, and reusable authentication handling across the entire platform. This system separates credential definitions from their implementations and usage.

Three-Tier Architecture
------------------------

The credential management system follows a three-tier relationship pattern:

**CredentialPrototype → CredentialType → Credential**

.. code-block:: text

    CredentialPrototype (Template)
           ↓
    CredentialType (Company Instance) 
           ↓
    Credential (Application Usage)

1. **CredentialPrototype** (JSON-based Templates)
   - **Purpose**: Defines the schema and structure for credential types
   - **Storage**: Database tables: `credential_prototype` and `credential_prototype_field`
   - **Format**: JSON-based templates with field definitions, validation rules, and metadata
   - **Management**: Via `crprototype` CLI commands
   - **Scope**: Global templates available across all companies
   - **Reusability**: One prototype can be instantiated multiple times for different companies

2. **CredentialType** (PHP-based Company Instances)
   - **Purpose**: Company-specific implementations of credential prototypes
   - **Storage**: Database table: `credential_type` with UUID support
   - **Implementation**: PHP classes implementing `CredentialTypeInterface`
   - **Management**: Via `credtype` CLI commands
   - **Scope**: Company-specific instances
   - **Relationship**: Each CredentialType references a CredentialPrototype

3. **Credential** (Application Usage)
   - **Purpose**: Actual credential values used by applications and jobs
   - **Storage**: Database table: `credentials` with encrypted sensitive data
   - **Usage**: Referenced by RunTemplates and Job executions
   - **Management**: Via web interface and CLI
   - **Scope**: Specific credential instances for actual use
   - **Relationship**: Each Credential references a CredentialType

Example Workflow
-----------------

.. code-block:: text

    1. Administrator creates AbraFlexi CredentialPrototype (JSON template)
       └── Defines: server, username, password, company fields
    
    2. Company A creates CredentialType instance from AbraFlexi prototype  
       └── Implements: PHP class with company-specific logic
    
    3. Job executor uses Credential based on Company A's CredentialType
       └── Contains: actual server URL, username, password for Company A

Benefits of Three-Tier System
------------------------------

**Template Reusability**
  - CredentialPrototypes serve as reusable templates
  - Standardized field definitions across companies
  - Consistent validation and schema enforcement

**Company Isolation**
  - Each company has its own CredentialType instances
  - Company-specific customizations and business logic
  - Secure multi-tenant credential management

**Flexible Implementation**
  - JSON-based templates for easy creation and modification
  - PHP-based instances for complex business logic
  - Clear separation of concerns between definition and implementation

Credential Type Schema
======================

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
    
    # Create new credential type instance
    multiflexi-cli credtype create --company-id 1 --class AbraFlexi
    
    # Delete credential type
    multiflexi-cli credtype delete --id=1
    
    # Validate credential type JSON before import
    multiflexi-cli credtype validate-json --file example.credential-type.json
    
    # Import credential type from JSON file
    multiflexi-cli credtype import-json --file example.credential-type.json
    multiflexi-cli credtype import-json --file example.credential-type.json --format json
    
    # Export credential type to JSON file
    multiflexi-cli credtype export-json --id=1 --file exported-credtype.json
    
    # Update existing credential type
    multiflexi-cli credtype update --id=1 --name="Updated Name"

Managing Credential Prototypes with CLI
---------------------------------------

Credential prototypes (templates) are managed separately using specialized commands:

**Credential Prototype CLI Commands:**

.. code-block:: bash

    # List all credential prototypes
    multiflexi-cli crprototype list
    
    # Get specific credential prototype details
    multiflexi-cli crprototype get --id=1
    
    # Create new credential prototype from JSON
    multiflexi-cli crprototype create --file template.json
    
    # Update existing credential prototype
    multiflexi-cli crprototype update --id=1 --file updated.json
    
    # Delete credential prototype
    multiflexi-cli crprototype delete --id=1
    
    # Validate prototype JSON before import
    multiflexi-cli crprototype validate-json --file template.json
    
    # Export prototype to JSON file
    multiflexi-cli crprototype export-json --id=1 --file exported-template.json

Development Workflow Example
----------------------------

Here's a complete example of the credential management workflow:

**Step 1: Create Credential Prototype (Template)**

.. code-block:: bash

    # Create AbraFlexi credential prototype template
    cat > abraflexi-prototype.json << EOF
    {
        "name": "AbraFlexi Connection",
        "description": "Standard AbraFlexi server connection template",
        "fields": [
            {
                "keyword": "SERVER_URL",
                "name": "Server URL",
                "type": "string",
                "description": "AbraFlexi server URL",
                "required": true
            },
            {
                "keyword": "USERNAME", 
                "name": "Username",
                "type": "string",
                "description": "AbraFlexi username",
                "required": true
            },
            {
                "keyword": "PASSWORD",
                "name": "Password", 
                "type": "password",
                "description": "AbraFlexi password",
                "required": true
            },
            {
                "keyword": "COMPANY",
                "name": "Company",
                "type": "string", 
                "description": "AbraFlexi company code",
                "required": true
            }
        ]
    }
    EOF
    
    # Import the prototype
    multiflexi-cli crprototype create --file abraflexi-prototype.json

**Step 2: Create Company-Specific CredentialType**

.. code-block:: bash

    # Create credential type instance for Company ID 1
    multiflexi-cli credtype create --company-id 1 --class AbraFlexi
    
    # Verify creation
    multiflexi-cli credtype list

**Step 3: Use in Applications**

Once the CredentialType is created, it becomes available for selection in:

- Web interface credential management
- RunTemplate configuration
- Job execution setup
- Application configuration

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

Database Schema and Relationships
----------------------------------

The three-tier credential system uses the following database tables:

**credential_prototype Table**
  - Stores JSON-based credential templates
  - Contains prototype metadata and field definitions
  - Global scope across all companies

**credential_prototype_field Table**
  - Stores individual field definitions for each prototype
  - Links to credential_prototype via foreign key
  - Contains field validation rules and types

**credential_type Table**
  - Stores company-specific credential type instances
  - References credential_prototype as template
  - Contains PHP class implementation details
  - Company-scoped with UUID support

**credentials Table**
  - Stores actual credential values for use by applications
  - References credential_type for schema and validation
  - Contains encrypted sensitive field values
  - Links to specific companies and applications

**Entity Relationships:**

.. code-block:: text

    credential_prototype (1) ←→ (∞) credential_prototype_field
            ↓ (1)
            ↓
    credential_type (∞) ←→ (1) company
            ↓ (1)
            ↓ 
    credentials (∞) ←→ (1) runtemplate
                   ←→ (1) application

Security Features
------------------

The credential management system implements several security features:

**Encryption at Rest**
  - Sensitive credential fields are encrypted in the database
  - Passwords and API keys are never stored in plain text
  - Encryption keys are managed separately from credential data

**Multi-Tenant Isolation**
  - Credential types are company-scoped
  - Companies cannot access each other's credential instances  
  - Database-level access controls enforce isolation

**Access Control**
  - Role-based permissions for credential management
  - API token authentication for programmatic access
  - Audit logging for all credential operations

**Validation and Sanitization**
  - JSON schema validation for all imports
  - Field type enforcement and sanitization
  - Required field validation before credential usage

See Also:
---------
- :doc:`multiflexi-cli`
- :doc:`commandline`
