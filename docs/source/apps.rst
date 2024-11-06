Applications
============

.. toctree::
   :maxdepth: 2

   abraflexi-checker
   daily-digest
   weekly-digest
   monthly-digest
   email-sender
   kimai2abraflexi
   email-importer
   show-unsent
   abraflexi-send
   redmine2abraflexi
   yearly-digest
   multiflexi-probe
   alltime-digest
   abraflexi-bulk-mailer
   subreg-to-abraflexi
   realpad-to-mailkit
   abraflexi-revolut-statements-import
   abraflexi-transaction-report
   abraflexi-bank-statements-puller
   abraflexi-issued-invoices-matcher
   recieved-invoices-matcher
   contract-to-invoices
   clear-reminder-labels
   debts-overview
   notify-customers
   sms-input
   pohoda-checker
   abraflexi-benchmark
   abraflexi-copy
   price-fixer
   subreg-credit-check
   subreg-for-abraflexi
   raiffeisenbank-transactions-importer-for-abraflexi
   raiffeisenbank-statement-importer-for-abraflexi
   discomp2abraflexi
   pohoda-transaction-report
   reminder
   rb-balance
   raiffeisenbank-statements-for-pohodasql
   rb-statement-downloader
   raiffeisenbank-statements-for-pohoda
   raiffeisenbank-statements-for-pohodasql-sharepoint
   fio-statement-downloader
   rb-statement-mailer
   fio-statement-mailer
   fio-transaction-report
   rb-transaction-report
   import-raiffeisen-bank-statements-to-abraflexi-events
.. contents::


MultiFlexi Application Requirements
===================================

MultiFlexi can execute applications written in any programming language. The application must read its configuration from environment variables. The output can be directed to stdout/stderr and stored. Additionally, it is possible to generate a JSON file and pass it to Zabbix.

Applications Overview
=====================

MultiFlexi offers a variety of applications to enhance your accounting and business processes. Below is a brief overview of the available applications:

