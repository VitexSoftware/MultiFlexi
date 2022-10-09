<?php

/**
 * Multi Flexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2022 Vitex Software
 */
use Psr\Http\Message\ServerRequestInterface as Request;
use AbraFlexi\MultiFlexi\App\ResponseEmitter as Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

require '../../vendor/autoload.php';

$app = \DI\Bridge\Slim\Bridge::create();
$app->addErrorMiddleware(true, true, true);
$app->setBasePath('/EASE/MultiFlexi/src/api');


// Add Routing Middleware
$app->addRoutingMiddleware();

// Define Custom Error Handler
$customErrorHandler = function (
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails,
        ?LoggerInterface $logger = null
) use ($app) {
//    $logger->error($exception->getMessage());

    $payload = ['error' => $exception->getMessage()];

    $response = $app->getResponseFactory()->createResponse($exception->getCode())->withHeader('Content-type', 'application/json');
    $response->getBody()->write(
            json_encode($payload, JSON_UNESCAPED_UNICODE)
    );

    return $response;
};

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true, new Slim\Logger());
$errorMiddleware->setDefaultErrorHandler($customErrorHandler);

$app->run();


