<?php

//use DataTables\Editor,
//    DataTables\Editor\Field,
//    DataTables\Editor\Format,
//    DataTables\Editor\Mjoin,
//    DataTables\Editor\Options,
//    DataTables\Editor\Upload,
//    DataTables\Editor\Validate,
//    DataTables\Editor\ValidateOptions;


/**
 * Multi Flexi - DataTable feeder.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

require_once './init.php';
$oPage->onlyForLogged();
header('Content-Type: application/json');
$class = \Ease\WebPage::getRequestValue('class');
/**
 * @var \MultiFlexi\Engine Data Source
 */
$engine = new $class();
// DataTables PHP library
//include( './lib/DataTables.php' );
//if ($oPage->getRequestValue('columns')) {
echo json_encode($engine->getAllForDataTable($_REQUEST));
//} else {
//    $editor = new DataTableSaver($db, $engine);
//    $editor->process($_POST)->json();
//}
