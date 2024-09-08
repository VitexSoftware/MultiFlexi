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
 * Description of AppSelector.
 *
 * @author vitex
 */
class AppsSelector extends \Ease\Html\InputTextTag {

    use \Ease\ui\Selectizer;

    public function __construct($identifier = null, $enabled = [], $optionsPage = 'app.php') {
        parent::__construct($identifier, $enabled);

        $properties = [
            'valueField' => 'id',
            'labelField' => 'name',
            'searchField' => ['name', 'description', 'homepage'],
        ];

        $properties['render']['item'] = 'function (item, escape) { return "<div class=container><div class=row> <div class=col-md-2><a href=app.php?id=" + escape(item.id) + "><img height=40 align=left src=\"appimage.php?uuid=" + escape(item.uuid) + "\"></a></div><div class=col-md-7>&nbsp;" + escape(item.name) + "</div><div class=col-md-3><a href=' . $optionsPage . '?id=" + escape(item.id) + "&interval=' . $identifier . ' style=\"font-size: 30px; padding: 5px;\" >🛠️️</a></div> </div></div>" }';
        $properties['render']['option'] = 'function (item, escape) { return "<div><img height=40 align=right src=\"appimage.php?uuid=" + escape(item.uuid) + "\">" + escape(item.name) + "<br><small>" + escape(item.description) + "</small></div>" }';
        $properties['plugins'] = ['remove_button'];

        $this->selectize($properties, self::translateColumns($this->availbleApps(), ['name', 'description'], true));
    }

    public function availbleApps() {
        $apper = new \MultiFlexi\Application();

        return $apper->listingQuery()->select(['id', 'name', 'description', 'homepage', 'uuid'], true)->fetchAll();
    }

    /**
     * Translate strings in specified column using GetText.
     */
    public static function translateColumns(array $data, array $columns, $addslashes = false): array {
        foreach ($data as $rowId => $record) {
            foreach ($columns as $transcol) {
                if (\array_key_exists($transcol, $record)) {
                    if (\strlen($record[$transcol])) {
                        $data[$rowId][$transcol] = $addslashes ? addslashes(_($data[$rowId][$transcol])) : _($data[$rowId][$transcol]);
                    }
                }
            }
        }

        return $data;
    }
}
