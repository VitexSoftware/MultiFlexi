FROM debian:latest

ENV APACHE_DOCUMENT_ROOT /usr/share/multiflexi
ENV COMPOSER_ALLOW_SUPERUSER 1
env DEBIAN_FRONTEND=noninteractive
ENV DB_CONNECTION=sqlite
ENV DB_DATABASE=/var/lib/dbconfig-common/sqlite3/multiflexi/multiflexi

RUN apt update ; apt install -y wget  lsb-release wget apt-transport-https  libapache2-mod-php; echo "deb http://repo.vitexsoftware.com  $(lsb_release -sc) main backports" | tee /etc/apt/sources.list.d/vitexsoftware.list ; wget -O /etc/apt/trusted.gpg.d/vitexsoftware.gpg http://repo.vitexsoftware.cz/keyring.gpg
RUN apt-get update && apt-get install -y locales  apache2 php-pdo-sqlite aptitude composer php-cakephp-phinx php-curl php-yaml php-xml cron php-tools locales-all multiflexi-sqlite && rm -rf /var/lib/apt/lists/* \
    && localedef -i cs_CZ -c -f UTF-8 -A /usr/share/locale/locale.alias cs_CZ.UTF-8
ENV LANG cs_CZ.utf8

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN apt update && apt -y install multiflexi-all php-tools

RUN phinx seed:run -s UserSeeder -c /usr/lib/multiflexi/phinx-adapter.php
RUN phinx seed:run -s CompanySeeder -c /usr/lib/multiflexi/phinx-adapter.php
RUN phinx seed:run -s ServerSeeder -c /usr/lib/multiflexi/phinx-adapter.php

RUN php-devconf

RUN multiflexi-executor n ; multiflexi-executor h ; multiflexi-executor d ; multiflexi-executor m

CMD /usr/sbin/cron
CMD [ "/usr/sbin/apache2ctl", "-D", "FOREGROUND" ]
