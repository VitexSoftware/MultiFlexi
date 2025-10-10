Docker Deployment
=================

MultiFlexi provides a complete Docker-based deployment solution using Docker Compose. This setup allows you to run the entire MultiFlexi stack in containers, making deployment and management easier across different environments.

Overview
--------

The Docker setup includes the following services:

* **web**: MultiFlexi web interface (Apache + PHP)
* **executor**: MultiFlexi job executor daemon
* **scheduler**: MultiFlexi job scheduler daemon  
* **db**: MySQL 8.0 database
* **redis**: Redis cache server

All services are orchestrated using Docker Compose and follow the same architecture as systemd-based installations.

Quick Start
-----------

1. Navigate to the docker directory:

   .. code-block:: bash

      cd docker/

2. Copy the environment templates:

   .. code-block:: bash

      cp .env.example .env
      cp multiflexi.env.example multiflexi.env

3. Edit the configuration files:

   .. code-block:: bash

      nano .env              # Docker Compose environment
      nano multiflexi.env    # MultiFlexi application environment

4. Start the services:

   .. code-block:: bash

      docker-compose up -d

5. Access the web interface at http://localhost:8080

Service Architecture
--------------------

Executor Service
^^^^^^^^^^^^^^^^

The executor service runs the MultiFlexi job executor daemon:

* **Base Image**: debian:bookworm-slim
* **Package**: multiflexi-executor (from VitexSoftware repository)
* **Command**: ``/usr/bin/php /usr/lib/multiflexi-executor/daemon.php``
* **User**: multiflexi:multiflexi
* **Purpose**: Executes scheduled MultiFlexi jobs

This matches the systemd service definition in ``/usr/lib/systemd/system/multiflexi-executor.service``.

Scheduler Service
^^^^^^^^^^^^^^^^^

The scheduler service runs the MultiFlexi job scheduler daemon:

* **Base Image**: debian:bookworm-slim
* **Package**: multiflexi-scheduler (from VitexSoftware repository)
* **Command**: ``/usr/bin/php /usr/lib/multiflexi-scheduler/daemon.php``
* **User**: multiflexi:multiflexi
* **Purpose**: Manages job scheduling and timing

This matches the systemd service definition in ``/usr/lib/systemd/system/multiflexi-scheduler.service``.

Web Service
^^^^^^^^^^^

The web service provides the MultiFlexi web interface:

* **Base Image**: Uses existing Dockerfile (Debian + Apache + PHP)
* **Port**: 8080 (configurable via WEB_PORT)
* **Document Root**: /opt/multiflexi/src
* **Purpose**: Web interface and API access

Database Service
^^^^^^^^^^^^^^^^

MySQL 8.0 database service:

* **Image**: mysql:8.0
* **Port**: 3306 (configurable via DB_PORT)
* **Health Check**: Built-in MySQL ping
* **Persistence**: Named volume for data

Redis Service
^^^^^^^^^^^^^

Redis cache service:

* **Image**: redis:7-alpine
* **Purpose**: Caching and job queues
* **Persistence**: Named volume for data

Configuration
-------------

Docker Compose Environment (.env)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Docker Compose specific settings:

.. code-block:: bash

   # Web Configuration
   WEB_PORT=8080

   # Database Configuration
   DB_CONNECTION=mysql
   DB_DATABASE=multiflexi
   DB_USERNAME=multiflexiuser
   DB_PASSWORD=secure_user_password
   MYSQL_ROOT_PASSWORD=very_secure_root_password

   # Optional: Database port exposure
   DB_PORT=3306

MultiFlexi Environment (multiflexi.env)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Application-specific settings that match the systemd service configuration:

.. code-block:: bash

   # Database Configuration
   DB_CONNECTION=mysql
   DB_HOST=db
   DB_DATABASE=multiflexi
   DB_USERNAME=multiflexiuser  
   DB_PASSWORD=secure_user_password

   # Application Configuration
   MULTIFLEXI_DEBUG=false
   MULTIFLEXI_LOG_LEVEL=info

   # Paths
   MULTIFLEXI_CONFIG_DIR=/etc/multiflexi
   MULTIFLEXI_LOG_DIR=/var/log/multiflexi

   # Timezone
   TZ=Europe/Prague

Volume Management
-----------------

The setup uses named volumes for data persistence:

* ``db_data``: MySQL database files
* ``redis_data``: Redis data
* ``web_logs``: Apache access and error logs
* ``executor_logs``: Executor service logs
* ``scheduler_logs``: Scheduler service logs
* ``sqlite_data``: SQLite database files (if using SQLite)
* ``config_data``: MultiFlexi configuration files

Package Installation
--------------------

The Dockerfiles use ``apt install multiflexi-*`` packages from the VitexSoftware repository instead of copying source files. This approach provides several benefits:

Consistency
^^^^^^^^^^^

* Same packages as system installations
* Identical file locations and permissions
* Consistent behavior across environments

Dependency Management
^^^^^^^^^^^^^^^^^^^^^

* All required PHP modules automatically installed
* Proper system integration
* Correct package dependencies resolved

Security
^^^^^^^^

* Official package updates and security patches
* No custom build processes
* Trusted package signatures

Repository Configuration
^^^^^^^^^^^^^^^^^^^^^^^^

Both Dockerfiles add the VitexSoftware repository:

