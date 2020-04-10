<?php

/**
 * Multi FlexiBee Setup - Phinx database adapter.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */
include_once './vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$db = new \Ease\SQL\Engine(null, ['database' => 'db/' . basename(getenv('DB_DATABASE'))]);

return array('environments' =>
    array(
        'default_database' => 'development',
        'development' => array(
            'adapter' => getenv('DB_CONNECTION'),
            'name' => $db->database,
            'connection' => $db->getPdo()
        ),
        'default_database' => 'production',
        'production' => array(
            'adapter' => getenv('DB_CONNECTION'),
            'name' => $db->database,
            'connection' => $db->getPdo()
        ),
    ),
    'paths' => [
        'migrations' => 'db/migrations',
        'seeds' => 'db/seeds'
    ]
);

