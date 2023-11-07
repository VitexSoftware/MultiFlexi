<?php

/**
 * Multi Flexi - API Config
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

/**
 * App configuration defaults for production.
 * This file used when 'APP_ENV' variable set to 'prod' or 'production'. Check public/.htaccess file
 */

/**
 * Each environment(dev, prod) should contain two files default.inc.php and config.inc.php.
 * This is the first file with production defaults. It contains all data which can be safely committed
 * to VCS(version control system). For sensitive values(passwords, api keys, emails) use config.inc.php
 * and make sure it's excluded from VCS by .gitignore.
 * do not add dependencies here, use MultiFlexi\App\RegisterDependencies class
 * @see https://php-di.org/doc/php-definitions.html#values
 */
return [
    'mode' => 'production',

    // Returns a detailed HTML page with error details and
    // a stack trace. Should be disabled in production.
    'slim.displayErrorDetails' => boolval(\Ease\Shared::cfg('API_DEBUG', false)),

    // Whether to display errors on the internal PHP log or not.
    'slim.logErrors' => true,

    // If true, display full errors with message and stack trace on the PHP log.
    // If false, display only "Slim Application Error" on the PHP log.
    // Doesn't do anything when 'logErrors' is false.
    'slim.logErrorDetails' => true,

    // CORS settings
    // https://github.com/neomerx/cors-psr7/blob/master/src/Strategies/Settings.php
    'cors.settings' => [
        isset($_SERVER['HTTPS']) ? 'https' : 'http', // serverOriginScheme
        $_SERVER['SERVER_NAME'], // serverOriginHost
        null, // serverOriginPort
        true, // isPreFlightCanBeCached
        86400, // preFlightCacheMaxAge
        false, // isForceAddMethods
        false, // isForceAddHeaders
        true, // isUseCredentials
        false, // areAllOriginsAllowed
        [], // allowedOrigins
        false, // areAllMethodsAllowed
        [], // allowedLcMethods
        '', // allowedMethodsList
        false, // areAllHeadersAllowed
        [], // allowedLcHeaders
        '', // allowedHeadersList
        '', // exposedHeadersList
        true, // isCheckHost
    ],

    // PDO
    'pdo.dsn' => \Ease\Shared::cfg('DB_CONNECTION') . ':host=' . \Ease\Shared::cfg('DB_HOST') . ';charset=utf8mb4',
    'pdo.username' => \Ease\Shared::cfg('DB_USERNAME'),
    'pdo.password' => \Ease\Shared::cfg('DB_PASSWORD'),
    'pdo.options' => [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    ],

    // logger
    'logger.name' => \Ease\Shared::appName(),
    'logger.path' => \realpath(__DIR__ . '/../../logs') . '/app.log',
    'logger.level' => 300, // equals WARNING level
    'logger.options' => [],
];
