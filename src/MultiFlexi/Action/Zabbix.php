<?php

declare(strict_types=1);

/**
 * Multi Flexi -
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Action;

/**
 * Description of TriggerJenkins
 *
 * @author vitex
 */
class Zabbix extends \MultiFlexi\CommonAction
{
    /**
     * Module Caption
     *
     * @return string
     */
    public static function name()
    {
        return _('Zabbix');
    }

    /**
     * Module Description
     *
     * @return string
     */
    public static function description()
    {
        return _('Send Job Output to Zabbix');
    }

    public static function logo()
    {
        return 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2NCIgaGVpZ2h0PSI2NCI+PHBhdGggZD0iTTAgMGg2NHY2NEgweiIgZmlsbD0iI2QzMWYyNiIvPjxwYXRoIGQ9Ik0xOC44IDE1LjM4MmgyNi4zOTN2My40MjRsLTIxLjI0IDI2LjAyN2gyMS43NDR2My43ODRIMTguMjkzdi0zLjQzbDIxLjI0LTI2LjAySDE4Ljh6IiBmaWxsPSIjZmZmIi8+PC9zdmc+';
    }

    /**
     * Is this Action Situable for Application
     *
     * @param Application $app
     */
    public static function usableForApp($app): bool
    {
        return is_object($app);
    }

    /**
     * Perform Action
     */
    public function perform()
    {
        $dataForZabbix = null;
        $metricsfile = $this->getDataValue('metricsfile');
        if (empty($metricsfile)) {
            $dataForZabbix = stripslashes($this->job->getDataValue('stdout'));
        } else {
            if (file_exists($metricsfile)) {
                $dataForZabbix = file_get_contents($metricsfile); //TODO: Use Executor
            } else {
                $this->addStatusMessage(_('Required metrics file not found '), 'warning');
            }
        }
        if ($dataForZabbix) {
            $packet = new \MultiFlexi\Zabbix\Request\Packet();
            $packet->addMetric((new \MultiFlexi\Zabbix\Request\Metric($this->getDataValue('key'), $dataForZabbix))->withHostname($this->getDataValue('hostname')));
            $zabbixSender = new \MultiFlexi\ZabbixSender($this->getDataValue('server'));
            $zabbixSender->send($packet);
        } else {
            $this->addStatusMessage(_('No Data For zabix provided'), 'warning');
        }
    }

    /**
     * Form Inputs
     *
     * @return \Ease\Embedable
     */
    public static function inputs(string $prefix)
    {
        return [
            new \Ease\TWB4\FormGroup(_('Zabbix key'), new \Ease\Html\InputTextTag($prefix . '[Zabbix][key]'), 'custom.key', _('Zabbix Item key')),
            new \Ease\TWB4\FormGroup(_('Metrics file'), new \Ease\Html\InputTextTag($prefix . '[Zabbix][metricsfile]'), '/tmp/metrics.json', _('File with metrics. Leave empty to send stdout'))
        ];
    }

    /**
     *
     * @return \Ease\Embedable
     */
    public static function configForm()
    {
        return
                [
                    new \Ease\TWB4\FormGroup(_('Zabbix Server'), new \Ease\Html\InputTextTag('Zabbix[server]'), \Ease\Shared::cfg('ZABBIX_SERVER', 'zabbix.yourcompany.com')),
                    new \Ease\TWB4\FormGroup(_('Hostname'), new \Ease\Html\InputTextTag('Zabbix[hostname]'), \Ease\Shared::cfg('ZABBIX_HOST', 'multiflexi.yourcompany.com'))
        ];
    }
}
