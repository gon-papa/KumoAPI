<?php

declare(strict_types=1);

namespace App\Domains\User\Application;

use App\Domains\User\Infrastructure\UserRepository;

class UserService
{
    public function __construct(
        public readonly UserRepository $repository
    ) {
    }

    /**
     * @param int $id
     * @return array{id: int, name: string, gender: string, name: string}
     */
    public function getUserById(int $id): array
    {
        return $this->repository->getUser($id);
    }
}
