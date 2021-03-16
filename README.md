Multi Flexi Bee Setup
=====================

![MFB](src/images/project-logo.svg?raw=true)

Umoźňuje spouštět zvolené nástroje nad určitými účetními jednotkami AbraFlexi v daných intervalech. 

Nastavené úlohy jsou pravidelně spouštěny ze systémovécho plánovače.
Protokol spouštění je zapisován do systémového logu.

Spouštěným skriptům jsou nastavoavány tyto proměnné prostředí:

 * **ABRAFLEXI_URL**
 * **ABRAFLEXI_LOGIN** 
 * **ABRAFLEXI_PASSWORD**
 * **ABRAFLEXI_COMPANY**

+ proměnné prostředí dle individuální konfigurace každého modulu pro každou firmu


instalace
---------

```shell
sudo apt install lsb-release wget
echo "deb http://repo.vitexsoftware.cz $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/vitexsoftware.list
sudo wget -O /etc/apt/trusted.gpg.d/vitexsoftware.gpg http://repo.vitexsoftware.cz/keyring.gpg
sudo apt update
sudo apt install multi-abraflexi-setup-DATABASE 
```

k dispozici jsou tyto databázové adaptéry: **multi-abraflexi-setup-mysql**, **multi-abraflexi-setup-pgsql** a **multi-abraflexi-setup-sqlite**


Screenshoty
-----------

Přehled stavu aplikace:  
![MFB](doc/MultiAbraFlexiSetup.png?raw=true)

Editace Aplikace/Skriptu:
![MFB](doc/Application.png?raw=true)  

Přehled nastavených aplikací:
![MFB](doc/Applications.png?raw=true)  

Editace firmy a nastavení spouštěných služeb
![MFB](doc/Company.png?raw=true)  

Instance AbraFlexi serveru:
![MFB](doc/instance.png?raw=true)

Pluginy:
--------

Jako plugin je možné použít jakýkoliv spustitelný skript nebo binárku. Tyto jsou však připraveny k použití:

 * https://github.com/VitexSoftware/abraflexi-contract-invoices
 * https://github.com/VitexSoftware/php-abraflexi-matcher
 * https://github.com/VitexSoftware/php-abraflexi-reminder
 * https://github.com/VitexSoftware/AbraFlexi-Digest
 * https://github.com/Vitexus/ISDOC-via-IMAP-to-AbraFlexi
