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
class Zabbix extends \MultiFlexi\Action\Zabbix
{
    public static function logo()
    {
        return 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2NCIgaGVpZ2h0PSI2NCI+PHBhdGggZD0iTTAgMGg2NHY2NEgweiIgZmlsbD0iI2QzMWYyNiIvPjxwYXRoIGQ9Ik0xOC44IDE1LjM4MmgyNi4zOTN2My40MjRsLTIxLjI0IDI2LjAyN2gyMS43NDR2My43ODRIMTguMjkzdi0zLjQzbDIxLjI0LTI2LjAySDE4Ljh6IiBmaWxsPSIjZmZmIi8+PC9zdmc+';
    }

    /**
     * @return \Ease\Embedable
     */
    public function configForm()
    {
        return
                [
                    new \Ease\TWB4\FormGroup(_('Zabbix Server'), new \Ease\Html\InputTextTag('Zabbix[server]'), \Ease\Shared::cfg('ZABBIX_SERVER', 'zabbix.yourcompany.com')),
                    new \Ease\TWB4\FormGroup(_('Hostname'), new \Ease\Html\InputTextTag('Zabbix[hostname]'), \Ease\Shared::cfg('ZABBIX_HOST', 'multiflexi.yourcompany.com')),
                ];
    }

    #[\Override]
    public function initialData(string $mode): array
    {
        $runtemplateConfig = $this->runtemplate->getRuntemplateEnvironment();

        return [
            'key' => $this->defaultKey(),
            'metricsfile' => $runtemplateConfig->getFieldByCode('RESULT_FILE') ? $runtemplateConfig->getFieldByCode('RESULT_FILE')->getValue() : '',
        ];
    }

    /**
     * Generate configuration form inputs for Zabbix action.
     *
     * @param string $prefix Form field prefix
     *
     * @return array Form field(s)
     */
    public function inputs(string $prefix): array
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
