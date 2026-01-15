RunTemplate
===========

.. toctree::
   :maxdepth: 2

.. contents::

Overview
--------

The `RunTemplate` is a key component of the MultiFlexi application that defines how, when, and where applications should be executed. It serves as a configuration blueprint that combines an application with specific execution parameters, scheduling, and environment settings.

**Key Features**:

- Application execution configuration
- Scheduling and frequency management
- Environment variable definition
- Success and failure action configuration  
- Rich text note editing with WYSIWYG editor
- Company and server assignment
- Credential management integration

Core Components
---------------

Name and Identification
~~~~~~~~~~~~~~~~~~~~~~~

Each RunTemplate has a unique name that helps identify its purpose and scope. The name should be descriptive and reflect the specific use case or business process it represents.

Application Assignment
~~~~~~~~~~~~~~~~~~~~~

RunTemplates are linked to specific applications from the MultiFlexi application catalog. This defines what code will be executed when the template runs.

Company and Server Context
~~~~~~~~~~~~~~~~~~~~~~~~~~

RunTemplates are associated with:

- **Company**: The business entity or customer for which the template will execute
- **Server**: The target server environment where the application will run
- **Credentials**: Authentication and connection information required by the application

Scheduling Configuration
~~~~~~~~~~~~~~~~~~~~~~~

RunTemplates support flexible scheduling options:

- **Frequency**: How often the template should execute (hourly, daily, weekly, monthly, etc.)
- **Cron Expressions**: Advanced scheduling using standard cron syntax
- **Manual Execution**: On-demand execution without scheduled intervals
- **Conditional Execution**: Execution based on specific triggers or events

Environment Variables
~~~~~~~~~~~~~~~~~~~

RunTemplates can define custom environment variables that will be available to the application during execution:

- Application-specific configuration values
- API endpoints and connection strings  
- Feature flags and operational parameters
- Debugging and logging levels

Documentation and Notes
~~~~~~~~~~~~~~~~~~~~~~

**Rich Text Notes** *(New in v2.1.1)*:

RunTemplates now include a powerful WYSIWYG note editor powered by Summernote, allowing you to:

- Create rich-formatted documentation for the template
- Include step-by-step procedures and troubleshooting guides
- Add links, images, and formatted text
- Maintain execution logs and historical information
- Document business requirements and technical specifications

The note editor supports:

- **Formatting**: Bold, italic, underline, colors, fonts
- **Structure**: Headers, lists, tables, code blocks
- **Media**: Images, links, attachments
- **Code**: Syntax highlighting for configuration examples
- **History**: Version tracking of note changes

Actions Configuration
--------------------

RunTemplates can be configured with actions that execute when specific events occur during job execution. Actions provide integration with external systems and services.

**Available Action Types**:

- **Success Actions**: Execute when jobs complete successfully
- **Fail Actions**: Execute when jobs fail or encounter errors

**Configuring Actions**:

1. Navigate to your RunTemplate in the web interface
2. Click "Configure Actions"  
3. Select the desired action type (e.g., ToDo, Email, etc.)
4. Configure the action parameters and credentials
5. Save the configuration

For detailed information about available actions and their configuration, see the :doc:`actions` section.

Creating RunTemplates
---------------------

Web Interface
~~~~~~~~~~~~

1. **Navigate to RunTemplates**: Access the RunTemplate management section from the main dashboard
2. **Create New Template**: Click "New RunTemplate" or similar creation button
3. **Basic Configuration**:
   
   - Enter a descriptive name for the template
   - Select the target application from the catalog
   - Choose the company/customer context
   - Assign the execution server

4. **Environment Setup**:
   
   - Define required environment variables
   - Configure application-specific parameters
   - Set credential references for secure authentication

5. **Scheduling Configuration**:
   
   - Set execution frequency (manual, scheduled, or triggered)
   - Configure cron expressions for advanced scheduling
   - Define execution windows and constraints

6. **Documentation**:
   
   - Use the rich text editor to add comprehensive notes
   - Document the purpose, requirements, and procedures
   - Include troubleshooting information and contact details

7. **Action Configuration**:
   
   - Configure success actions (notifications, follow-up tasks)
   - Set up failure actions (alerts, escalation procedures)
   - Test action configurations

Command Line Interface
~~~~~~~~~~~~~~~~~~~~~

RunTemplates can also be managed via CLI tools:

.. code-block:: bash

   # List existing RunTemplates
   multiflexi-run-template --list
   
   # Create new RunTemplate from JSON
   multiflexi-run-template --create template.json
   
   # Execute RunTemplate manually
   multiflexi-run-template --execute template_id

Best Practices
-------------

Naming Conventions
~~~~~~~~~~~~~~~~~

Use clear, descriptive names that include:

