# -*- mode: ruby -*-
# vi: set ft=ruby :

# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.
Vagrant.configure("2") do |config|
  config.vm.box = "debian/buster64"
  config.vm.network "forwarded_port", guest: 80, host: 8080
  config.vm.provision "shell", inline: <<-SHELL

    export APACHE_DOCUMENT_ROOT /usr/share/multi-flexibee-setup/
    export DEBIAN_FRONTEND=noninteractive
        
    apt install lsb-release wget
    echo "deb http://repo.vitexsoftware.cz $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/vitexsoftware.list
    wget -O /etc/apt/trusted.gpg.d/vitexsoftware.gpg http://repo.vitexsoftware.cz/keyring.gpg

    apt-get update
    apt-get install -y apache2 
    apt -y install multi-flexibee-setup-sqlite
    phinx seed:run -c /usr/lib/multi-flexibee-setup/phinx-adapter.php

    sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
    sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# apt -y install flexibee-matcher 
#    apt -y install  flexibee-reminder 
#    apt -y install flexibee-contract-invoices 
#    apt -y install flexibee-digest

  SHELL
end
