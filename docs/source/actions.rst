Actions
=======

.. contents::

MultiFlexi Actions are modular components that can be executed when specific events occur during job execution. Actions provide a way to extend MultiFlexi's functionality by integrating with external services and systems.

Overview
--------

Actions in MultiFlexi are event-driven components that can be triggered at different points in the job lifecycle:

Action Icons Gallery
~~~~~~~~~~~~~~~~~~~~

MultiFlexi provides a comprehensive set of actions, each with its distinctive icon:

.. list-table:: Available Actions
   :widths: 20 20 60
   :header-rows: 1
   
   * - Icon
     - Action Name
     - Description
   * - .. image:: _static/images/actions/ToDo.svg
          :width: 48px
          :height: 48px
     - **ToDo**
     - Microsoft Office 365 integration for task management
   * - .. image:: _static/images/actions/Reschedule.svg
          :width: 48px
          :height: 48px
     - **Reschedule**
     - Dynamic job scheduling and timing control
   * - .. image:: _static/images/actions/WebHook.svg
          :width: 48px
          :height: 48px
     - **WebHook**
     - HTTP POST requests to external endpoints
   * - .. image:: _static/images/actions/RedmineIssue.svg
          :width: 48px
          :height: 48px
     - **RedmineIssue**
     - Redmine project management integration
   * - .. image:: _static/images/actions/Github.svg
          :width: 48px
          :height: 48px
     - **Github**
     - GitHub repository management and issue creation
   * - .. image:: _static/images/actions/TriggerJenkins.svg
          :width: 48px
          :height: 48px
     - **TriggerJenkins**
     - Jenkins CI/CD pipeline triggering
   * - .. image:: _static/images/actions/LaunchJob.svg
          :width: 48px
          :height: 48px
     - **LaunchJob**
     - MultiFlexi job launching and workflow orchestration
   * - .. image:: _static/images/actions/Stop.svg
          :width: 48px
          :height: 48px
     - **Stop**
     - Process and service termination control
   * - .. image:: _static/images/actions/Zabbix.svg
          :width: 48px
          :height: 48px
     - **Zabbix**
     - Monitoring system integration
   * - .. image:: _static/images/actions/CustomCommand.svg
          :width: 48px
          :height: 48px
     - **CustomCommand**
     - Arbitrary shell command execution
   * - .. image:: _static/images/actions/Sleep.svg
          :width: 48px
          :height: 48px
     - **Sleep**
     - Workflow timing and pause control

Action Lifecycle
~~~~~~~~~~~~~~~~

Actions can be triggered at different points in the job lifecycle:

- **Success Actions**: Executed when a job completes successfully
- **Fail Actions**: Executed when a job fails or encounters an error

Each action is implemented as a PHP class that inherits from ``MultiFlexi\CommonAction`` and provides both a backend execution component and a UI configuration component.

Available Actions
----------------

ToDo Action
~~~~~~~~~~~

.. image:: _static/images/actions/ToDo.svg
   :width: 64px
   :height: 64px
   :align: left
   :alt: ToDo Action Icon

The ToDo Action integrates with Microsoft Office 365 To Do (Microsoft Graph API) to create and manage tasks based on job execution events.

**Features**:

- Create ToDo tasks automatically when jobs succeed or fail
- Support for both delegated and application authentication methods
- Customizable task titles and descriptions with placeholder support
- Configurable task priorities (low, normal, high)
- Integration with multiple Office365 tenants

**Use Cases**:

- Create reminder tasks when important jobs fail
- Track completed data processing operations
- Notify team members about system maintenance completion
- Log business process milestones in team task lists

Configuration
~~~~~~~~~~~~~

The ToDo Action supports two authentication methods:

**Method 1: Username/Password Authentication (Delegated)**

This method uses user credentials and accesses the authenticated user's ToDo lists.

.. code-block:: text

    OFFICE365_USERNAME=user@company.onmicrosoft.com
    OFFICE365_PASSWORD=user_password
    OFFICE365_TENANT=company.onmicrosoft.com

**Method 2: Client ID/Secret Authentication (Application)**

