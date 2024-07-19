<?php

declare(strict_types=1);

namespace App\Service\User;

class TokenUrlParams
{
    public function __construct(
        public readonly string $task,
        public readonly string $target,
        public readonly string $token,
        public readonly int $expireTimestamp,
    ) {
    }
}
