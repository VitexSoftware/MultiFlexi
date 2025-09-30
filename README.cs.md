MultiFlexi
===========

![MFB](multiflexi-social-preview.svg?raw=true)

[![ReadTheDocs](https://readthedocs.org/projects/multiflexi/badge/)](https://multiflexi.readthedocs.io/)
[![wakatime](https://wakatime.com/badge/user/5abba9ca-813e-43ac-9b5f-b1cfdf3dc1c7/project/28a38241-3585-4ce7-b365-d7341c69e635.svg)](https://wakatime.com/badge/user/5abba9ca-813e-43ac-9b5f-b1cfdf3dc1c7/project/28a38241-3585-4ce7-b365-d7341c69e635)
[![CodeFactor](https://www.codefactor.io/repository/github/vitexsoftware/multiflexi/badge)](https://www.codefactor.io/repository/github/vitexsoftware/multiflexi)
[![GitHub license](https://img.shields.io/github/license/VitexSoftware/MultiFlexi)](https://opensource.org/licenses/MIT)
[![GitHub release](https://img.shields.io/github/v/release/VitexSoftware/MultiFlexi)](https://github.com/VitexSoftware/MultiFlexi/releases)


Umožňuje spouštět zvolené nástroje nad určitými účetními jednotkami AbraFlexi v daných intervalech.

**Verze 1.29.0** přináší vylepšené možnosti monitorování s novou aplikací MultiFlexi Probe pro kontrolu stavu systému a testování.

Nastavené úlohy jsou pravidelně spouštěny ze systémovécho plánovače.
Protokol spouštění je zapisován do systémového logu.

Spouštěným skriptům jsou nastavoavány například tyto proměnné prostředí:


* **ABRAFLEXI_URL**
* **ABRAFLEXI_LOGIN**
* **ABRAFLEXI_PASSWORD**
* **ABRAFLEXI_COMPANY**

nebo

* **POHODA_ICO**
* **POHODA_URL**
* **POHODA_USERNAME**
* **POHODA_PASSWORD**

⊕ proměnné prostředí dle individuální konfigurace každého modulu pro každou firmu

See the https://multiflexi.readthedocs.io/ for complete documentation

Demo
----

K dispozici je [ukázková instance](https://demo.multiflexi.eu/?login=demo\&password=demo)

![demo screenshot](doc/index-1.10.4.314.png?raw=true)

instalace
---------

K dispozici jsou balíčky pro Debian. Více informací o instalaci naleznete v [instalační dokumentaci](INSTALL.md)

Ovládání z příkazového řádku
============================

ve složce bin se nacházejí tyto spouštěče různých funkcí:

* `multiflexi-app2json` - exportuje definici aplikace do souboru
* `multiflexi-executor` - periodický spouštěč aplikací
* `multiflexi-job2script` - vygeneruje skript s nastavením prostředí a příkazem pro běhu úlohy dle jejího čísla
* `multiflexi-json-app-remover` - na základě json definice odstraní aplikaci z MultiFlexi
* `multiflexi-json2app` - načte definice aplikace ze souboru
* `multiflexi-probe` - sonda pro monitorování systému a kontrolu stavu MultiFlexi

multiflexi-cli
--------------

MultiFlexi CLI poskytuje komplexní možnosti správy s podporou pro aplikace, firmy, úlohy, šablony spuštění a další.

Použití: multiflexi-cli <příkaz> [akce] [možnosti]

Klíčové příkazy: application, company, job, runtemplate, version

**Správa aplikací:**
- `multiflexi-cli application validate-json --json app.json` - validace JSON aplikace proti schématu
- `multiflexi-cli application import-json --json app.json` - import aplikace z JSON
- `multiflexi-cli application list` - seznam všech aplikací


Pluginy
-------

Jako plugin je možné použít jakýkoliv spustitelný skript nebo binárku. Uvádíme zde některé, připravené k použití:

|Jméno|Popis|Domovská stránka|
|-----|-----|----------------|
|MultiFlexi Probe|Sonda pro monitorování systému a testování funkcí|https://github.com/VitexSoftware/MultiFlexi|
|Email Importer|Načítá doklady z mailboxu do FlexiBee|https://github.com/VitexSoftware/AbraFlexi-email-importer|
|discomp2abraflexi|Import Pricelist from Discomp to AbraFlexi|https://github.com/Spoje-NET/discomp2abraflexi|
|AbraFlexi Revolut statements import|Import Revolut bank statemetnts into AbraFlexi|https://github.com/VitexSoftware/AbraFlexi-Revolut|
|AbraFlexi Checker|Kontrola dostupnosti AbraFlexi|https://github.com/VitexSoftware/php-abraflexi-config|
|Vůbec přehled|přehled vašeho účetnictví od začátku do nynějška|https://github.com/VitexSoftware/AbraFlexi-Digest/|
|Dení přehled|každodení přehled vašeho účetnictví|https://github.com/VitexSoftware/AbraFlexi-Digest/|
|Měsíční přehled|měsíční přehled vašeho účetnictví|https://github.com/VitexSoftware/AbraFlexi-Digest/|
|Týdení přehled|přehled vašeho účetnictví každý týden|https://github.com/VitexSoftware/AbraFlexi-Digest/|
|Roční přehed|Každoroční AbraFlexi přehled|https://github.com/VitexSoftware/AbraFlexi-Digest/|
|Hromadná pošta z AbraFlexi|Na základě dotazu zvolí příjmce z adresáře a odesílá mail na základě šablony|https://github.com/VitexSoftware/abraflexi-mailer/|
|AbraFlexi odesílač|Odešle všechny doklady vydaných faktur které ještě nebyly odeslány|https://github.com/VitexSoftware/abraflexi-mailer/|
|Odesílač pošty|Odešli neodeslané dokumenty s přílohami|https://github.com/VitexSoftware/abraflexi-mailer/|
|Ukaž neodeslané|Zobraz neodeslané dokumenty|https://github.com/VitexSoftware/abraflexi-mailer/|
|Smlouvy na Faktury|Spustí generování faktur ze smluv v AbraFlexi|https://github.com/VitexSoftware/abraflexi-contract-invoices|
|AbraFlexi Benchmark|AbraFlexi Server Benchmark|https://github.com/VitexSoftware/AbraFlexi-Tools|
|AbraFlexi Copy|Copy Company data between two AbraFlexi servers|https://github.com/VitexSoftware/AbraFlexi-Tools|
|AbraFlexi transaction report|obtain AbraFlexi bank transaction report|https://github.com/VitexSoftware/abraflexi-matcher/|
|AbraFlexi Bank statements puller|Stahni bankovní výpisy do AbraFlexi|https://github.com/VitexSoftware/abraflexi-matcher/|
|AbraFlexi Issued invoices Matcher|Ne pouze párovač faktur|https://github.com/VitexSoftware/abraflexi-matcher/|
|Párovač přijatých Faktur|Páruj přijaté faktury s odchozími platbami|https://github.com/VitexSoftware/abraflexi-matcher/|
|Subreg to AbraFlexi|Import Subreg Pricelist into AbraFlexi|https://github.com/Spoje-NET/subreg2abraflexi/|
|Fio Statement Downloader|Download FioBank statements to disk|https://github.com/Spoje-NET/fiobank-statement-downloader|
|Fio transaction report|FioBank transaction report|https://github.com/Spoje-NET/fiobank-statement-downloader|
|RB statement downloader|Download Raiffeisenbank statements in given format|Download your Statements to directory|
|RB transaction report|Raiffeisenbank transaction report|Download your Statements to directory|
|abraflexi-raiffeisenbank|Stahovač bankovních výpisů z Raiffeisen banky|https://github.com/VitexSoftware/abraflexi-raiffeisenbank|
|Redmine do AbraFlexi|Človekohodiny v Redmine do faktury v AbraFlexi|https://github.com/VitexSoftware/Redmine2AbraFlexi/|
|Čistič štítků upomínače|Vymaže štítky dlužníků|https://github.com/VitexSoftware/abraflexi-reminder|
|Přehled pohledávek|Získá neuhrazené faktury|https://github.com/VitexSoftware/abraflexi-reminder|
|Notify Customers|Zasílat inventarizaci|https://github.com/VitexSoftware/abraflexi-reminder|
|Upomínač|Upomínač neuhrazených faktur|https://github.com/VitexSoftware/abraflexi-reminder|
|Realpad do Mailkitu|Synchronizuje kontakty z Realpadu do Mailkitu |https://github.com/Spoje-NET/realpad2mailkit/|

Kompletní seznam naleznete na [stránce projektu](https://www.multiflexi.eu/apps.php).

See the full list of ready-to-run applications within the MultiFlexi platform on the [application list page](https://www.multiflexi.eu/apps.php).

[![MultiFlexi App](https://github.com/VitexSoftware/MultiFlexi/blob/main/doc/multiflexi-app.svg)](https://www.multiflexi.eu/apps.php)
