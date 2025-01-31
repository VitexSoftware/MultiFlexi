MultiFlexi Development
======================

Welcome to the MultiFlexi development documentation. This guide will help you get started with developing and contributing to the MultiFlexi project.

.. image:: https://wakatime.com/badge/user/5abba9ca-813e-43ac-9b5f-b1cfdf3dc1c7/project/28a38241-3585-4ce7-b365-d7341c69e635.svg
    :target: https://wakatime.com



Setting Up Your Development Environment
---------------------------------------

1. **Clone the Repository**: Start by cloning the MultiFlexi repository from GitHub.

    ```bash
    git clone https://github.com/VitexSoftware/MultiFlexi.git
    cd MultiFlexi
    ```

Code Structure
--------------

The MultiFlexi project is organized into several key components:

- **src/**: Contains the main source code for the project.
- **tests/**: Contains test cases for the project.
- **db/**: Contains database migrations and seed data.


Analysis
--------

The most complex file appears to be ``src/MultiFlexi/Job.php``. Here's why:

**Complexity Factors:**

- **Multiple Dependencies**: It depends on several other classes within the MultiFlexi namespace, including:

    - ``\MultiFlexi\executor`` (Interface)
    - ``\MultiFlexi\Zabbix\Request\Metric``
    - ``\MultiFlexi\Zabbix\Request\Packet``
    - ``\MultiFlexi\ZabbixSender``
    - ``\MultiFlexi\Application``
    - ``\MultiFlexi\Company``
    - ``\MultiFlexi\RunTemplate``
    - ``\Ease\Shared``
    - ``\Ease\Logger\Logging``

- **Business Logic**: It encapsulates core business logic related to job execution:

    - ``prepareJob``: Sets up the job environment and creates a new job record in the database.
    - ``runBegin``: Prepares the job for execution, setting up logging and Zabbix reporting.
    - ``runEnd``: Performs actions after the job has run, including updating the database, reporting to Zabbix, and handling output.
    - ``performJob``: Orchestrates the actual job execution using an executor.
    - ``scheduleJobRun``: Schedules the job to run at a specific time.
    - ``reportToZabbix``: Handles sending data to Zabbix.
    - ``cleanUp``: Removes temporary files after job execution.
    - ``launcherScript``: Generates a shell script for executing the job.

- **Environment Handling**: It manages the environment variables for the job, including fetching data from various sources and applying macros.

    - ``getFullEnvironment()``: Retrieves the full environment for the job, including data from the application, company, and run template.
    - ``compileEnv()``: Compiles the environment variables into a flat array.
    - ``applyMarcros()``: Populates the environment with values from the job's context.

- **Database Interaction**: It interacts with the database for creating, updating, and deleting job records, as well as related data.

    - Uses ``Ease\SQL\Engine`` for database operations.

- **Plugin System**: It is designed to work with different executors.

    - Uses an executor interface to allow flexibility in how jobs are executed.

- **Zabbix Integration**: It has logic to send data to Zabbix monitoring system.

    - Uses ``MultiFlexi\Zabbix\Request\Metric`` and ``MultiFlexi\Zabbix\Request\Packet`` to create Zabbix messages.
    - Uses ``MultiFlexi\ZabbixSender`` to send data to Zabbix.

- **State Management**: It keeps track of the job's state and provides methods to set and update the state.

- **Complexity of Methods**: Some methods, like ``prepareJob`` and ``runEnd``, perform a series of complex actions.

- **Integration with Other Modules**: It integrates with other parts of the system, such as the scheduler, configuration, and action modules.

**Other Notable Files:**

- ``src/MultiFlexi/Ui/DBDataTable.php``: This file is also quite complex, as it handles the generation of DataTables, which involves a lot of configuration and rendering logic.
- ``src/MultiFlexi/Api/Server/DefaultApi.php``: This file is complex in its routing and handling of various API requests, but it's more about orchestration than complex logic.
- ``src/MultiFlexi/Action/Github.php``: Handles integration with external service and needs to handle API communication.

**Conclusion:**

While other files have their own complexities, ``src/MultiFlexi/Job.php`` stands out due to its combination of core business logic, database interactions, environment handling, plugin system integration, and external service integration. It is the central orchestrator for job execution in the MultiFlexi system.


Contributing
------------

We welcome contributions from the community. To contribute, follow these steps:

1. **Fork the Repository**: Fork the MultiFlexi repository to your GitHub account.
2. **Create a Branch**: Create a new branch for your feature or bugfix.

    ```bash
    git checkout -b feature-name
    ```

3. **Make Changes**: Make your changes and commit them with a descriptive message.

    ```bash
    git commit -m "Description of the feature or fix"
    ```

4. **Push Changes**: Push your changes to your forked repository.

    ```bash
    git push origin feature-name
    ```

5. **Create a Pull Request**: Open a pull request on the original repository.

Thank you for contributing to MultiFlexi!

