<?php

/**
 * Multi Flexi - Company instance editor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2022 Vitex Software
 */

namespace AbraFlexi\MultiFlexi;

use Ease\Shared;
use AbraFlexi\MultiFlexi\Ui\WebPage;

require_once '../vendor/autoload.php';

session_start();

\Ease\Shared::singleton()->loadConfig(dirname(__DIR__) . '/.env', true);

\Ease\Locale::singleton(null, '../i18n', 'multiflexi');

define('EASE_LOGGER', 'syslog|\AbraFlexi\MultiFlexi\LogToSQL');

$oUser = Shared::user(null, 'AbraFlexi\MultiFlexi\User');
$oPage = new WebPage();
