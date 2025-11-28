<?php
declare(strict_types=1);

namespace App\Route;

use App\Controllers\HomeController;
use App\Controllers\UserController;
use Framework\Router\Route;
use Framework\Router\Router;

function registerRoutes(Router $router): void
{
    Route::setRouter($router);

    Route::route('/', function (): void {
        Route::get('', [HomeController::class, 'index']);
        Route::get('users', [UserController::class, 'index']);
        Route::get('users/{id}', [UserController::class, 'show']);
        Route::post('users', [UserController::class, 'store']);

        Route::route('admin', function(): void {
            Route::get('', [HomeController::class, 'index']);
        });
    });
}