- Business process or function
- Target environment (prod, test, dev)
- Frequency or trigger information
- Company or department context

**Examples**:

- ``ACME_Corp_Daily_Invoice_Processing_PROD``
- ``Monthly_Financial_Reports_TestCompany``
- ``Hourly_Data_Sync_CustomerXYZ_DEV``

Environment Management
~~~~~~~~~~~~~~~~~~~~~

**Security**: 

- Use credential references instead of hardcoded passwords
- Implement least-privilege access principles
- Regular credential rotation and audit
- Secure storage of sensitive configuration data

**Organization**:

- Group related environment variables logically
- Use consistent naming conventions for variables
- Document the purpose and format of each variable
- Implement validation for critical parameters

Documentation Standards
~~~~~~~~~~~~~~~~~~~~~~

**Comprehensive Notes**:

- Document the business purpose and expected outcomes
- Include step-by-step execution procedures
- List all dependencies and prerequisites
- Provide troubleshooting guides and common solutions
- Maintain change history and version information

**Formatting Best Practices**:

- Use headers to organize information clearly
- Employ bullet points and numbered lists for procedures
- Include code examples and configuration snippets
- Add visual elements (tables, highlighted text) for clarity
- Link to related documentation and resources

Monitoring and Maintenance
~~~~~~~~~~~~~~~~~~~~~~~~~

**Regular Reviews**:

- Periodically review and update RunTemplate configurations
- Verify that scheduled executions are performing as expected
- Update documentation to reflect process changes
- Review and optimize performance settings

**Health Monitoring**:

- Monitor execution success rates and performance metrics
- Set up appropriate alerting for failures and anomalies
- Implement logging and audit trails
- Regular backup of RunTemplate configurations

Troubleshooting
---------------

Common Issues
~~~~~~~~~~~~

**Execution Failures**:

- Verify credential validity and permissions
- Check environment variable configuration
- Confirm application dependencies are available
- Review server resource availability and capacity

**Scheduling Problems**:

- Validate cron expression syntax
- Check for timezone configuration issues
- Verify scheduler service status and health
- Review execution window conflicts

**Action Configuration**:

- Test action configurations in isolation
- Verify external system connectivity and credentials
- Check action parameter formatting and validation
- Review action execution logs for detailed error information

Debugging Tools
~~~~~~~~~~~~~~

**Logging**:

- Enable detailed logging for troubleshooting
- Review execution logs for error patterns
- Monitor system resource usage during execution
- Analyze timing and performance metrics

**Testing**:

- Use manual execution for testing changes
- Implement dry-run modes where available
- Test in non-production environments first
- Validate action configurations separately

Integration Examples
-------------------

Business Process Automation
~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Financial Reporting**:

.. code-block:: yaml

   name: Monthly_Financial_Reports_ACME
   application: financial-report-generator
   company: ACME Corporation
   schedule: "0 9 1 * *"  # 9 AM on first day of month
   environment:
     REPORT_TYPE: monthly
     OUTPUT_FORMAT: pdf
     EMAIL_RECIPIENTS: finance@acme.com
   success_actions:
     - type: email
       recipients: [finance@acme.com, management@acme.com]
       subject: "Monthly Financial Reports Ready"
   fail_actions:
     - type: email
       recipients: [it-support@acme.com]
       subject: "ALERT: Financial Report Generation Failed"

**Data Processing Pipeline**:

.. code-block:: yaml

   name: Hourly_Data_Sync_CustomerDB
   application: data-synchronizer
   company: Customer Database Inc
   schedule: "0 * * * *"  # Every hour
   environment:
     SOURCE_DB: customer_prod
     TARGET_DB: analytics_warehouse
     BATCH_SIZE: 1000
   success_actions:
     - type: webhook
       url: https://monitoring.company.com/sync-complete
   fail_actions:
     - type: slack
       channel: "#data-alerts"
       message: "Data sync failed for Customer Database"

Advanced Configuration
---------------------

Conditional Execution
~~~~~~~~~~~~~~~~~~~~~

RunTemplates can be configured with conditional logic:

- **Dependency Chains**: Execute only after other templates complete successfully
- **Business Rules**: Execute based on data conditions or external triggers
- **Resource Availability**: Execute when system resources meet requirements
- **Time-based Conditions**: Execute only during specific time windows or business hours

Multi-Environment Management
~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Environment Promotion**:

- Develop and test RunTemplates in development environments
- Use consistent naming and configuration patterns across environments
- Implement automated promotion workflows
- Maintain environment-specific variable configurations

**Configuration Management**:

- Version control RunTemplate configurations
- Implement change approval workflows
- Track configuration drift and compliance
- Automate configuration backup and recovery

For comprehensive information about configuring actions within RunTemplates, see the :doc:`actions` section.

