<?php

/**
 * MultiFlexi API
 * PHP version 7.4
 *
 * @package AbraFlexi\MultiFlexi
 * @author  OpenAPI Generator team
 * @link    https://github.com/openapitools/openapi-generator
 */
/**
 * This is an example of using OAuth2 Application Flow in a specification to describe security to your API.
 * The version of the OpenAPI document: 1.0.0
 * Generated by: https://github.com/openapitools/openapi-generator.git
 */
/**
 * NOTE: This class is auto generated by the openapi generator program.
 * https://github.com/openapitools/openapi-generator
 */
require_once __DIR__ . '/../../vendor/autoload.php';

use AbraFlexi\MultiFlexi\App\RegisterDependencies;
use AbraFlexi\MultiFlexi\App\RegisterMiddlewares;
use AbraFlexi\MultiFlexi\App\RegisterRoutes;
use AbraFlexi\MultiFlexi\App\ResponseEmitter as Response;
use DI\Bridge\Slim\Bridge;
use DI\ContainerBuilder;
use Neomerx\Cors\Contracts\AnalyzerInterface;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Middleware\ErrorMiddleware;

\Ease\Shared::singleton()->loadConfig(dirname(__DIR__) . '/../.env', true);

// Instantiate PHP-DI ContainerBuilder
$builder = new ContainerBuilder();

// consider prod by default
$env;
switch (strtolower($_SERVER['APP_ENV'] ?? 'prod')) {
    case 'development':
    case 'dev':
        $env = 'dev';
        break;
    case 'production':
    case 'prod':
    default:
        $env = 'prod';
}

// Main configuration
$builder->addDefinitions(__DIR__ . "/config.php");

// Config file for the environment if exists
$userConfig = __DIR__ . "/config.php";
if (file_exists($userConfig)) {
    $builder->addDefinitions($userConfig);
}

// Set up dependencies
$dependencies = new RegisterDependencies();
$dependencies($builder);

// Build PHP-DI Container instance
$container = $builder->build();

// Instantiate the app
$app = Bridge::create($container);
$app->setBasePath('/EASE/MultiFlexi/src/api');
$path = '/EASE/MultiFlexi/src/api/VitexSoftware/MultiFlexi/1.0.0/';

// Register middleware
$middleware = new RegisterMiddlewares();
$middleware($app);

// Register routes
// yes, it's anti-pattern you shouldn't get deps from container directly
$routes = $container->get(RegisterRoutes::class);
$routes($app);

// Create Request object from globals
$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

// Get error middleware from container
// also anti-pattern, of course we know
$errorMiddleware = $container->get(ErrorMiddleware::class);

//$app->add(new Slim\Middleware\TokenAuthentication([
//            'path' => $app->getBasePath() . '/api',
//            'passthrough' => ['/', '/ping', '/login'], /* or ['/api/auth', '/api/test'] */
//            "authenticator" => function ($arguments) {
//                return (bool) rand(0, 1);
//            }
//        ]));


//route0 → (unnamed) → /{routes:.*}
//route1 → listAbraFlexis → /VitexSoftware/MultiFlexi/1.0.0/abraflexis/
//route2 → setAbraFlexiById → /VitexSoftware/MultiFlexi/1.0.0/abraflexi/
//route3 → getAbraFlexiById → /VitexSoftware/MultiFlexi/1.0.0/abraflexi/{abraflexiId}
//route4 → listApps → /VitexSoftware/MultiFlexi/1.0.0/apps/
//route5 → setAppById → /VitexSoftware/MultiFlexi/1.0.0/app/
//route6 → getAppById → /VitexSoftware/MultiFlexi/1.0.0/app/{appId}
//route7 → getApiIndex → /VitexSoftware/MultiFlexi/1.0.0/
//route8 → loginGet → /VitexSoftware/MultiFlexi/1.0.0/login
//route9 → loginPost → /VitexSoftware/MultiFlexi/1.0.0/login
//route10 → pingGet → /VitexSoftware/MultiFlexi/1.0.0/ping
//route11 → (unnamed) → /


        
$app->add(new \Tuupola\Middleware\HttpBasicAuthentication([
            'relaxed' => ['localhost'],
//            'path' => ['/EASE/MultiFlexi/src/api/VitexSoftware/MultiFlexi/1.0.0/apps/', $path . '/apps', $path . '/users'],
//            "ignore" => [$path . '/', $path . '/ping', $path . '/authorize'],
//            'path' => '/',
            'ignore' => [ $path . '/', $path . '/ping/'],
//            "authenticator" => new AbraFlexi\MultiFlexi\Auth\BasicAuthenticator()
            "authenticator" => function ($arguments) {
                $prober = new AbraFlexi\MultiFlexi\User($arguments['user']);
                return $prober->getUserID() && strlen($arguments['password']) && $prober->isAccountEnabled() && $prober->passwordValidation($arguments['password'], $prober->getDataValue($prober->passwordColumn));
            }
        ]));

// Run App & Emit Response
$response = $app->handle($request);
$responseEmitter = (new Response())
        ->setRequest($request)
        ->setErrorMiddleware($errorMiddleware)
        ->setAnalyzer($container->get(AnalyzerInterface::class));

$responseEmitter->emit($response);