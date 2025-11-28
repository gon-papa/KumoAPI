<?php

declare(strict_types=1);

namespace App\Domains\User\Presentation;

use App\Domains\User\Application\UserService;
use Framework\Request\Request;
use Framework\Response\Response;

class UserController
{
    public function index(Request $request, UserService $usecase): mixed
    {
        $idParam = $request->query['id'] ?? null;
        $id = is_numeric($idParam) ? (int)$idParam : 0;
        $user = $usecase->getUserById($id);
        return Response::json($user, 200);
    }
}
