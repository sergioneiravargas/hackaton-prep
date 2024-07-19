<?php

declare(strict_types=1);

namespace App\Framework\Notification;

final class Request
{
    public function __construct(
        public readonly string $type,
        public readonly array $data,
    ) {
    }
}
