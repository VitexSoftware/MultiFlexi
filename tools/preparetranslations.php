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
define('EASE_LOGGER','console');

$apper = new Application();
$texts = $apper->listingQuery()->select(['name','description'], true);
$translateFileContents = "<?php\n\n";

$translateFileContents .= "/**\n";
$translateFileContents .= " * Multi Flexi - Generated i18n translations\n";
$translateFileContents .= " *\n";
$translateFileContents .= " * @author Vítězslav Dvořák <info@vitexsoftware.cz>\n";
$translateFileContents .= " * @copyright  ".date('Y')." Vitex Software\n";
$translateFileContents .= " */\n\n";

$translations = 0;

foreach ($texts as $text){
    if($text['name']){
        $translateFileContents .= "_('".str_replace("'","\'",$text['name'])."');\n";
        $translations++;
    }
    if($text['description']){
        $translateFileContents .= "_('".str_replace("'","\'",$text['description'])."');\n";
        $translations++;
    }
}

file_put_contents('../src/translations.php',$translateFileContents);
$apper->addStatusMessage(sprintf(_('%d language strings exported'),$translations) , 'success');
