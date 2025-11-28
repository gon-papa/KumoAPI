<?php

namespace App\Controllers;

use Framework\Request\Request;
use Framework\Response\Response;

class UserController
{
    public function index(Request $request): Response
    {
        return Response::json([
            ['id' => 1, 'name' => 'Naoki'],
            ['id' => 2, 'name' => 'Taro'],
        ]);
    }

    public function show(Request $request, string $id): Response
    {
        return Response::json([
            'id' => $id,
            'name' => 'User ' . $id,
        ]);
    }

    public function store(Request $request): Response
    {
        return Response::json([
            'message' => 'created',
            'data' => $request->json,
        ], 201);
    }
}