.. list-table:: Available Applications
    :header-rows: 1

    * - Name
      - Description
      - HomePage
    * - AbraFlexi Checker
      - Check AbraFlexi availability
      - https://github.com/VitexSoftware/php-abraflexi-config
    * - Daily Digest
      - Digest for your Accounting every day
      - https://github.com/VitexSoftware/AbraFlexi-Digest/
    * - Weekly Digest
      - Digest for your Accounting every week
      - https://github.com/VitexSoftware/AbraFlexi-Digest/
    * - Monthly Digest
      - Digest for your Accounting every month
      - https://github.com/VitexSoftware/AbraFlexi-Digest/
    * - Email Sender
      - Send unsent documents with attachments
      - https://github.com/VitexSoftware/abraflexi-mailer/
    * - Kimai2AbraFlexi
      - Convert your Kiami timesheets into AbraFlexi invoices
      - https://github.com/VitexSoftware/Kimai2AbraFlexi
    * - Email Importer
      - Načítá doklady z mailboxu do FlexiBee
      - https://github.com/VitexSoftware/AbraFlexi-email-importer
    * - Show Unsent
      - Show Unsent documents
      - https://github.com/VitexSoftware/abraflexi-mailer/
    * - AbraFlexi send
      - Simple trigger AbraFlexi to send all unsent documents in Issued Invoices Agenda
      - https://github.com/VitexSoftware/abraflexi-mailer/
    * - Redmine2AbraFlexi
      - Redmine WorkHours to AbraFlexi invoice
      - https://github.com/VitexSoftware/Redmine2AbraFlexi/
    * - Yearly Digest
      - Generate AbraFlexi report every year
      - https://github.com/VitexSoftware/AbraFlexi-Digest/
    * - MultiFlexi Probe
      - Task launcher testing tool
      - https://github.com/VitexSoftware/MultiFlexi
    * - AllTime Digest
      - Digest for your Accounting from begin to now
      - https://github.com/VitexSoftware/AbraFlexi-Digest/
    * - AbraFlexi Bulk Mailer
      - By Query select recipients from Addressbook and send mail based on template
      - https://github.com/VitexSoftware/abraflexi-mailer/
    * - Subreg to AbraFlexi
      - Import Subreg Pricelist into AbraFlexi
      - https://github.com/Spoje-NET/subreg2abraflexi/
    * - Realpad to Mailkit
      - Synchronize Realpad Contacts into Mailkit
      - https://github.com/Spoje-NET/realpad2mailkit/
    * - AbraFlexi Revolut statements import
      - Import Revolut bank statements into AbraFlexi
      - https://github.com/VitexSoftware/AbraFlexi-Revolut
    * - AbraFlexi transaction report
      - Obtain AbraFlexi bank transaction report
      - https://github.com/VitexSoftware/abraflexi-matcher/
    * - AbraFlexi Bank statements puller
      - Pull bank statements into AbraFlexi
      - https://github.com/VitexSoftware/abraflexi-matcher/
    * - AbraFlexi Issued invoices Matcher
      - Not even Invoice matcher
      - https://github.com/VitexSoftware/abraflexi-matcher/
    * - Recieved invoices Matcher
      - Match received invoices with outcoming payments
      - https://github.com/VitexSoftware/abraflexi-matcher/
    * - Contract to Invoices
      - Trigger AbraFlexi contracts to generate invoices
      - https://github.com/VitexSoftware/abraflexi-contract-invoices
    * - Clear Reminder Labels
      - Clear Debtor's labels
      - https://github.com/VitexSoftware/abraflexi-reminder
    * - Debts overview
      - Gather unsettled invoices
      - https://github.com/VitexSoftware/abraflexi-reminder
    * - Notify Customers
      - Send inventarization
      - https://github.com/VitexSoftware/abraflexi-reminder
    * - SMS input
      - E5180s-22 SMS to Json receiver
      - https://github.com/Spoje-NET/Sms-Input
    * - Pohoda Checker
      - Check Stormware mServer availability
      - https://github.com/Spoje-NET/pohoda-client-checker
    * - AbraFlexi Benchmark
      - AbraFlexi Server Benchmark
      - https://github.com/VitexSoftware/AbraFlexi-Tools
    * - AbraFlexi Copy
      - Copy Company data between two AbraFlexi servers
      - https://github.com/VitexSoftware/AbraFlexi-Tools
    * - Price Fixer
      - Bundle product price updater for AbraFlexi
      - https://github.com/VitexSoftware/AbraFlexi-pricefixer
    * - Subreg credit Check
      - Check Subreg Credit for Customers in AbraFlexi
      - https://github.com/Spoje-NET/subreg2abraflexi/
    * - SubReg for AbraFlexi
      - Import Subreg Pricelist into AbraFlexi
      - https://github.com/Spoje-NET/subreg2abraflexi/
    * - Raiffeisenbank Transactions importer for AbraFlexi
      - Raiffeisen Bank Transaction puller
      - https://github.com/VitexSoftware/abraflexi-raiffeisenbank
    * - Raiffeisenbank Statement importer for AbraFlexi
      - Raiffeisen Bank Statements puller
      - https://github.com/VitexSoftware/abraflexi-raiffeisenbank
    * - discomp2abraflexi
      - Import Pricelist from Discomp to AbraFlexi
      - https://github.com/Spoje-NET/discomp2abraflexi
    * - Pohoda Transaction Report
      - Check Bank Transactions in Stormware Pohoda
      - https://github.com/Spoje-NET/pohoda-client-checker
    * - Reminder
      - Remind unsettled invoices
      - https://github.com/VitexSoftware/abraflexi-reminder
    * - RB Balance
      - Raiffeisenbank Balance check
      - https://github.com/Spoje-NET/raiffeisenbank-statement-tools
    * - Raiffeisenbank statements for PohodaSQL
      - Import Raiffeisenbank statements into Pohoda
      - https://github.com/Spoje-NET/pohoda-raiffeisenbank
    * - RB statement downloader
      - Download Raiffeisenbank statements in given format
      - https://github.com/Spoje-NET/raiffeisenbank-statement-tools
    * - Raiffeisenbank statements for Pohoda
      - Import Raiffeisenbank statements into Pohoda
      - https://github.com/Spoje-NET/pohoda-raiffeisenbank
    * - Raiffeisenbank statements for PohodaSQL+Sharepoint
      - Import Raiffeisenbank statements into PohodaSQL and store in Sharepoint
      - https://github.com/Spoje-NET/pohoda-raiffeisenbank
    * - Fio Statement Downloader
      - Download FioBank statements to disk
      - https://github.com/Spoje-NET/fiobank-statement-tools
    * - RB statement mailer
      - Download Raiffeisenbank statements in given format and send it via email
      - https://github.com/Spoje-NET/raiffeisenbank-statement-tools
    * - Fio Statement Mailer
      - Send FioBank statements to mail recipient
      - https://github.com/Spoje-NET/fiobank-statement-tools
    * - Fio transaction report
      - FioBank transaction report
      - https://github.com/Spoje-NET/fiobank-statement-tools
    * - RB transaction report
      - Raiffeisenbank transaction report
      - https://github.com/Spoje-NET/raiffeisenbank-statement-tools
    * - Import Raiffeisen bank Statements to AbraFlexi Events
      - Download Raiffeisenbank PDF Statements and import them to AbraFlexi events
      - https://github.com/VitexSoftware/abraflexi-raiffeisenbank
