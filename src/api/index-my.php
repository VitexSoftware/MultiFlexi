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

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

require '../../vendor/autoload.php';

$app = \DI\Bridge\Slim\Bridge::create();
$app->addErrorMiddleware(true, true, true);
$app->setBasePath('/EASE/MultiFlexi/src/api');

// Add Routing Middleware
$app->addRoutingMiddleware();

// Define Custom Error Handler
$customErrorHandler = static function (
    ServerRequestInterface $request,
    Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails,
    ?LoggerInterface $logger = null,
) use ($app) {
    //    $logger->error($exception->getMessage());

    $payload = ['error' => $exception->getMessage()];

    $response = $app->getResponseFactory()->createResponse($exception->getCode())->withHeader('Content-type', 'application/json');
    $response->getBody()->write(
        json_encode($payload, \JSON_UNESCAPED_UNICODE),
    );

    return $response;
};

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true, new Slim\Logger());
$errorMiddleware->setDefaultErrorHandler($customErrorHandler);

$app->run();
