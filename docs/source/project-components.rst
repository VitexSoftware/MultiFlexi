Project Components and Architecture
====================================

This section describes the individual projects that make up the MultiFlexi ecosystem, their purposes, and how developers should work with them.

Core Components
---------------

php-vitexsoftware-multiflexi-core
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Location**: ``~/Projects/Multi/php-vitexsoftware-multiflexi-core``

**Purpose**: Core PHP library providing the fundamental business logic, data models, and shared utilities for the entire MultiFlexi ecosystem.

**Key Features**:
- ORM base classes (Engine, DBEngine)
- Domain models (Company, Application, Credential, Job, RunTemplate)
- Execution & scheduling framework
- Logging integration with multiple targets
- Zabbix monitoring integration

**Main Namespaces**:

.. code-block:: text

    MultiFlexi\                    → src/MultiFlexi/
    MultiFlexi\Env\               → src/MultiFlexi/Env/
    MultiFlexi\Action\            → src/MultiFlexi/Action/
    MultiFlexi\Zabbix\            → src/MultiFlexi/Zabbix/
    MultiFlexi\Executor\          → src/MultiFlexi/Executor/
    MultiFlexi\CredentialType\    → src/MultiFlexi/CredentialType/

**Developer Usage**:

.. code-block:: bash

    # Development setup
    cd ~/Projects/Multi/php-vitexsoftware-multiflexi-core
    composer install
    
    # Run tests
    ./vendor/bin/phpunit
    
    # Code analysis
    ./vendor/bin/phpstan analyse

**Key Classes**:
- ``MultiFlexi\Job`` - Central job execution orchestrator
- ``MultiFlexi\Application`` - Application definition management
- ``MultiFlexi\Company`` - Multi-tenant company handling
- ``MultiFlexi\RunTemplate`` - Job template configuration
- ``MultiFlexi\Executor\Native`` - Native command execution
- ``MultiFlexi\Executor\Docker`` - Docker container execution

multiflexi-database
~~~~~~~~~~~~~~~~~~~

**Location**: ``~/Projects/Multi/multiflexi-database``

**Purpose**: Database schema definitions, migration scripts, and database maintenance tools for MultiFlexi platform.

**Key Features**:
- Phinx-based database migrations
- Multi-database support (MySQL, PostgreSQL, SQLite)
- Schema versioning and rollback capabilities
- Seed data for development and testing

**Structure**:

.. code-block:: text

    db/
    ├── migrations/           # Database migration files
    │   ├── 20160203130652_user.php
    │   ├── 20200413063021_applications.php
    │   └── ...
    └── seeds/               # Test and development data

**Developer Usage**:

.. code-block:: bash

    # Run migrations
    ./vendor/bin/phinx migrate
    
    # Create new migration
    ./vendor/bin/phinx create MigrationName
    
    # Rollback migration
    ./vendor/bin/phinx rollback

**Database Tables** (key entities):
- ``user`` - User accounts and authentication
- ``company`` - Multi-tenant company entities
- ``applications`` - Application registry
- ``job`` - Job execution records
- ``run_template`` - Job execution templates
- ``config_registry`` - Configuration storage

API and Server Components
-------------------------

multiflexi-api
~~~~~~~~~~~~~~

**Location**: ``~/Projects/Multi/multiflexi-api``

**Purpose**: API class generator and server components that generate API server classes and handle routing.

**Key Features**:
- Generates API server classes from OpenAPI specifications
- Handles API routing and middleware
- Supports multiple output formats (JSON, XML, YAML, HTML)

**Developer Usage**:

.. code-block:: bash

    # Generate API classes
    php generate-api.php
    
    # Validate OpenAPI schema
    swagger-codegen validate -i openapi-schema.yaml

multiflexi-server
~~~~~~~~~~~~~~~~~

**Location**: ``~/Projects/Multi/multiflexi-server``

**Purpose**: REST API server implementation built on PHP Slim 4 framework.

**Key Features**:
- RESTful API endpoints
- HTTP Basic authentication
- Request/response handling
- API versioning support

**API Endpoints**:
- ``/apps`` - Application management
- ``/jobs`` - Job management and execution
- ``/companies`` - Multi-tenant company management
- ``/users`` - User management
- ``/credentials`` - Credential management
- ``/runtemplates`` - Job template management

**Developer Usage**:

.. code-block:: bash

    # Start development server
    php -S localhost:8080 -t public/
    
    # Test API endpoints
    curl -X GET "http://localhost:8080/api/apps.json" \
         -H "Authorization: Basic $(echo -n 'user:pass' | base64)"

CLI and Execution Components
----------------------------

