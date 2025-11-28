<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Framework\Request\Request;
use Framework\Response\Response;
use Framework\Router\Router;
use function App\Route\registerRoutes;

$request = Request::fromGlobals();
$router = new Router();

// Load routes
require __DIR__ . '/../App/Route/route.php';
registerRoutes($router);

$route = $router->dispatch($request);

if ($route === null) {
    $response = Response::json(['message' => 'Not found'], 404);
} else {
    [$handler, $params] = $route;
    [$class, $method] = $handler;
    $controller = new $class();

    $response = $controller->$method($request, ...$params);
}

$response->send();
