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

namespace MultiFlexi\Ui\Action;

/**
 * Zabbix Action UI Class.
 *
 * @author vitex
 */
class Zabbix
{
    /**
     * Generate configuration form inputs for Zabbix action.
     *
     * @param string $prefix Form field prefix
     *
     * @return array Form field(s)
     */
    public static function inputs(string $prefix): array
    {
        $keyPrefix = new \Ease\Html\DivTag('zabbix_action-', ['class' => 'input-group-text']);

        $prepend = new \Ease\Html\DivTag($keyPrefix, ['class' => 'input-group-prepend']);

        $input = new \Ease\Html\DivTag($prepend, ['class' => 'input-group mb-2']);

        $input->addItem(
            new \Ease\Html\InputTextTag($prefix.'[Zabbix][key]', null, ['class' => 'form-control']),
        );

        return [
            new \Ease\TWB4\FormGroup(_('Zabbix key'), $input, '{COMPANY_CODE}-{APP_CODE}-{RUNTEMPLATE_ID}-data', _('Zabbix Item key')),
            new \Ease\TWB4\FormGroup(_('Metrics file'), new \Ease\Html\InputTextTag($prefix.'[Zabbix][metricsfile]'), '/tmp/metrics.json', _('File with metrics. Leave empty to send stdout')),
        ];
    }
}
