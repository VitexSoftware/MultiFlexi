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
 * Description of ConfiguredFieldBadges.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class ConfiguredFieldBadges extends ConfigFieldsBadges
{
    public function __construct(int $companyID, int $appID)
    {
        $conffield = new \MultiFlexi\Conffield();
        $appFields = $conffield->appConfigs($appID);
        $configs = new \MultiFlexi\Configuration();
        $appConfigs = $configs->getColumnsFromSQL(['name', 'value'], ['app_id' => $appID]);

        if (!empty($appFields)) {
            foreach ($appFields as $key => $fieldInfo) {
                $appFields[$key]['state'] = \array_key_exists($key, $appConfigs) ? 'success' : 'warning';
            }
        }

        parent::__construct($appFields);

        if (!empty($appFields)) {
            $this->addItem(new \Ease\TWB4\LinkButton('custserviceconfig.php?app_id='.$appID.'&amp;company_id='.$companyID, _('Configure').' '.new \Ease\Html\ImgTag('images/set.svg', _('Set'), ['height' => '30px']), 'success btn-sm'));
        }
    }
}
