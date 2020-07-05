<?php

/**
 * Multi FlexiBee Setup - Company instance editor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace FlexiPeeHP\MultiSetup;

use Dotenv\Dotenv;
use Ease\Shared;
use FlexiPeeHP\MultiSetup\Ui\WebPage;

require_once '../vendor/autoload.php';

//$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv = Dotenv::create(dirname(__DIR__));
$dotenv->load();

session_start();

define('EASE_LOGGER', 'syslog|\FlexiPeeHP\MultiSetup\LogToSQL');

$oUser = Shared::user(null, 'FlexiPeeHP\MultiSetup\User');
$oPage = new WebPage();
