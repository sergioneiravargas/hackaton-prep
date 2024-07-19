<?php

declare(strict_types=1);

namespace App\Service\User\Notification\Exception;

class InvalidRequest extends \Exception
{
    public const DEFAULT_MESSAGE = 'Invalid request';

    public function __construct(
        string $message = null,
        int $code = 0,
        \Throwable $previous = null,
    ) {
        $message = null === $message
            ? self::DEFAULT_MESSAGE
            : sprintf('%s: %s', self::DEFAULT_MESSAGE, $message);

        parent::__construct($message, $code, $previous);
    }
}
