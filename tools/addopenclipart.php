<?php

/**
 * Multi Flexi - Export Language strings
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace MultiFlexi;

require_once '../vendor/autoload.php';
\Ease\Shared::init(['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'], '../.env');
define('EASE_LOGGER', 'console');

function getit($id) {
    if (file_put_contents(__DIR__ . '/../src/images/openclipart/' . $id . '.svg', file_get_contents('https://openclipart.org/download/' . $id))) {
        echo __DIR__ . '/../src/img/openclipart/' . $id . ".svg SAVED\n";
    }
}

if ($argc > 1) {
    if(is_numeric($argv[1])){
        getit($argv[1]);
    } else {
       $path = explode('/', parse_url($argv[1],  PHP_URL_PATH));
       switch ($path[1]) {
           case 'image':
               getit($path[3]);
               break;
           case 'detail':
               getit($path[2]);
               break;

           default:
               getit($path[1]);
               break;
       }
    }
    
} else {
    echo "$argv[0] <openclipartID>\n";
}
