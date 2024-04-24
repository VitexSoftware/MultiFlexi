<?php

declare(strict_types=1);

/**
 * Multi Flexi -
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of AppSelector
 *
 * @author vitex
 */
class AppsSelector extends \Ease\Html\InputTextTag
{
    use \Ease\ui\Selectizer;

    public function __construct($identifier = null, $enabled = [], $optionsPage = 'app.php')
    {
        parent::__construct($identifier, $enabled);

        $properties = [
            'valueField' => 'id',
            'labelField' => 'name',
            'searchField' => ['name', 'description', 'homepage']
        ];

        $properties['render']['item'] = 'function (item, escape) { return "<div class=container><div class=row> <div class=col-md-2><a href=app.php?id=" + escape(item.id) + "><img height=40 align=left src=\"" + escape(item.image) + "\"></a></div><div class=col-md-7>&nbsp;" + escape(item.name) + "</div><div class=col-md-3><a href=' . $optionsPage . '?id=" + escape(item.id) + "&interval=' . $identifier . ' style=\"font-size: 30px; padding: 5px;\" >🛠️️</a></div> </div></div>" }';
        $properties['render']['option'] = 'function (item, escape) { return "<div><img height=40 align=right src=\"" + escape(item.image) + "\">" + escape(item.name) + "<br><small>" + escape(item.description) + "</small></div>" }';
        $properties['plugins'] = ['remove_button'];

        $this->selectize($properties, self::translateColumns($this->availbleApps(), ['name', 'description']));
    }

    public function availbleApps()
    {
        $apper = new \MultiFlexi\Application();
        return $apper->listingQuery()->select(['id', 'name', 'description', 'homepage', 'image'], true)->fetchAll();
    }

    /**
     * Translate strings in specified column using gettext
     *
     * @param array $data
     * @param array $columns
     *
     * @return array
     */
    public static function translateColumns(array $data, array $columns)
    {
        foreach ($data as $rowId => $record) {
            foreach ($columns as $transcol) {
                if (array_key_exists($transcol, $record)) {
                    if (strlen($record[$transcol])) {
                        $data[$rowId][$transcol] = _($data[$rowId][$transcol]);
                    }
                }
            }
        }
        return $data;
    }
}
