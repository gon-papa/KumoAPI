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
        $id = empty($request->query['id']) ? 0 : $request->query['id'];
        $user = $usecase->getUserById((int)$id);
        return Response::json($user, 200);
    }
}