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

$companys = (new \FlexiPeeHP\MultiSetup\Company())->listingQuery()->select('flexibees.*')->leftJoin('flexibees ON flexibees.id = company.flexibee');



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
        echo $envName . '=' . $sqlValue . "\n";
        putenv($envName . '=' . $sqlValue);
    }


    $command = 'flexibee-client-config-checker';
     system($command);
}
