<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function App\Route\registerRoutes;

use Framework\Container\Container;
use Framework\Request\Request;
use Framework\Response\Response;
use Framework\Router\Router;
use RuntimeException;

$container = new Container();
$request = Request::fromGlobals();
$container->instance(Request::class, $request);
/** @var Router $router */
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
    $callable = [$controller, $method];
    if (!is_callable($callable)) {
        throw new RuntimeException('Route handler is not callable.');
    }
    $response = $container->call($callable, $params);
    if (!$response instanceof Response) {
        throw new RuntimeException('Controller must return a Response instance.');
    }
}

$response->send();
