FROM debian:latest

ENV APACHE_DOCUMENT_ROOT /usr/share/multi-flexibee-setup/
env DEBIAN_FRONTEND=noninteractive

RUN apt update ; apt install -y wget libapache2-mod-php; echo "deb http://repo.vitexsoftware.cz buster main" | tee /etc/apt/sources.list.d/vitexsoftware.list ; wget -O /etc/apt/trusted.gpg.d/vitexsoftware.gpg http://repo.vitexsoftware.cz/keyring.gpg
RUN apt-get update && apt-get install -y locales php7.3-sqlite apache2 aptitude  cron locales-all && rm -rf /var/lib/apt/lists/* \
    && localedef -i cs_CZ -c -f UTF-8 -A /usr/share/locale/locale.alias cs_CZ.UTF-8
ENV LANG cs_CZ.utf8

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN apt update

RUN aptitude -y install multi-flexibee-setup-sqlite flexibee-matcher flexibee-reminder flexibee-contract-invoices flexibee-digest

RUN phinx seed:run -c /usr/lib/multi-flexibee-setup/phinx-adapter.php
RUN a2ensite multi-flexibee-setup

CMD /usr/sbin/cron
CMD [ "/usr/sbin/apache2ctl", "-D", "FOREGROUND" ]
