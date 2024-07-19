<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Service\User\Exception\InvalidParameterException;
use App\Service\User\Exception\InvalidTokenException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TokenService
{
    public function __construct(
        private UrlGeneratorInterface $urlGeneratorInterface,
        private string $baseUrl,
        private string $secret,
    ) {
    }

    public function generateUrl(
        string $routeName,
        string $task,
        string $target,
        int $tokenTtl,
    ): string {
        $expireTimestamp = time() + $tokenTtl;
        $token = self::generate(
            task: $task,
            target: $target,
            expireTimestamp: $expireTimestamp,
            secret: $this->secret,
        );

        $relativePath = $this->urlGeneratorInterface->generate(
            $routeName,
            [
                'task' => $task,
                'target' => $target,
                'token' => $token,
                'expire' => $expireTimestamp,
            ],
            UrlGeneratorInterface::RELATIVE_PATH,
        );

        return $this->baseUrl.'/'.$relativePath;
    }

    public function getUrlParams(string $url): TokenUrlParams
    {
        // Validar query params de la URL
        $urlParts = parse_url($url);
        if (!isset($urlParts['query'])) {
            throw new InvalidParameterException(message: 'Missing URL query parameters');
        }
        parse_str($urlParts['query'], $queryParts);

        $task = $queryParts['task'] ?? null;
        $validTask = is_string($task);
        if (!$validTask) {
            throw new InvalidParameterException(message: 'Invalid task');
        }
        $task = (string) $task;

        $target = $queryParts['target'] ?? null;
        $validTarget = is_string($target);
        if (!$validTarget) {
            throw new InvalidParameterException(message: 'Invalid target');
        }
        $target = (string) $target;

        $token = $queryParts['token'] ?? null;
        $validToken = is_string($token);
        if (!$validToken) {
            throw new InvalidParameterException(message: 'Invalid token');
        }
        $token = (string) $token;

        $expireTimestamp = $queryParts['expire'] ?? null;
        $validExpireTimestamp = is_numeric($expireTimestamp);
        if (!$validExpireTimestamp) {
            throw new InvalidParameterException(message: 'Invalid expire timestamp');
        }
        $expireTimestamp = (int) $expireTimestamp;
        if ($expireTimestamp < time()) {
            throw new InvalidParameterException(message: 'Expired URL');
        }

        return new TokenUrlParams(
            task: $task,
            target: $target,
            token: $token,
            expireTimestamp: $expireTimestamp,
        );
    }

    public function validateUrlParams(
        TokenUrlParams $params,
    ): void {
        // Validar token
        $isValid = self::validate(
            token: $params->token,
            task: $params->task,
            target: $params->target,
            secret: $this->secret,
            expireTimestamp: $params->expireTimestamp,
        );
        if (!$isValid) {
            throw new InvalidTokenException(message: 'Invalid token');
        }
    }

    private static function generate(
        string $task,
        string $target,
        string $secret,
        int $expireTimestamp,
    ): string {
        $password = self::createTokenPassword(
            task: $task,
            target: $target,
            secret: $secret,
            expireTimestamp: $expireTimestamp,
        );

        return password_hash($password, PASSWORD_BCRYPT);
    }

    private static function validate(
        string $token,
        string $task,
        string $target,
        string $secret,
        int $expireTimestamp,
    ): bool {
        $password = self::createTokenPassword(
            task: $task,
            target: $target,
            secret: $secret,
            expireTimestamp: $expireTimestamp,
        );

        return password_verify($password, $token);
    }

    private static function createTokenPassword(
        string $task,
        string $target,
        string $secret,
        int $expireTimestamp,
    ): string {
        return $task.$target.$secret.$expireTimestamp;
    }
}