This method uses application credentials and can access any user's ToDo lists with proper permissions.

.. code-block:: text

    OFFICE365_CLIENTID=87731a38-540e-405d-9e1f-e867617dd8fe
    OFFICE365_CLSECRET=8FR8Q~3Rab4-4o7cAd~1vDRId8oYiqEtMJB.Ucb2
    OFFICE365_TENANT=company.onmicrosoft.com
    OFFICE365_USER_ID=5cded639-0b8d-4abc-8976-d202aa1770fa

Required Azure API Permissions
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

For the ToDo Action to work properly, your Azure App Registration must have the appropriate Microsoft Graph API permissions configured.

**For Username/Password Authentication (Delegated Permissions)**:

1. Navigate to **Azure Portal** → **App registrations** → **Your app**
2. Go to **API permissions**
3. Click **Add a permission** → **Microsoft Graph** → **Delegated permissions**
4. Add the following permissions:

   - ``Tasks.ReadWrite`` - Read and write user's tasks
   - ``User.Read`` - Sign in and read user profile

5. Click **Grant admin consent** for your organization

**For Client ID/Secret Authentication (Application Permissions)**:

1. Navigate to **Azure Portal** → **App registrations** → **Your app**
2. Go to **API permissions**
3. Click **Add a permission** → **Microsoft Graph** → **Application permissions**
4. Add the following permissions:

   - ``Tasks.ReadWrite.All`` - Read and write all tasks
   - ``User.Read.All`` - Read all users' profiles

5. **Important**: Click **Grant admin consent** for your organization
6. Ensure your application has a **Client Secret** configured in **Certificates & secrets**

**Azure App Registration Setup**:

1. **Create App Registration**:
   
   - Go to **Azure Portal** → **Azure Active Directory** → **App registrations**
   - Click **New registration**
   - Enter a name for your application
   - Select appropriate supported account types
   - Click **Register**

2. **Configure Client Secret** (for Application authentication):
   
   - In your app registration, go to **Certificates & secrets**
   - Click **New client secret**
   - Add a description and select expiration period
   - Copy the **Value** (not the Secret ID) - this is your ``OFFICE365_CLSECRET``

3. **Get Required IDs**:
   
   - **Application (client) ID**: Found on the app registration Overview page
   - **Directory (tenant) ID**: Found on the app registration Overview page  
   - **User Object ID**: Go to **Azure Active Directory** → **Users** → Select user → Copy **Object ID**

Configuration Parameters
~~~~~~~~~~~~~~~~~~~~~~~

When configuring a ToDo Action in MultiFlexi, you can customize the following parameters:

**Office365 Credential**
  Select the Office365 credential containing authentication information

**ToDo List**
  Choose which ToDo list to create tasks in (automatically populated based on available lists)

**Default Task Priority**
  Set the priority for created tasks:
  
  - ``low`` - Low priority
  - ``normal`` - Normal priority (default)
  - ``high`` - High priority

**Task Subject Template**
  Customize the task title using placeholders:
  
  - ``{job_name}`` - Name of the executed job
  - ``{status}`` - Job execution status (success/failure)
  - ``{company}`` - Company name
  
  Example: ``Task: {job_name} - {status}``

**Task Body Template**
  Customize the task description using placeholders:
  
  - ``{job_name}`` - Name of the executed job
  - ``{status}`` - Job execution status
  - ``{company}`` - Company name  
  - ``{timestamp}`` - Execution timestamp
  
  Example: ``Job: {job_name}\nCompany: {company}\nStatus: {status}\nTime: {timestamp}``

Authentication Methods Comparison
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. list-table:: Authentication Methods
   :header-rows: 1
   :widths: 20 40 40

   * - Aspect
     - Username/Password (Delegated)
     - Client ID/Secret (Application)
   * - **Setup Complexity**
     - Simple - just user credentials
     - Complex - requires Azure app registration
   * - **Security**
     - Requires storing user passwords
     - More secure - no user passwords
   * - **Permissions**
     - Limited to user's own tasks
     - Can access any user's tasks
   * - **API Endpoint**
     - ``/me/todo/lists``
     - ``/users/{userId}/todo/lists``
   * - **Admin Consent**
     - Not required
     - **Required**
   * - **Recommended For**
     - Personal use, development
     - Production, enterprise environments

