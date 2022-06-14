<?php

/**
 * Multi Flexi - Company instance editor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace AbraFlexi\MultiSetup;

use Ease\Shared;
use AbraFlexi\MultiSetup\Ui\WebPage;

require_once '../vendor/autoload.php';

session_start();

\Ease\Shared::singleton()->loadConfig(dirname(__DIR__) . '/.env', true);

\Ease\Locale::singleton(null, '../i18n', 'multiabraflexisetup');

define('EASE_LOGGER', 'syslog|\AbraFlexi\MultiSetup\LogToSQL');

$oUser = Shared::user(null, 'AbraFlexi\MultiSetup\User');
$oPage = new WebPage();
