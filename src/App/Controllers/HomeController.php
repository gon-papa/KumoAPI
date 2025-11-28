<?php
declare(strict_types=1);

namespace App\Controllers;

use Framework\Response\Response;

class HomeController
{
    public function index(): Response
    {
        return Response::json(['message' => 'welcome']);
    }
}