Troubleshooting
~~~~~~~~~~~~~~

**Common Issues and Solutions**:

1. **"UnknownError" from Microsoft Graph API**
   
   - **Cause**: Missing API permissions or insufficient admin consent
   - **Solution**: Verify Azure app registration has correct permissions and admin consent is granted

2. **"Cannot access user data"**
   
   - **Cause**: Invalid User ID or insufficient permissions
   - **Solution**: Verify the User Object ID is correct and app has ``User.Read.All`` permission

3. **"Invalid client secret detected"**
   
   - **Cause**: Using Client Secret ID instead of the secret value
   - **Solution**: Copy the secret **Value** from Azure Portal, not the ID

4. **"SharePoint tenant detected"**
   
   - **Cause**: Using SharePoint tenant name instead of full domain
   - **Solution**: Use full domain format (e.g., ``company.onmicrosoft.com``)

5. **Authentication failed with username/password**
   
   - **Cause**: Account has MFA enabled or conditional access policies
   - **Solution**: Use Client ID/Secret authentication method instead

**Debug Information**:

MultiFlexi provides detailed debug information when ToDo Action fails:

- API endpoints being called
- Token permission verification results  
- Request IDs for Microsoft support
- Specific error codes and messages

Implementation Details
~~~~~~~~~~~~~~~~~~~~

**PHP Classes**:

- ``MultiFlexi\Action\ToDo`` - Backend action execution logic
- ``MultiFlexi\Ui\Action\ToDo`` - UI configuration interface

**Key Methods**:

- ``getToDoLists()`` - Retrieves available ToDo lists from Microsoft Graph
- ``getAccessToken()`` - Handles OAuth2 token acquisition
- ``verifyUserAccess()`` - Validates user permissions and access

**API Integration**:

The action integrates with Microsoft Graph API endpoints:

- Token endpoint: ``https://login.microsoftonline.com/{tenant}/oauth2/v2.0/token``
- User info: ``https://graph.microsoft.com/v1.0/users/{userId}``
- ToDo lists: ``https://graph.microsoft.com/v1.0/users/{userId}/todo/lists``
- Create task: ``https://graph.microsoft.com/v1.0/users/{userId}/todo/lists/{listId}/tasks``

**Security Considerations**:

- All credentials are stored securely in MultiFlexi's credential system
- OAuth2 tokens are acquired on-demand and not stored persistently  
- Client secrets should be rotated regularly in Azure Portal
- Application permissions require admin consent for security compliance

Extending Actions
----------------

Developers can create custom actions by:

1. Creating a new action class inheriting from ``MultiFlexi\CommonAction``
2. Implementing the ``perform()`` method for execution logic
3. Creating a corresponding UI class inheriting from the action class
4. Implementing the ``inputs()`` method for configuration interface
5. Adding proper credential type support if needed

**Example Action Structure**:

.. code-block:: php

   <?php
   namespace MultiFlexi\Action;

   class MyCustomAction extends \MultiFlexi\CommonAction
   {
       public static function name(): string
       {
           return _('My Custom Action');
       }

       public static function description(): string  
       {
           return _('Description of what this action does');
       }

       public function perform(): bool
       {
           // Implementation logic
           return true;
       }
   }

For more details on action development, see the :doc:`development` section.

Other Available Actions
----------------------

Reschedule Action
~~~~~~~~~~~~~~~~

.. image:: _static/images/actions/Reschedule.svg
   :width: 64px
   :height: 64px
   :align: left
   :alt: Reschedule Action Icon

The Reschedule Action allows you to modify the scheduling of jobs, changing when they will run next. This is useful for dynamic scheduling based on job results, system load, or business requirements.

**Features**:
- Modify job schedules dynamically
- Support for cron expressions and time intervals
- One-time delays or permanent schedule changes
- Integration with MultiFlexi's job scheduler

