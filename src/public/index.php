<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function App\Route\registerRoutes;

use Framework\Container\Container;
use Framework\Request\Request;
use Framework\Response\Response;
use Framework\Router\Router;

$container = new Container();
$request = Request::fromGlobals();
$container->instance(Request::class, $request);
$router = $container->make(Router::class);

// Load routes
require __DIR__ . '/../App/Route/route.php';
registerRoutes($router);

$route = $router->dispatch($request);

if ($route === null) {
    $response = Response::json(['message' => 'Not found'], 404);
} else {
    [$handler, $params] = $route;
    [$class, $method] = $handler;
    $controller = $container->make($class);
    $response = $container->call([$controller, $method], $params);
}

$response->send();
