<?php

/**
 * Multi Flexi - Application instance editor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2017-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

require_once './init.php';
$oPage->onlyForLogged();
$apps = new \MultiFlexi\Application($oPage->getRequestValue('id', 'int'));

$appJson = $apps->getAppJson();

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $apps->jsonFileName() . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . strlen($appJson));

echo $appJson;