**Configuration Options**:
- **New Schedule** - Cron expression or time specification for the new schedule
- **Delay Type** - Type of delay (minutes, hours, days, weeks)
- **Delay Amount** - Number of delay units
- **One-time or Recurring** - Whether to reschedule once or change the recurring schedule

**Use Cases**:
- Delay jobs during maintenance windows
- Reschedule failed jobs for retry with backoff
- Adjust frequency based on system performance
- Implement business logic-based scheduling

WebHook Action
~~~~~~~~~~~~~

.. image:: _static/images/actions/WebHook.svg
   :width: 64px
   :height: 64px
   :align: left
   :alt: WebHook Action Icon

The WebHook Action sends HTTP POST requests to external endpoints, enabling integration with third-party systems and services.

**Features**:
- HTTP/HTTPS webhook notifications
- Customizable headers and payloads
- Authentication support (Basic, Bearer token, API key)
- Retry mechanisms with configurable backoff
- SSL certificate validation

**Configuration Options**:
- **URL** - Target webhook endpoint URL
- **HTTP Method** - Request method (POST, PUT, PATCH)
- **Headers** - Custom HTTP headers
- **Payload** - JSON or form data to send
- **Authentication** - Basic auth, API key, or bearer token
- **Timeout** - Request timeout in seconds
- **Retry Policy** - Number of retry attempts and backoff strategy

**Security Features**:
- SSL/TLS certificate validation
- Webhook signature verification
- Configurable user agent strings
- Request/response logging for debugging

**Use Cases**:
- Notify external systems of job completion
- Trigger workflows in other applications
- Send data to analytics platforms
- Integration with chat systems (Slack, Teams, Discord)

RedmineIssue Action
~~~~~~~~~~~~~~~~~~

.. image:: _static/images/actions/RedmineIssue.svg
   :width: 64px
   :height: 64px
   :align: left
   :alt: RedmineIssue Action Icon

The RedmineIssue Action creates and manages issues in Redmine project management systems, perfect for automated issue tracking and project management workflows.

**Features**:
- Automatic issue creation in Redmine
- Customizable issue attributes
- Support for custom fields
- Integration with Redmine projects and trackers
- API key authentication

**Configuration Options**:
- **Redmine URL** - Base URL of your Redmine instance
- **API Key** - Redmine API access key
- **Project** - Target project identifier
- **Tracker** - Issue tracker type
- **Status** - Initial issue status
- **Priority** - Issue priority level
- **Subject** - Issue title/subject
- **Description** - Detailed issue description
- **Assignee** - User to assign the issue to
- **Category** - Issue category
- **Custom Fields** - Project-specific custom field values

**Authentication**:
Requires a valid Redmine API key with permissions to create issues in the target project.

**Use Cases**:
- Automatically create issues for failed jobs
- Generate maintenance tickets
- Track deployment activities
- Create bug reports from monitoring alerts

Github Action
~~~~~~~~~~~~

.. image:: _static/images/actions/Github.svg
   :width: 64px
   :height: 64px
   :align: left
   :alt: Github Action Icon

The Github Action integrates with GitHub repositories to create issues, pull requests, and manage repository activities.

**Features**:
- GitHub Issues and Pull Request creation
- Repository management operations
- Label and milestone assignment
- Markdown-formatted descriptions
- GitHub API v4 (GraphQL) and v3 (REST) support

**Configuration Options**:
- **Repository** - Target GitHub repository (owner/repo format)
- **Authentication Token** - GitHub Personal Access Token or OAuth token
- **Action Type** - Issue creation, PR creation, or repository management
- **Title** - Issue or PR title
- **Body** - Detailed description with Markdown support
- **Labels** - Repository labels to apply
- **Assignees** - Users to assign to the issue/PR
- **Milestone** - Target milestone
- **Branch** - Branch for PR operations

**Required Permissions**:
The GitHub token must have appropriate permissions:
- **Issues: Write** - For creating and managing issues
- **Pull Requests: Write** - For creating PRs
- **Repository: Read** - For repository access
- **Metadata: Read** - For accessing repository metadata

