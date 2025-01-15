<?php

namespace App\DataTransferObjects;

use App\Models\User;

class AuthResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?User $user = null,
        public readonly ?string $token = null
    ) {}
}
