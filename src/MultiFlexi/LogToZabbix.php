<?php

/**
 * MultiFlexi - Send Log message to Zabbix.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023-2024 Vitex Software
 */

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

use MultiFlexi\Zabbix\Request\Metric as ZabbixMetric;
use MultiFlexi\Zabbix\Request\Packet as ZabbixPacket;

/**
 * Send All Log messages to zabbix.
 *
 * @author vitex
 */
class LogToZabbix implements \Ease\Logger\Loggingable
{
    /**
     * @var ZabbixSender Sender Object
     */
    public ZabbixSender $sender;

    /**
     * Saves obejct instace (singleton...).
     */
    private static $instance;

    public function __construct()
    {
        if (class_exists('ZabbixSender')) {
            $this->sender = new ZabbixSender(\Ease\Shared::cfg('ZABBIX_SERVER'));
        }
    }

    /**
     * Send message to Zabbix.
     *
     * @param mixed  $caller
     * @param string $message
     * @param string $type
     */
    public function addToLog($caller, $message, $type = 'message'): void
    {
        $packet = new ZabbixPacket();
        $me = \Ease\Shared::cfg('ZABBIX_HOST');
        $jsonText = json_encode([
            'stamp' => microtime(),
            'caller' => \Ease\Logger\Message::getCallerName($caller),
            'message' => $message,
            'type' => $type,
        ]);

        if ($jsonText) {
            $packet->addMetric((new ZabbixMetric('ease.message', $jsonText))->withHostname($me));

            if (isset($this->sender)) {
                try {
                    $this->sender->send($packet);
                } catch (Zabbix\Exception\ZabbixNetworkException $exc) {
                    echo $exc->getTraceAsString();
                }
            }
        }

        // system('zabbix_sender -z ' . \Ease\Shared::cfg('ZABBIX_SERVER') . ' -p 10051 -s "' . \Ease\Shared::cfg('ZABBIX_HOST') . '" -k ' . \Ease\Shared::cfg('ZABBIX_FIELD', 'multi.message') . ' -o "' . $message . '"');
    }

    /**
     * Pri vytvareni objektu pomoci funkce singleton (ma stejne parametry, jako
     * konstruktor) se bude v ramci behu programu pouzivat pouze jedna jeho
     * instance (ta prvni).
     *
     * @see http://docs.php.net/en/language.oop5.patterns.html Dokumentace a
     * priklad
     */
    public static function singleton()
    {
        if (!isset(self::$instance)) {
            $class = __CLASS__;
            self::$instance = new $class();
        }

        return self::$instance;
    }
}
