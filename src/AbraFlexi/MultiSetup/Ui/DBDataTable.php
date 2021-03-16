<?php

/**
 * Multi AbraFlexi Setup  - DataTable 
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2020 Vitex Software
 */

namespace AbraFlexi\MultiSetup\Ui;

use Ease\Sand;

/**
 * Description of DataTable
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class DBDataTable extends \Ease\Html\TableTag {

    /**
     * Where to get/put data
     * @var string 
     */
    public $ajax2db = 'ajax2db.php';

    /**
     * Enable Editor ?
     * @var boolean 
     */
    public $rw = false;

    /**
     * Buttons to render on top of the datatable
     * @var array 
     */
    public $buttons = null;

    /**
     * Buttons to show by default
     * @var array 
     */
    public $defaultButtons = ['reload', 'copy', 'excel', 'print', 'pdf', 'pageLength',
        'colvis'];

    /**
     *
     * @var array 
     */
    public $columns = null;

    /**
     * Add footer columns
     * @var boolean 
     */
    public $showFooter = false;

    /**
     *
     * @var \DBFinance\Engine
     */
    public $engine = null;

    /**
     *
     * @var handle 
     */
    public $rndId;

    /**
     * 
     * @param \DBFinance\Engine $engine
     * @param array $properties
     */
    public function __construct($engine = null, $properties = array()) {
        $this->engine = $engine;
        $this->ajax2db = $this->dataSourceURI($engine);
        $this->columnDefs = $engine->columnDefs();

        parent::__construct(null,
                ['class' => 'display', 'style' => 'width: 100%']);

        $gridTagID = $this->setTagId($engine->getObjectName());
        $this->columns = $this->prepareColumns($engine->getGetDataTableColumns());

//        $this->includeJavaScript('assets/datatables.js');
//        $this->includeCss('assets/datatables.css');
//        $this->includeJavaScript('js/cache.js');
//        $this->includeJavaScript('js/datatablerenderutils.js');

        $this->includeJavaScript('js/jquery.dataTables.js');
        $this->includeJavaScript('js/dataTables.bootstrap4.js');
        $this->includeCss('css/dataTables.bootstrap4.css');



//        $this->includeJavaScript('assets/DataTables-1.10.19/js/jquery.dataTables.min.js');
//        $this->includeJavaScript('assets/DataTables-1.10.19/js/dataTables.bootstrap.min.js');
//        $this->includeJavaScript('assets/Select-1.3.0/js/dataTables.select.min.js');
//        $this->includeCss('assets/DataTables-1.10.19/css/dataTables.bootstrap.min.css');
//        $this->includeCss('assets/Select-1.3.0/css/select.bootstrap.min.css');
//
//        $this->includeJavaScript('assets/ColReorder-1.5.0/js/dataTables.colReorder.min.js');
//        $this->includeCss('assets/ColReorder-1.5.0/css/colReorder.bootstrap.min.css');
//
//        $this->includeJavaScript('assets/Responsive-2.2.2/js/dataTables.responsive.min.js');
//        $this->includeJavaScript('assets/Responsive-2.2.2/js/responsive.bootstrap.min.js');
        $this->includeJavaScript('js/selectize.min.js');
        $this->includeCss('css/selectize.css');
        $this->includeCss('css/selectize.bootstrap4.css');


        $this->setTagClass('table table-bordered');

        $this->includeJavaScript('assets/Buttons-1.5.6/js/dataTables.buttons.js');
        $this->includeJavaScript('assets/Buttons-1.5.6/js/buttons.bootstrap.min.js');
        $this->includeCss('assets/Buttons-1.5.6/css/buttons.bootstrap.min.css');
//
//        $this->includeJavaScript('assets/JSZip-2.5.0/jszip.min.js');
//        $this->includeJavaScript('assets/pdfmake-0.1.36/pdfmake.min.js');
//        $this->includeJavaScript('assets/pdfmake-0.1.36/vfs_fonts.js');
        $this->includeJavaScript('assets/Buttons-1.5.6/js/buttons.html5.min.js');
        $this->includeJavaScript('assets/Buttons-1.5.6/js/buttons.print.min.js');
        $this->includeJavaScript('assets/Buttons-1.5.6/js/buttons.colVis.min.js');
//        $this->includeCss('assets/RowGroup-1.1.0/css/rowGroup.bootstrap.min.css');
//        $this->includeJavaScript('assets/RowGroup-1.1.0/js/rowGroup.bootstrap.min.js');
//        $this->includeJavaScript('assets/RowGroup-1.1.0/js/dataTables.rowGroup.min.js');
//        $this->includeCss('https://nightly.datatables.net/rowgroup/css/rowGroup.dataTables.css');
//        $this->includeJavaScript('https://nightly.datatables.net/rowgroup/js/dataTables.rowGroup.js');
//        $this->includeJavaScript('assets/moment-with-locales.js');
//        $this->includeJavaScript('//cdn.datatables.net/plug-ins/1.10.19/sorting/datetime-moment.js');


        $this->addJavaScript('$.fn.dataTable.ext.buttons.reload = {
    text: \'' . _('Reload') . '\',
    action: function ( e, dt, node, config ) {
        dt.ajax.reload();
    }
};');


        $this->addJavaScript('
$("#gridFilter' . $gridTagID . '").hide( );
$.fn.dataTable.ext.buttons.filter' . $gridTagID . ' = {
    text: \'' . _('Filter') . '\',
    action: function ( e, dt, node, config ) {
        $("#gridFilter' . $gridTagID . '").appendTo( $("#' . $gridTagID . '_filter") );            
        $("#gridFilter' . $gridTagID . '").toggle();
    }
};');

        $this->defaultButtons[] = 'filter' . $gridTagID;

        if (array_key_exists('buttons', $properties)) {
            if ($properties['buttons'] === false) {
                $this->defaultButtons = [];
            } else {
                foreach ($properties['buttons'] as $function) {
                    $this->addButton($function);
                }
            }
        }

        foreach ($this->defaultButtons as $button) {
            $this->addButton($button);
        }

        $this->includeJavaScript('js/advancedfilter.js');
    }

    /**
     * Only columns with date type
     * 
     * @param array $columns
     * 
     * @return array
     */
    public static function getDateColumns($columns) {
        $dateColumns = [];
        foreach ($columns as $columnInfo) {
            if ($columnInfo['type'] == 'date') {
                $dateColumns[$columnInfo['name']] = $columnInfo['label'];
            }
        }
        return $dateColumns;
    }

    /**
     * Convert Ease Columns to DataTables Format
     */
    public function prepareColumns($easeColumns) {
        foreach ($easeColumns as $column) {

            switch ($column['type']) {
                case '':
                case 'email':
                case 'float':
                case 'currency':
                case 'price':
                case 'int':
                    $column['type'] = 'text';
                    break;
//                case 'currency':
//                    $column['type'] = 'mask';
//                    $column['mask'] = '#,##0';
                    break;

                case 'boolean':
                    $column['type'] = 'checkbox';
                    $column['separator'] = "|";
                    $column['options'] = [['label' => '', 'value' => 1]];
                    break;
                case 'ckeditor':
                case 'ckeditorClassic':
                    $this->addCSS('.modal-dialog { width: 90%; }');
                    break;

                case 'display':
                case 'checkbox':
                case 'password':
                case 'hidden':
                case 'radio':
                case 'readonly':
                case 'select':
                case 'selectize':
                case 'text':
                case 'textarea':
                case 'upload':
                case 'uploadMany':
                    break;
                case 'datetime':
                case 'date':
                    break;
                default :
                    $this->addStatusMessage('Uknown column ' . $column['name'] . ' type ' . $column['type']);
                    break;
            }

//            unset($column['type']);
            $dataTablesColumns[] = $column;
        }
        return $dataTablesColumns;
    }

    public function finalize() {
        $this->addRowHeaderColumns(self::columnsToHeader($this->columns));
        $this->addItem(new FilterDialog($this->getTagID(), $this->columns));
        $this->addJavascript($this->javaScript($this->columns));
        if ($this->showFooter) {
            $this->addFooter();
        }

        parent::finalize();
    }

    /**
     * 
     * @return type
     */
    public static function getUri() {
        $uri = parent::getUri();
        return substr($uri, -1) == '/' ? $uri . 'index.php' : $uri;
    }

    /**
     * Prepare DataSource URI 
     * 
     * @param \DBFinance\Engine $engine
     * 
     * @return string Data Source URI
     */
    public function dataSourceURI($engine) {
        $conds = ['class' => get_class($engine)];
        if (!is_null($engine->filter)) {
            $conds = array_merge($engine->filter, $conds);
        }
        return $this->ajax2db . '?' . http_build_query($conds);
    }

    /**
     * Add TOP button
     * 
     * @param string $function create|edit|remove
     */
    public function addButton($function) {
        $this->buttons[] = '{extend: "' . $function . '"}';
    }

    /**
     * 
     */
    public function addCustomButton($caption,
            $callFunction = "alert( 'Button activated' );") {
        $this->buttons[] = '{
                text: \'' . $caption . '\',
                action: function ( e, dt, node, config ) {
                    ' . $callFunction . '
                }            
}';
    }

    /**
     * 
     * @param arrays $columns
     * 
     * @return string
     */
    public function javaScript($columns) {
        $tableID = $this->getTagID();
        return $this->engine->preTableCode($tableID) . '
//    $.fn.dataTable.moment(\'DD. MM. YYYY\');            
//    $.fn.dataTable.moment(\'YYYY-MM-DD HH:mm:ss\');            
    var ' . $tableID . ' = $(\'#' . $tableID . '\').DataTable( {
        ' . $this->footerCallback($this->engine->foterCallback($tableID)) . '
        "dom": "Bfrtip",
        "colReorder": true,
        "stateSave": true,
        "bStateSave": true,
        "responsive": true,
        "processing": true,
        "serverSide": true,
        "lengthMenu": [[10, 25, 50, 100, 200, 500, -1], [10, 25, 50, 100 ,200 ,500 , "' . _('All pages') . '"]],
        "language": {
                "url": "assets/i18n/Czech.lang"
        },
        ' . $this->engine->tableCode($tableID) . '
        ajax: { "url": "' . $this->ajax2db . '", "type": "POST" },
        //ajax: loadDataTableData(data, callback, settings),            
        ' . $this->engine->columnDefs() . '
        columns: [
            ' . self::getColumnsScript($columns) . '
        ],
        select: true
        ' . ( $this->buttons ? ',        buttons: [ ' . \Ease\Part::partPropertiesToString($this->buttons) . ']' : '') . '
    } );

    ' . $this->engine->postTableCode($tableID) . '
            $(\'.tablefilter\').change( function() { ' . $tableID . '.draw(); } );
';
//    $("#'.$tableID.'_filter").css(\'border\', \'1px solid red\');
//setInterval( function () { '.$tableID.'.ajax.reload( null, false ); }, 30000 );        
    }

    //    '.self::columnIndexNames($columns,$tableID).'
    public static function columnIndexNames($columns, $of) {
        $colsCode[] = 'var tableColumnIndex = [];';
        foreach (Sand::reindexArrayBy($columns, 'name') as $colName => $columnInfo) {
            $colsCode[] = "tableColumnIndex['" . $colName . "'] = " . $of . ".column('" . $colName . ":name').index();";
        }
        return implode("\n", $colsCode);
    }

    /**
     * Gives You Columns JS 
     * 
     * @param array $columns
     * 
     * @return string
     */
    public static function getColumnsScript($columns) {
        $parts = [];
        foreach ($columns as $properties) {
            $name = $properties['name'];
            $properties['valueColumn'] = array_key_exists('valueColumn', $properties) ? addslashes($properties['valueColumn']) : $properties['name'];
            $properties['data'] = $name;
            $parts[] = '{' . \Ease\Part::partPropertiesToString($properties) . '}';
        }
        return implode(", \n", $parts);
    }

    /**
     * Engine columns to Table Header columns format
     * 
     * @param array $columns
     * 
     * @return array
     */
    public static function columnsToHeader($columns) {
        foreach ($columns as $properties) {
            if (array_key_exists('hidden', $properties) && ($properties['hidden'] == true)) {
                continue;
            }
            if (isset($properties['label'])) {
                $header[$properties['name']] = $properties['label'];
            } else {
                $header[$properties['name']] = $properties['name'];
            }
        }
        return $header;
    }

    /**
     * Define footer Callback code
     * 
     * @param string $initialContent
     * 
     * @return string
     */
    public function footerCallback($initialContent = null) {
        if (empty($initialContent)) {
            $foterCallBack = '';
        } else {
            $foterCallBack = '
        "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api(), data;
 
            // Remove the formatting to get integer data for summation
            var intVal = function ( i ) {
                return typeof i === \'string\' ?
                    i.replace(/[\$,]/g, \'\')*1 :
                    typeof i === \'number\' ?
                        i : 0;
            };
            ' . $initialContent . '
        },
';
        }

        return $foterCallBack;
    }

    /**
     * 
     */
    public function addFooter() {
        foreach (current($this->tHead->getContents())->getContents() as $column) {
            $columns[] = '';
        }
        unset($columns['id']);
        $this->addRowFooterColumns($columns);
    }

}
