FROM debian:latest

ENV APACHE_DOCUMENT_ROOT /usr/share/multi-flexibee-setup/
env DEBIAN_FRONTEND=noninteractive

RUN apt update ; apt install -y wget; echo "deb http://repo.vitexsoftware.cz buster main" | tee /etc/apt/sources.list.d/vitexsoftware.list ; wget -O /etc/apt/trusted.gpg.d/vitexsoftware.gpg http://repo.vitexsoftware.cz/keyring.gpg
RUN apt-get update && apt-get install -y locales php7.3-sqlite apache2 aptitude  cron locales-all && rm -rf /var/lib/apt/lists/* \
    && localedef -i cs_CZ -c -f UTF-8 -A /usr/share/locale/locale.alias cs_CZ.UTF-8
ENV LANG cs_CZ.utf8

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN apt update

RUN aptitude -y install multi-flexibee-setup
#RUN apt -y install flexibee-matcher 
RUN apt -y install  flexibee-reminder 
RUN apt -y install flexibee-contract-invoices 
RUN apt -y install flexibee-digest

RUN phinx seed:run -c /usr/lib/multi-flexibee-setup/phinx-adapter.php


CMD /usr/sbin/cron
CMD [ "/usr/sbin/apache2ctl", "-D", "FOREGROUND" ]