**Use Cases**:
- Create issues for deployment failures
- Generate automated pull requests
- Track feature requests and bugs
- Integrate with development workflows

TriggerJenkins Action
~~~~~~~~~~~~~~~~~~~~

.. image:: _static/images/actions/TriggerJenkins.svg
   :width: 64px
   :height: 64px
   :align: left
   :alt: TriggerJenkins Action Icon

The TriggerJenkins Action starts builds and jobs on Jenkins CI/CD servers, enabling automated pipeline triggering based on MultiFlexi job results.

**Features**:
- Remote Jenkins job triggering
- Build parameter passing
- Job status monitoring
- CSRF token handling
- Multiple authentication methods

**Configuration Options**:
- **Jenkins URL** - Base URL of Jenkins instance
- **Job Name** - Name of the Jenkins job to trigger
- **Authentication** - Username/password or API token
- **Parameters** - Build parameters to pass to Jenkins job
- **Wait for Completion** - Whether to wait for job completion
- **Timeout** - Maximum wait time for job completion

**Security Configuration**:
- Basic authentication support
- API token authentication (recommended)
- CSRF token handling
- SSL certificate validation

**Use Cases**:
- Trigger deployments after successful tests
- Start build pipelines based on data processing results
- Chain Jenkins jobs with MultiFlexi workflows
- Automated CI/CD integration

LaunchJob Action
~~~~~~~~~~~~~~~

.. image:: _static/images/actions/LaunchJob.svg
   :width: 64px
   :height: 64px
   :align: left
   :alt: LaunchJob Action Icon

The LaunchJob Action starts other MultiFlexi jobs or external processes, enabling complex workflow orchestration and job chaining.

**Features**:
- MultiFlexi job launching
- External process execution
- Parameter passing between jobs
- Process lifecycle management
- Resource usage monitoring

**Configuration Options**:
- **Job Type** - MultiFlexi job or external command
- **Job Identifier** - Job ID or command to execute
- **Parameters** - Parameters to pass to the launched job
- **Working Directory** - Directory for command execution
- **Environment Variables** - Custom environment variables
- **Wait for Completion** - Whether to wait for job completion
- **Timeout** - Maximum execution time
- **Success Criteria** - Conditions that define successful completion

**Process Management**:
- Process lifecycle monitoring
- Resource usage tracking
- Output capture and logging
- Clean shutdown handling

**Use Cases**:
- Create complex multi-step workflows
- Launch parallel processing jobs
- Execute system maintenance scripts
- Orchestrate data processing pipelines

Stop Action
~~~~~~~~~~

.. image:: _static/images/actions/Stop.svg
   :width: 64px
   :height: 64px
   :align: left
   :alt: Stop Action Icon

The Stop Action terminates running processes, jobs, or services, providing control over system resources and workflow management.

**Features**:
- Process and service termination
- Graceful shutdown handling
- Force termination capabilities
- Safety confirmation prompts
- Comprehensive operation logging

**Configuration Options**:
- **Target Type** - Process, service, or MultiFlexi job
- **Target Identifier** - Process ID, service name, or job ID
- **Stop Method** - Graceful shutdown or forced termination
- **Timeout** - Time to wait before forcing termination
- **Confirmation Required** - Whether to require manual confirmation

**Safety Features**:
- Confirmation prompts for critical operations
- Graceful shutdown attempts before force killing
- Process ownership verification
- Logging of all stop operations

**Use Cases**:
- Emergency job termination
- Resource cleanup operations
- Service restart workflows
- Automated system maintenance

Zabbix Action
~~~~~~~~~~~~

.. image:: _static/images/actions/Zabbix.svg
   :width: 64px
   :height: 64px
   :align: left
   :alt: Zabbix Action Icon

The Zabbix Action sends monitoring data, alerts, and metrics to Zabbix monitoring systems, enabling comprehensive system monitoring integration.

**Features**:
- Zabbix sender integration
- Custom metric reporting
- Alert generation
- Performance data collection
- Multiple data type support

