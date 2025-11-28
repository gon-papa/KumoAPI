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

    public function getUserById(int $id): array
    {
        return $this->repository->getUser($id);
    }
}
