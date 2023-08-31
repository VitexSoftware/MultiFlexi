# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  config.vm.box = "debian/bullseye64"
  config.vm.network "forwarded_port", guest: 80, host: 8080
  config.vm.provision "shell", inline: <<-SHELL

    export APACHE_DOCUMENT_ROOT=/usr/share/multiflexi/
    export DEBIAN_FRONTEND=noninteractive

    apt install lsb-release wget

    wget -qO- https://repo.vitexsoftware.com/keyring.gpg | sudo tee /etc/apt/trusted.gpg.d/vitexsoftware.gpg
    echo "deb [signed-by=/etc/apt/trusted.gpg.d/vitexsoftware.gpg]  https://repo.vitexsoftware.com  $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/vitexsoftware.list

    echo "deb [trusted=yes] file:///vagrant/deb ./" > /etc/apt/sources.list.d/local.list
    apt-get update
    apt-get install -y apache2 libapache2-mod-php


    apt -y install multiflexi-sqlite

    #apt -y install mariadb-server
    #systemctl start mysql
    #apt -y install multiflexi-mysql

    phinx seed:run -c /usr/lib/multiflexi/phinx-adapter.php
    a2enconf multiflexi

    echo ServerName MultiAbraFlexi >> /etc/apache2/apache2.conf
    #sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
    #sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

    export DEBCONF_DEBUG=developer 

    #apt -y install abraflexi-matcher abraflexi-reminder abraflexi-contract-invoices abraflexi-digest

    apt -y install  php-tools php-xdebug
    echo "xdebug.force_display_errors = 1" >> /etc/php/*/cli/conf.d/20-xdebug.ini
    echo "xdebug.mode=develop"  >> /etc/php/*/cli/conf.d/20-xdebug.ini
    
    php-devconf
    phpenmod xdebug
    
    echo '<a href="multiflexi?login=demo&password=demo">Multi Flexi</a><?php phpinfo();' > /var/www/html/index.php
    rm -rf /var/www/html/index.html

    apache2ctl restart

    apt-get  -y install links
    links -dump http://localhost/multiflexi

  SHELL
end
