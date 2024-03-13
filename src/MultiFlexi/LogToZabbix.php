<?php

/**
 * Multi Flexi - Send Log message to Zabbix
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023-2024 Vitex Software
 */

declare(strict_types=1);

namespace MultiFlexi;

use MultiFlexi\Zabbix\Request\Packet as ZabbixPacket;
use MultiFlexi\Zabbix\Request\Metric as ZabbixMetric;

/**
 * Send All Log messages to zabbix
 *
 * @author vitex
 */
class LogToZabbix implements \Ease\Logger\Loggingable
{
    /**
     * Saves obejct instace (singleton...).
     */
    private static $instance = null;

    /**
     * @var ZabbixSender Sender Object
     */
    public $sender = null;

    public function __construct()
    {
        $this->sender = new ZabbixSender(\Ease\Shared::cfg('ZABBIX_SERVER'));
    }

    /**
     * Send message to Zabbix
     *
     * @param mixed  $caller
     * @param string $message
     * @param string $type
     */
    public function addToLog($caller, $message, $type = 'message')
    {
        $packet = new ZabbixPacket();
        $me = \Ease\Shared::cfg('ZABBIX_HOST');
        $jsonText = json_encode([
            'stamp' => microtime(),
            'caller' => \Ease\Logger\Message::getCallerName($caller),
            'message' => $message,
            'type' => $type
        ]);
        if ($jsonText) {
            $packet->addMetric((new ZabbixMetric('ease.message', $jsonText))->withHostname($me));
            $this->sender->send($packet);
        } else {
        }
        //system('zabbix_sender -z ' . \Ease\Shared::cfg('ZABBIX_SERVER') . ' -p 10051 -s "' . \Ease\Shared::cfg('ZABBIX_HOST') . '" -k ' . \Ease\Shared::cfg('ZABBIX_FIELD', 'multi.message') . ' -o "' . $message . '"');
    }

    /**
     * Pri vytvareni objektu pomoci funkce singleton (ma stejne parametry, jako
     * konstruktor) se bude v ramci behu programu pouzivat pouze jedna jeho
     * instance (ta prvni).
     *
     * @link http://docs.php.net/en/language.oop5.patterns.html Dokumentace a
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
