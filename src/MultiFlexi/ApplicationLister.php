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

namespace MultiFlexi;

/**
 * Description of ApplicationLister.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class ApplicationLister extends Application
{
    /**
     * @param array $columns
     *
     * @return array
     */
    public function columns($columns = [])
    {
        return parent::columns([
            ['name' => 'id', 'type' => 'text', 'label' => _('ID'),
                'detailPage' => 'app.php', 'valueColumn' => 'apps.id', 'idColumn' => 'apps.id', ],
            ['name' => 'icon', 'type' => 'text', 'label' => _('Icon'), 'searchable' => false],
            ['name' => 'name', 'type' => 'text', 'label' => _('Name')],
            ['name' => 'description', 'type' => 'text', 'label' => _('Description')],
            ['name' => 'version', 'type' => 'text', 'label' => _('Version')],
            ['name' => 'topics', 'type' => 'text', 'label' => _('Topics')],
            ['name' => 'executable', 'type' => 'text', 'label' => _('Executable')],
            ['name' => 'uuid', 'type' => 'text', 'label' => _('UUID')],
            ['name' => 'ociimage', 'type' => 'text', 'label' => _('OCI Image')],
        ]);
    }

    public function completeDataRow(array $dataRowRaw)
    {
        $dataRow = current(Ui\AppsSelector::translateColumns([$dataRowRaw], ['name', 'description']));
        $dataRow['name'] = '<a title="'.$dataRowRaw['name'].'" href="app.php?id='.$dataRowRaw['id'].'">'.$dataRowRaw['name'].'</a>';
        $dataRow['icon'] = '<a title="'.$dataRowRaw['name'].'" href="app.php?id='.$dataRowRaw['id'].'"><img src="appimage.php?uuid='.$dataRowRaw['uuid'].'" height="50">';

        $topics = new \Ease\Html\DivTag();

        if (empty($dataRow['topics']) === false) {
            foreach (explode(',', $dataRow['topics']) as $topic) {
                $topics->addItem(new \Ease\TWB4\Badge('secondary', $topic));
            }

            $dataRow['topics'] = (string) $topics;
        }

        //        $dataRowRaw['created'] = (new LiveAge((new DateTime($dataRowRaw['created']))))->__toString();

        return parent::completeDataRow($dataRow);
    }
}
