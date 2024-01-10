FROM debian:latest

ENV APACHE_DOCUMENT_ROOT /opt/multiflexi/src
ENV COMPOSER_ALLOW_SUPERUSER 1
env DEBIAN_FRONTEND=noninteractive
ENV DB_CONNECTION=sqlite
ENV DB_DATABASE=/opt/multiflexi/db/multiflexi.sqlite

RUN apt update ; apt install -y wget  lsb-release wget apt-transport-https  libapache2-mod-php; echo "deb http://repo.vitexsoftware.com  $(lsb_release -sc) main backports" | tee /etc/apt/sources.list.d/vitexsoftware.list ; wget -O /etc/apt/trusted.gpg.d/vitexsoftware.gpg http://repo.vitexsoftware.cz/keyring.gpg
RUN apt-get update && apt-get install -y locales php-pdo-sqlite apache2 aptitude composer php-cakephp-phinx php-curl php-yaml php-xml cron php-tools locales-all && rm -rf /var/lib/apt/lists/* \
    && localedef -i cs_CZ -c -f UTF-8 -A /usr/share/locale/locale.alias cs_CZ.UTF-8
ENV LANG cs_CZ.utf8

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY . /opt/multiflexi
COPY bin/multiflexi-probe /usr/bin

RUN php-devconf ; rm -f /opt/multiflexi/.env ; touch /opt/multiflexi/db/multiflexi.sqlite ; chown www-data:www-data /opt/multiflexi/db/multiflexi.sqlite ; cd /opt/multiflexi ; composer update ; cd src ; ../vendor/bin/phinx migrate -c ../phinx-adapter.php ;  ../vendor/bin/phinx seed:run -c ../phinx-adapter.php; cd ../lib; php -f json2app.php ../tests/multiflexi_probe.multiflexi.app.json
#RUN a2ensite multiflexi

CMD /usr/sbin/cron
CMD [ "/usr/sbin/apache2ctl", "-D", "FOREGROUND" ]