multiflexi-cli
~~~~~~~~~~~~~~

**Location**: ``~/Projects/Multi/multiflexi-cli``

**Purpose**: Command-line interface for managing MultiFlexi resources and operations.

**Key Features**:
- Application management (list, create, validate)
- Company and user management
- Job execution and monitoring
- Configuration management

**Common Commands**:

.. code-block:: bash

    # List applications
    multiflexi-cli application list
    
    # Create new company
    multiflexi-cli company create --name "Test Company"
    
    # Validate application JSON
    multiflexi-cli application validate-json --json app.json
    
    # Execute job
    multiflexi-cli job run --template-id 123

multiflexi-executor
~~~~~~~~~~~~~~~~~~~

**Location**: ``~/Projects/Multi/multiflexi-executor``

**Purpose**: Dedicated service for executing jobs and tasks in isolated environments.

**Key Features**:
- Job execution coordination
- Task scheduling
- Environment isolation
- Support for multiple execution backends

**Executor Types**:
- ``Native`` - Direct command execution
- ``Docker`` - Container-based execution
- ``Kubernetes`` - Pod-based execution
- ``Azure`` - Cloud-based execution

**Developer Usage**:

.. code-block:: bash

    # Start executor daemon
    php daemon.php
    
    # Execute single job
    php executor.php --job-id 123

Web Interface Components
------------------------

MultiFlexi (Main Application)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Location**: ``~/Projects/Multi/MultiFlexi``

**Purpose**: Primary web interface providing dashboard, management tools, and user interface.

**Key Features**:
- Bootstrap 4-based responsive UI
- Company management interface
- Job monitoring and control
- Application configuration tools
- User authentication and authorization

**Key Namespaces**:

.. code-block:: text

    MultiFlexi\Ui\     → src/MultiFlexi/Ui/
    MultiFlexi\Api\    → src/MultiFlexi/Api/

**Developer Usage**:

.. code-block:: bash

    # Development server
    php -S localhost:8080
    
    # Access web interface
    http://localhost:8080/MultiFlexi/

Deployment and Infrastructure
-----------------------------

multiflexi-ansible-collection
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Location**: ``~/Projects/Multi/multiflexi-ansible-collection``

**Purpose**: Ansible collection for automated deployment and configuration management of MultiFlexi components.

**Key Features**:
- Infrastructure as Code
- Multi-environment deployment
- Configuration management
- Service orchestration

**Usage**:

.. code-block:: bash

    # Install collection
    ansible-galaxy collection install .
    
    # Deploy MultiFlexi
    ansible-playbook deploy.yml -i inventory

Development Workflow
--------------------

Source Code vs Vendor Dependencies
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Editable Source Code** (Full write access as author):

.. code-block:: text

    ~/Projects/Multi/php-vitexsoftware-multiflexi-core/src/MultiFlexi/
    ~/Projects/Multi/multiflexi-database/
    ~/Projects/Multi/multiflexi-server/
    ~/Projects/Multi/multiflexi-cli/
    ~/Projects/Multi/MultiFlexi/src/MultiFlexi/

**Vendor Dependencies** (Read-only, prefer modifying source):

.. code-block:: text

    vendor/vitexsoftware/ease-core/
    vendor/vitexsoftware/ease-fluentpdo/
    vendor/fpdo/fluentpdo/

Common Development Tasks
~~~~~~~~~~~~~~~~~~~~~~~~

**Adding New Application Support**:

1. Create application JSON definition
2. Validate against schema
3. Add credential types if needed
4. Test execution in different environments

**Extending API**:

1. Update OpenAPI schema in ``multiflexi-api/``
2. Regenerate API classes
3. Implement endpoints in ``multiflexi-server/``
4. Update CLI commands in ``multiflexi-cli/``

**Database Changes**:

1. Create migration in ``multiflexi-database/db/migrations/``
2. Test migration on all supported database types
3. Update related model classes in core library

**Adding New Executor**:

1. Implement executor interface in core library
2. Add executor configuration options
3. Update job execution logic
4. Test in executor service

Testing Strategy
~~~~~~~~~~~~~~~~

Each component has its own testing approach:

- **Unit Tests**: PHPUnit tests in ``tests/`` directories
- **Integration Tests**: Database connectivity and API endpoint testing
- **Validation Tests**: JSON schema validation for applications
- **End-to-End Tests**: Complete workflow testing across components

**Running Tests**:

.. code-block:: bash

    # Run all tests in a component
    ./vendor/bin/phpunit
    
    # Run specific test
    ./vendor/bin/phpunit tests/JobTest.php
    
    # Generate coverage report
    ./vendor/bin/phpunit --coverage-html coverage/