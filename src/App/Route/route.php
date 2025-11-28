<?php

declare(strict_types=1);

namespace App\Route;

use App\Domains\User\Presentation\UserController;
use Framework\Router\Route;
use Framework\Router\Router;

function registerRoutes(Router $router): void
{
    Route::setRouter($router);

    Route::route('/', function (): void {
        Route::get('user', [UserController::class, 'index']);
    });
}