**Configuration Options**:
- **Zabbix Server** - Hostname or IP of Zabbix server
- **Port** - Zabbix server port (default 10051)
- **Host Name** - Monitored host name in Zabbix
- **Item Key** - Zabbix item key for the metric
- **Value** - Metric value to send
- **Timestamp** - Optional custom timestamp
- **Authentication** - PSK or certificate authentication

**Supported Data Types**:
- Numeric values (integers, floats)
- Text values
- Log entries
- Status indicators
- Custom metrics

**Use Cases**:
- Send job execution metrics to monitoring
- Report application performance data
- Generate custom alerts and notifications
- Integration with existing monitoring infrastructure

CustomCommand Action
~~~~~~~~~~~~~~~~~~~

.. image:: _static/images/actions/CustomCommand.svg
   :width: 64px
   :height: 64px
   :align: left
   :alt: CustomCommand Action Icon

The CustomCommand Action executes arbitrary shell commands or scripts, providing maximum flexibility for system automation and integration.

**Features**:
- Shell command execution
- Script running capabilities
- Environment variable support
- Input/output handling
- Security restrictions

**Configuration Options**:
- **Command** - Shell command or script to execute
- **Arguments** - Command-line arguments
- **Working Directory** - Directory for command execution
- **Environment Variables** - Custom environment variables
- **Input Data** - Data to pass to command stdin
- **Timeout** - Maximum execution time
- **Expected Exit Code** - Success criteria
- **Output Capture** - Whether to capture stdout/stderr

**Security Considerations**:
- Command validation and sanitization
- Restricted execution environments
- User privilege management
- Audit logging of all commands

**Use Cases**:
- Execute system maintenance scripts
- Run data processing utilities
- Integration with legacy systems
- Custom automation workflows

Sleep Action
~~~~~~~~~~~

.. image:: _static/images/actions/Sleep.svg
   :width: 64px
   :height: 64px
   :align: left
   :alt: Sleep Action Icon

The Sleep Action pauses workflow execution for a specified duration, useful for timing control, rate limiting, and coordination between workflow steps.

**Features**:
- Precise timing control
- Interruptible sleep operations
- Progress monitoring
- Resource-efficient waiting
- Multiple time unit support

**Configuration Options**:
- **Duration** - Sleep time amount
- **Time Unit** - Seconds, minutes, hours, or days
- **Interruptible** - Whether sleep can be interrupted
- **Progress Reporting** - Show countdown or progress indicator

**Implementation Features**:
- High-precision timing
- Graceful interruption handling
- Progress monitoring
- Resource-efficient waiting

**Use Cases**:
- Rate limiting for API calls
- Waiting for external system processing
- Coordinating parallel workflows
- Implementing retry delays

Action Configuration Best Practices
----------------------------------

When configuring actions, follow these best practices:

**Security**:
- Use encrypted credential storage
- Implement least-privilege access
- Regular credential rotation
- Audit action configurations

**Performance**:
- Configure appropriate timeouts
- Implement retry mechanisms
- Monitor resource usage
- Use async operations where possible

**Monitoring**:
- Enable comprehensive logging
- Set up alerting for failures
- Track action performance metrics
- Implement health checks

**Maintenance**:
- Document action configurations
- Version control action definitions
- Test actions in staging environments
- Regular configuration reviews

Advanced Action Usage
--------------------

Actions can access the RunTemplate instance through ``$this->runtemplate``, allowing for dynamic parameter configuration based on job context and results. This enables sophisticated automation scenarios where action behavior adapts to runtime conditions.

**Dynamic Configuration Example**:

.. code-block:: php

   public function perform(): bool
   {
       // Access job context
       $jobResult = $this->runtemplate->getJobResult();
       $company = $this->runtemplate->getCompany();
       
       // Adapt action behavior based on context
       if ($jobResult->hasErrors()) {
           $this->setPriority('high');
           $this->setTitle('URGENT: Job Failed - ' . $company->getName());
       }
       
       return $this->execute();
   }

For complex workflows, actions can be chained together, with each action's output potentially influencing subsequent actions. The MultiFlexi framework provides comprehensive logging and error handling to ensure reliable workflow execution.