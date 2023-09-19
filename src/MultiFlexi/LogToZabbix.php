<?php

declare(strict_types=1);
/**
 * Multi Flexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi;

use \MultiFlexi\Zabbix\Request\Packet as ZabbixPacket;
use \MultiFlexi\Zabbix\Request\Metric as ZabbixMetric;

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
        $this->sender = new ZabbixSender(\Ease\Functions::cfg('ZABBIX_SERVER'));
    }

    /**
     * Send message to Zabbix
     * 
     * @param mixed $caller
     * @param string $message
     * @param string $type
     */
    public function addToLog($caller, $message, $type = 'message')
    {
        $packet = new ZabbixPacket();
        $me = \Ease\Functions::cfg('ZABBIX_HOST');
        $packet->addMetric((new ZabbixMetric('ease.message', json_encode(['caller' => \Ease\Logger\Message::getCallerName($caller), 'message' => $message, 'type' => $type])))->withHostname($me));
        $this->sender->send($packet);
        //system('zabbix_sender -z ' . \Ease\Functions::cfg('ZABBIX_SERVER') . ' -p 10051 -s "' . \Ease\Functions::cfg('ZABBIX_HOST') . '" -k ' . \Ease\Functions::cfg('ZABBIX_FIELD', 'multi.message') . ' -o "' . $message . '"');
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
