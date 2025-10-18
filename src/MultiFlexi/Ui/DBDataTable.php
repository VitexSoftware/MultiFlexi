<?php

declare(strict_types=1);

/**
 * This file is part of the MultiFlexi package
 *
 * https://multiflexi.eu/
 *
 * (c) Vítězslav Dvořák <http://vitexsoftware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MultiFlexi\Ui;

/**
 * Description of DataTable.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 *
 * @no-named-arguments
 */
class DBDataTable extends \Ease\Html\TableTag
{
    /**
     * Where to get/put data.
     */
    public string $ajax2db = 'ajax2db.php';

    /**
     * Enable Editor ?
     */
    public bool $rw = false;

    /**
     * Buttons to render on top of the datatable.
     */
    public array $buttons;

    /**
     * Buttons to show by default.
     */
    public array $defaultButtons = ['reload', 'copy', 'excel', 'print', 'pdf', 'pageLength', 'colvis'];
    public array $columns;

    /**
     * Add footer columns.
     */
    public bool $showFooter = false;
    public $engine;
    public handle $rndId;
    private $columnDefs;

    /**
     * Common database engine.
     *
     * @param DatabaseEngine $engine
     * @param array          $properties
     */
    public function __construct($engine = null, $properties = [])
    {
        $this->engine = $engine;
        $this->ajax2db = $this->dataSourceURI($engine);
        $this->columnDefs = $engine->columnDefs();
        parent::__construct(
            null,
            ['class' => 'display', 'style' => 'width: 100%'],
        );
        $gridTagID = $this->setTagId($engine->getObjectName());
        $this->columns = $this->prepareColumns($engine->getGetDataTableColumns());
        //        $this->includeJavaScript('assets/datatables.js');
        //        $this->includeCss('assets/datatables.css');
        //        $this->includeJavaScript('js/cache.js');
        //        $this->includeJavaScript('js/datatablerenderutils.js');

        $this->includeJavaScript('js/jquery.dataTables.js');
        $this->includeJavaScript('js/dataTables.bootstrap4.js');
        $this->includeCss('css/dataTables.bootstrap4.css');
        //$this->includeCss('css/buttons.bootstrap4.css');
        
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
        $this->includeJavaScript('assets/Buttons-1.5.6/js/buttons.bootstrap4.min.js');
        $this->includeCss('assets/Buttons-1.5.6/css/buttons.bootstrap4.min.css');
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

        $this->addJavaScript(<<<'EOD'
$.fn.dataTable.ext.buttons.reload = {
    text: '
EOD._('Reload').<<<'EOD'
',
    action: function ( e, dt, node, config ) {
        dt.ajax.reload();
    }
};
EOD);
        $this->addJavaScript(<<<'EOD'

$("#gridFilter
EOD.$gridTagID.<<<'EOD'
").hide( );
$.fn.dataTable.ext.buttons.filter
EOD.$gridTagID.<<<'EOD'
 = {
    text: '
EOD._('Filter').<<<'EOD'
',
    action: function ( e, dt, node, config ) {
        $("#gridFilter
EOD.$gridTagID.'").appendTo($("#'.$gridTagID.<<<'EOD'
_filter") );
        $("#gridFilter
EOD.$gridTagID.<<<'EOD'
").toggle();
    }
};
EOD);
        $this->defaultButtons[] = 'filter'.$gridTagID;

        if (\array_key_exists('buttons', $properties)) {
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
     * Only columns with date type.
     *
     * @param array $columns
     *
     * @return array
     */
    public static function getDateColumns($columns)
    {
        $dateColumns = [];

        foreach ($columns as $columnInfo) {
            if ($columnInfo['type'] === 'date') {
                $dateColumns[$columnInfo['name']] = $columnInfo['label'];
            }
        }

        return $dateColumns;
    }

    /**
     * Convert Ease Columns to DataTables Format.
     *
     * @param mixed $easeColumns
     */
    public function prepareColumns($easeColumns)
    {
        $dataTablesColumns = [];

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
                    $column['separator'] = '|';
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

                default:
                    $this->addStatusMessage('Uknown column '.$column['name'].' type '.$column['type']);

                    break;
            }

            //            unset($column['type']);
            $dataTablesColumns[] = $column;
        }

        return $dataTablesColumns;
    }

    public function finalize(): void
    {
        $this->addRowHeaderColumns(self::columnsToHeader($this->columns));
        $this->addItem(new FilterDialog($this->getTagID(), $this->columns));
        $this->addJavascript($this->javaScript($this->columns));

        if ($this->showFooter) {
            $this->addFooter();
        }

        parent::finalize();
    }

    /**
     * @return type
     */
    public static function getUri()
    {
        $uri = parent::getUri();

        return substr($uri, -1) === '/' ? $uri.'index.php' : $uri;
    }

    /**
     * Prepare DataSource URI.
     *
     * @param \DBFinance\Engine $engine
     *
     * @return string Data Source URI
     */
    public function dataSourceURI($engine)
    {
        $conds = ['class' => $engine::class];

        if (null !== $engine->filter) {
            $conds = array_merge($engine->filter, $conds);
        }

        return $this->ajax2db.'?'.http_build_query($conds);
    }

    /**
     * Add TOP button.
     *
     * @param string $function create|edit|remove
     */
    public function addButton($function): void
    {
        $this->buttons[] = '{extend: "'.$function.'"}';
    }

    /**
     * @param mixed $caption
     * @param mixed $callFunction
     */
    public function addCustomButton(
        $caption,
        $callFunction = "alert( 'Button activated' );",
    ): void {
        $this->buttons[] = <<<'EOD'
{
                text: '
EOD.$caption.<<<'EOD'
',
                action: function ( e, dt, node, config ) {

EOD.$callFunction.<<<'EOD'

                }
}
EOD;
    }

    /**
     * @param arrays $columns
     *
     * @return string
     */
    public function javaScript($columns)
    {
        $tableID = $this->getTagID();

        return $this->engine->preTableCode($tableID).<<<'EOD'

//    $.fn.dataTable.moment('DD. MM. YYYY');
//    $.fn.dataTable.moment('YYYY-MM-DD HH:mm:ss');
    var
EOD.$tableID.' = $(\'#'.$tableID.<<<'EOD'
').DataTable( {

EOD.$this->footerCallback($this->engine->foterCallback($tableID)).<<<'EOD'

        "dom": "Bfrtip",
        "colReorder": true,
        "stateSave": true,
        "bStateSave": true,
        "responsive": true,
        "processing": true,
        "serverSide": true,
        "lengthMenu": [[10, 25, 50, 100, 200, 500, -1], [10, 25, 50, 100 ,200 ,500 , "
EOD._('All pages').<<<'EOD'
"]],
        "language": {
                "url": "assets/i18n/Czech.lang"
        },

EOD.$this->engine->tableCode($tableID).<<<'EOD'

        ajax: { "url": "
EOD.$this->ajax2db.<<<'EOD'
", "type": "POST" },
        //ajax: loadDataTableData(data, callback, settings),

EOD.$this->engine->columnDefs().<<<'EOD'

        columns: [

EOD.self::getColumnsScript($columns).<<<'EOD'

        ],
        select: true

EOD.($this->buttons ? ',        buttons: [ '.\Ease\Part::partPropertiesToString($this->buttons).']' : '').<<<'EOD'

    } );


EOD.$this->engine->postTableCode($tableID).<<<'EOD'

            $('.tablefilter').change( function() {
EOD.$tableID.<<<'EOD'
.draw(); } );

EOD;
        //    $("#'.$tableID.'_filter").css(\'border\', \'1px solid red\');
        // setInterval( function () { '.$tableID.'.ajax.reload( null, false ); }, 30000 );
    }

    //    '.self::columnIndexNames($columns,$tableID).'
    public static function columnIndexNames($columns, $of)
    {
        $colsCode[] = 'var tableColumnIndex = [];';

        foreach (\Ease\Functions::reindexArrayBy($columns, 'name') as $colName => $columnInfo) {
            $colsCode[] = "tableColumnIndex['".$colName."'] = ".$of.".column('".$colName.":name').index();";
        }

        return implode("\n", $colsCode);
    }

    /**
     * Gives You Columns JS.
     *
     * @param array $columns
     *
     * @return string
     */
    public static function getColumnsScript($columns)
    {
        $parts = [];

        foreach ($columns as $properties) {
            $name = $properties['name'];
            $properties['valueColumn'] = \array_key_exists('valueColumn', $properties) ? addslashes($properties['valueColumn']) : $properties['name'];
            $properties['data'] = $name;
            $parts[] = '{'.\Ease\Part::partPropertiesToString($properties).'}';
        }

        return implode(", \n", $parts);
    }

    /**
     * Engine columns to Table Header columns format.
     *
     * @param array $columns
     *
     * @return array
     */
    public static function columnsToHeader($columns)
    {
        $header = [];

        foreach ($columns as $properties) {
            if (\array_key_exists('hidden', $properties) && ($properties['hidden'] === true)) {
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
     * Define footer Callback code.
     *
     * @param string $initialContent
     *
     * @return string
     */
    public function footerCallback($initialContent = null)
    {
        if (empty($initialContent)) {
            $foterCallBack = '';
        } else {
            $foterCallBack = <<<'EOD'

        "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api(), data;

            // Remove the formatting to get integer data for summation
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    i.replace(/[\$,]/g, '')*1 :
                    typeof i === 'number' ?
                        i : 0;
            };

EOD.$initialContent.<<<'EOD'

        },

EOD;
        }

        return $foterCallBack;
    }

    public function addFooter(): void
    {
        foreach (current($this->tHead->getContents())->getContents() as $column) {
            $columns[] = '';
        }

        unset($columns['id']);
        $this->addRowFooterColumns($columns);
    }
}
