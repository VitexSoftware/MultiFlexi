<?php

/**
 * Multi FlexiBee Setup - Company instance editor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace FlexiPeeHP\MultiSetup;

use Dotenv\Dotenv;
use FlexiPeeHP\MultiSetup\Ui\WebPage;

require_once '../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

define('EASE_LOGGER', 'console');

$companer = new \FlexiPeeHP\MultiSetup\Company();
$companys = $companer->listingQuery()->select('flexibees.*')->leftJoin('flexibees ON flexibees.id = company.flexibee');



foreach ($companys as $company) {

    $envNames = [
        'FLEXIBEE_URL' => $company['url'],
        'FLEXIBEE_LOGIN' => $company['user'],
        'FLEXIBEE_PASSWORD' => $company['password'],
        'FLEXIBEE_COMPANY' => $company['company'],
//        ''=>$company[''],
//        ''=>$company[''],
//        ''=>$company[''],
//        ''=>$company[''],
//        ''=>$company[''],
    ];

    foreach ($envNames as $envName => $sqlValue) {
//        echo $envName . '=' . $sqlValue . "\n";
        putenv($envName . '=' . $sqlValue);
    }

    $ap2c = new AppToCompany(['company_id' => $company['id']]);
    if (empty($ap2c->getData())) {
        $companer->addStatusMessage(sprintf(_('No applications enabled for %s'), $company['nazev']), 'warning');
    } else {
        foreach ($ap2c->getData() as $servData) {
            $app = new Application(intval($servData['app_id']));
            $exec = $app->getDataValue('executable');
            $app->addStatusMessage('begin' . $exec . '@' . $company['nazev']);
            $app->addStatusMessage(shell_exec($exec), 'debug');
            $app->addStatusMessage('end' . $exec . '@' . $company['nazev']);
        }
    }
}
