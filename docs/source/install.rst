Installation Guide
==================

.. contents::

This is the installation guide for MultiFlexi.

To install MultiFlexi using Debian packages, you can follow these steps:

1. Prepare your system by running the following commands in a terminal:

    ```bash
    sudo apt update
    sudo apt install lsb-release apt-transport-https bzip2 ca-certificates curl
    ```

2. Choose stability by running the following commands:

    ```bash
    curl -sSLo /tmp/multiflexi-archive-keyring.deb https://repo.multiflexi.eu/multiflexi-archive-keyring.deb
    dpkg -i /tmp/multiflexi-archive-keyring.deb
    sh -c 'echo "deb [signed-by=/usr/share/keyrings/repo.multiflexi.eu.gpg] https://repo.multiflexi.eu/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/multiflexi.list'
    ```

3. Update the package sources by running the following command:

    ```bash
    sudo apt update
    ```

4. Install MultiFlexi for the chosen database by running the following command:

    ```bash
    sudo apt install multiflexi-mysql
    ```

5. Check for available applications by running the following command:

    ```bash
    apt search multiflexi
    ```

That's it! You have successfully updated the installation instructions for MultiFlexi using Debian packages.