Installation Guide
==================

Installation of MultiFlexi is simple on Debian based systems. The installation process is done using Debian packages. The packages are available for MySQL, PostgreSQL, and SQLite databases.

**Current Version: 1.29.0** - includes enhanced monitoring capabilities with MultiFlexi Probe.

.. attention::

   The MultiFlexi packages are available for

    - Debian 12 (Bookworm)
    - Debian 13 (Trixie)
    - Debian 14 (Forky)
    - Ubuntu 22.04 (Jammy Jellyfish)
    - Ubuntu 24.04 (Noble Numbat)

.. important::
   
   **System Requirements**
   
   - **Memory**: Minimum 2GB RAM required for database migration operations
   - Database server (MySQL, PostgreSQL, or SQLite)
   - Web server (Apache2, Nginx, or similar) for web interface access

To install MultiFlexi using Debian packages, you can follow these steps:

1. Prepare your system by running the following commands in a terminal:

.. code-block:: bash

    sudo apt update
    sudo apt install lsb-release apt-transport-https bzip2 ca-certificates curl

2. Add the MultiFlexi repository and key:

.. code-block:: bash


    # For production (stable) repository:
    wget -O /usr/share/keyrings/repo.multiflexi.eu.gpg https://repo.multiflexi.eu/KEY.gpg
    echo "deb [signed-by=/usr/share/keyrings/repo.multiflexi.eu.gpg] https://repo.multiflexi.eu/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/multiflexi.list

    # For testing (nightly) repository:
    wget -O /usr/share/keyrings/repo.vitexsoftware.com.gpg http://repo.vitexsoftware.com/KEY.gpg
    echo "deb [signed-by=/usr/share/keyrings/repo.vitexsoftware.com.gpg] http://repo.vitexsoftware.com/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/multiflexi-testing.list

    # Use the production keyring for stable, and the testing keyring for nightly builds.

3. Update the package sources:

.. code-block:: bash

    sudo apt update

4. Install MultiFlexi for the chosen database:

.. code-block:: bash

    sudo apt install multiflexi-mysql


.. compound::

    On fresh Ubuntu 22.04 (with database already installed), the installation command will look like this::

        nymph@jammy:~$ sudo apt install multiflexi-mysql
        Reading package lists... Done
        Building dependency tree... Done
        Reading state information... Done
        The following additional packages will be installed:
           anacron composer composer-debian dbconfig-common dbconfig-mysql javascript-common jq jsonlint libio-pty-perl libipc-run-perl libjq1 libjs-jquery
           libjs-jquery-datatables libjs-jquery-selectize.js libjs-microplugin.js libjs-sifter.js libonig5 libtime-duration-perl locales-all mailcap
           mime-support moreutils multiflexi php-auth-sasl php-cakephp-phinx php-cli php-common php-composer-ca-bundle php-composer-metadata-minifier
           php-composer-pcre php-composer-semver php-composer-spdx-licenses php-composer-xdebug-handler php-intl php-json-schema php-mail php-mail-mime
           php-mbstring php-mysql php-net-smtp php-net-socket php-pear php-psr-container php-psr-log php-react-promise php-symfony-console
           php-symfony-deprecation-contracts php-symfony-filesystem php-symfony-finder php-symfony-polyfill-php80 php-symfony-process
           php-symfony-service-contracts php-symfony-string php-vitexsoftware-ease-bootstrap4 php-vitexsoftware-ease-bootstrap4-widgets
           php-vitexsoftware-ease-core php-vitexsoftware-ease-fluentpdo php-vitexsoftware-ease-html php-vitexsoftware-ease-html-widgets php-xml php-yaml
           php8.1-cli php8.1-common php8.1-intl php8.1-mbstring php8.1-mysql php8.1-opcache php8.1-readline php8.1-xml php8.1-yaml unzip
        Suggested packages:
           default-mta \| mail-transport-agent fossil mercurial subversion php-zip apache2 \| lighttpd \| httpd multiflexi-all php-symfony-event-dispatcher
           php-symfony-lock php-symfony-service-implementation php-vitexsoftware-ease-bootstrap zip
           The following NEW packages will be installed:
           anacron composer composer-debian dbconfig-common dbconfig-mysql javascript-common jq jsonlint libio-pty-perl libipc-run-perl libjq1 libjs-jquery
           libjs-jquery-datatables libjs-jquery-selectize.js libjs-microplugin.js libjs-sifter.js libonig5 libtime-duration-perl locales-all mailcap
           mime-support moreutils multiflexi multiflexi-mysql php-auth-sasl php-cakephp-phinx php-cli php-common php-composer-ca-bundle
           php-composer-metadata-minifier php-composer-pcre php-composer-semver php-composer-spdx-licenses php-composer-xdebug-handler php-intl
           php-json-schema php-mail php-mail-mime php-mbstring php-mysql php-net-smtp php-net-socket php-pear php-psr-container php-psr-log
           php-react-promise php-symfony-console php-symfony-deprecation-contracts php-symfony-filesystem php-symfony-finder php-symfony-polyfill-php80
           php-symfony-process php-symfony-service-contracts php-symfony-string php-vitexsoftware-ease-bootstrap4 php-vitexsoftware-ease-bootstrap4-widgets
           php-vitexsoftware-ease-core php-vitexsoftware-ease-fluentpdo php-vitexsoftware-ease-html php-vitexsoftware-ease-html-widgets php-xml php-yaml
           php8.1-cli php8.1-common php8.1-intl php8.1-mbstring php8.1-mysql php8.1-opcache php8.1-readline php8.1-xml php8.1-yaml unzip
        0 upgraded, 72 newly installed, 0 to remove and 0 not upgraded.
        Need to get 70.0 MB of archives.
        After this operation, 455 MB of additional disk space will be used.
        Do you want to continue? [Y/n]

   The package name may vary depending on the chosen database.

.. note:: 

   - The `multiflexi-sqlite` package is used for testing purposes in automated environments.
   - Only the `multiflexi-mysql` package is recommended for production use. 
   - The `multiflexi-postgresql` package is not yet usable. Please fill GitHub issue if you want to help with development or testing.  

5.  During the installation, you will be asked to configure the database.

.. figure:: ubuntu22dbconfig.png
    :alt: Ubuntu 22.04 DB Config
    :align: center

    Database configuration dialog. The password field may be left empty to auto-generate a secure password.

.. figure:: ubuntu22dbpassword.png
    :alt: Ubuntu 22.04 DB Password
    :align: center

    Database password dialog. The database name may be left empty to auto-generate a default one.

Then installation will continue and finish.

.. image:: successfullinstallationdone.png
    :alt: Ubuntu 22.04 Installation Done
    :align: center

.. note::

    Finally the configuration file is saved as /etc/multiflexi/multiflexi.env

6. Check for available applications:

MultiFlexi is an empty shell without applications until you install them. Applications are available as Debian packages and can be installed on Debian-based systems like Ubuntu. To list available MultiFlexi application packages:

.. code-block:: bash

    apt search multiflexi

.. image:: apps-availble.png
    :alt: MultiFlexi Apt Search
    :align: center

For more details about available applications, visit the `MultiFlexi apps page <https://www.multiflexi.eu/apps.php>`_.

.. tip::

    To install all available applications, use the `multiflexi-all` meta package. For more details, visit the `multiflexi-all <https://github.com/VitexSoftware/multiflexi-all/>`_ GitHub repository.

For more information on how to perform the initial setup, please refer to the :doc:`firstrun` page.

.. autosummary::

   :toctree: generated
