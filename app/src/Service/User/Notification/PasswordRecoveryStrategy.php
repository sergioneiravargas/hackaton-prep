<?php

declare(strict_types=1);

namespace App\Service\User\Notification;

use App\Framework\Notification\Request;
use App\Framework\Notification\StrategyInterface;
use App\Service\User\Notification\Exception\FailedNotification;
use App\Service\User\Notification\Exception\InvalidRequest;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class PasswordRecoveryStrategy implements StrategyInterface
{
    public const REQUEST_TYPE = 'PASSWORD_RECOVERY';

    public function __construct(
        private MailerInterface $mailer,
    ) {
    }

    public function shouldNotify(Request $request): bool
    {
        return self::REQUEST_TYPE === $request->type;
    }

    public function notify(Request $request): void
    {
        if (!$this->requestIsValid($request)) {
            throw new InvalidRequest();
        }

        $to = $request->data['to'];
        $recoveryUrl = $request->data['recoveryUrl'];

        $message = new Email();
        $message
            ->to($to)
            ->subject('Password recovery')
            ->text('Recover your password')
            ->html(
                "Enter to the following link to recover your password: <a href=\"{$recoveryUrl}\">link</a>",
            );

        try {
            $this->mailer->send($message);
        } catch (\Throwable $th) {
            throw new FailedNotification(message: $th->getMessage(), code: $th->getCode(), previous: $th);
        }
    }

    private function requestIsValid(Request $request): bool
    {
        return isset($request->data['to'])
            && is_string($request->data['to'])
            && isset($request->data['recoveryUrl'])
            && is_string($request->data['recoveryUrl']);
    }
}
