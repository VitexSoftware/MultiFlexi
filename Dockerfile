FROM debian:latest

ENV APACHE_DOCUMENT_ROOT /usr/share/multiflexi/
env DEBIAN_FRONTEND=noninteractive

RUN apt update ; apt install -y wget  lsb-release wget apt-transport-https  libapache2-mod-php; echo "deb http://repo.vitexsoftware.com  $(lsb_release -sc) main backports" | tee /etc/apt/sources.list.d/vitexsoftware.list ; wget -O /etc/apt/trusted.gpg.d/vitexsoftware.gpg http://repo.vitexsoftware.cz/keyring.gpg
RUN apt-get update && apt-get install -y locales php-pdo-sqlite apache2 aptitude  cron locales-all && rm -rf /var/lib/apt/lists/* \
    && localedef -i cs_CZ -c -f UTF-8 -A /usr/share/locale/locale.alias cs_CZ.UTF-8
ENV LANG cs_CZ.utf8

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY dist/multi-flexi-dist_*_all.deb /tmp/multi-flexi-dist_all.deb 

RUN apt update && dpkg -i /tmp/multi-flexi-dist_all.deb && apt update

RUN aptitude -y install multiflexi-sqlite abraflexi-matcher abraflexi-reminder abraflexi-contract-invoices abraflexi-digest abraflexi-mailer abraflexi-email-importer

RUN phinx seed:run -c /usr/lib/multiflexi/phinx-adapter.php
#RUN a2ensite multiflexi

CMD /usr/sbin/cron
CMD [ "/usr/sbin/apache2ctl", "-D", "FOREGROUND" ]
