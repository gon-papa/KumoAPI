<?php

declare(strict_types=1);

namespace App\Domains\User\Infrastructure;

class UserRepository
{
    public function getUser(int $id): array
    {
        return [
            'id' => $id,
            'name' => 'Mario',
            'gender' => 'male',
            'age' => '20',
        ];
    }
}
