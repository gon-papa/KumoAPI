<?php

declare(strict_types=1);

namespace App\Domains\User\Infrastructure;

class UserRepository
{
    /**
     * @param int $id
     * @return array{id: int, name: string, gender: string, name: string}
     */
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