.. code-block:: dockerfile

   # Add VitexSoftware repository
   RUN apt-get update && apt-get install -y \
       wget \
       lsb-release \
       apt-transport-https \
       gpg \
   && wget -O /usr/share/keyrings/repo.vitexsoftware.com.gpg http://repo.vitexsoftware.com/KEY.gpg \
   && echo "deb [signed-by=/usr/share/keyrings/repo.vitexsoftware.com.gpg] http://repo.vitexsoftware.com $(lsb_release -sc) main backports" | tee /etc/apt/sources.list.d/vitexsoftware.list \
       && apt-get update

Management Commands
-------------------

Starting Services
^^^^^^^^^^^^^^^^^

.. code-block:: bash

   # Start all services in background
   docker-compose up -d

   # Start specific service
   docker-compose up -d executor

Viewing Logs
^^^^^^^^^^^^

.. code-block:: bash

   # View all logs
   docker-compose logs -f

   # View specific service logs
   docker-compose logs -f executor
   docker-compose logs -f scheduler

Stopping Services
^^^^^^^^^^^^^^^^^

.. code-block:: bash

   # Stop all services
   docker-compose down

   # Stop and remove volumes (destructive)
   docker-compose down -v

Service Status
^^^^^^^^^^^^^^

.. code-block:: bash

   # Check service status
   docker-compose ps

   # Check service health
   docker-compose exec db mysqladmin ping -h localhost

Rebuilding Images
^^^^^^^^^^^^^^^^^

.. code-block:: bash

   # Rebuild all images
   docker-compose build --no-cache

   # Rebuild specific service
   docker-compose build --no-cache executor

Database Management
^^^^^^^^^^^^^^^^^^^

.. code-block:: bash

   # Database backup
   docker-compose exec db mysqladmin -u root -p multiflexi > backup.sql

   # Database restore
   docker-compose exec -i db mysql -u root -p multiflexi < backup.sql

   # Connect to database
   docker-compose exec db mysql -u root -p

Troubleshooting
---------------

Service Won't Start
^^^^^^^^^^^^^^^^^^^

1. Check the logs:

   .. code-block:: bash

      docker-compose logs [service_name]

2. Verify configuration files exist:

   .. code-block:: bash

      ls -la .env multiflexi.env

3. Check for port conflicts:

   .. code-block:: bash

      netstat -tulpn | grep :8080

Database Connection Issues
^^^^^^^^^^^^^^^^^^^^^^^^^^

1. Ensure database service is healthy:

   .. code-block:: bash

      docker-compose ps db

2. Test database connectivity from executor:

   .. code-block:: bash

      docker-compose exec executor php -r "echo 'DB connection test';"

3. Check database logs:

   .. code-block:: bash

      docker-compose logs db

Environment File Issues
^^^^^^^^^^^^^^^^^^^^^^^

Verify the ``multiflexi.env`` file is properly mounted:

.. code-block:: bash

   docker-compose exec executor cat /etc/multiflexi/multiflexi.env

Permission Issues
^^^^^^^^^^^^^^^^^

If you encounter permission issues, ensure the multiflexi user has proper access:

.. code-block:: bash

   # Check user in container
   docker-compose exec executor id

   # Check file permissions
   docker-compose exec executor ls -la /etc/multiflexi/

Port Conflicts
^^^^^^^^^^^^^^

If ports 8080 or 3306 are already in use:

1. Change ports in ``.env`` file:

   .. code-block:: bash

      WEB_PORT=8081
      DB_PORT=3307

2. Restart services:

   .. code-block:: bash

      docker-compose down && docker-compose up -d

Differences from SystemD
-------------------------

When running in Docker vs systemd:

SystemD Services
^^^^^^^^^^^^^^^^

* Services managed by systemd init system
* Background daemon processes
* Automatic restart on failure
* System integration with journal logging

Docker Services
^^^^^^^^^^^^^^^

* Services run as foreground processes in containers
* Docker handles process management and restarts
* Container-based isolation
* Structured logging to stdout/stderr

Common Elements
^^^^^^^^^^^^^^^

* **Commands**: Identical PHP daemon commands
* **User**: Same multiflexi:multiflexi user/group
* **Environment**: Same ``/etc/multiflexi/multiflexi.env`` format
* **No Cron**: Both use PHP daemon processes, not cron jobs
* **Packages**: Same VitexSoftware packages

Production Considerations
-------------------------

Resource Limits
^^^^^^^^^^^^^^^^

Consider adding resource limits to your docker-compose.yml:

.. code-block:: yaml

   services:
     executor:
       deploy:
         resources:
           limits:
             cpus: '0.50'
             memory: 512M
           reservations:
             memory: 256M

Logging
^^^^^^^

Configure log rotation and retention:

.. code-block:: yaml

   services:
     web:
       logging:
         driver: "json-file"
         options:
           max-size: "10m"
           max-file: "3"

Health Checks
^^^^^^^^^^^^^

Add custom health checks for application services:

.. code-block:: yaml

   services:
     executor:
       healthcheck:
         test: ["CMD", "php", "-f", "/usr/lib/multiflexi-executor/health-check.php"]
         interval: 30s
         timeout: 10s
         retries: 3

Security
^^^^^^^^

* Use strong passwords in environment files
* Keep environment files out of version control
* Regularly update base images and packages
* Consider using Docker secrets for sensitive data
* Run containers with read-only root filesystem where possible

Backup Strategy
^^^^^^^^^^^^^^^

* Regular database backups using ``docker-compose exec``
* Backup named volumes containing persistent data
* Store configuration files in version control
* Test restore procedures regularly

Monitoring
^^^^^^^^^^

* Monitor container health and resource usage
* Set up log aggregation for centralized logging
* Configure alerts for service failures
* Monitor database performance and storage usage
