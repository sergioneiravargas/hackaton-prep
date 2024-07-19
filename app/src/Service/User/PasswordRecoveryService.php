<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Framework\Notification\Handler as NotificationHandler;
use App\Framework\Notification\Request as NotificationRequest;
use App\Service\User\Notification\PasswordRecoveryStrategy;
use App\Service\User\Exception\InvalidParameterException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PasswordRecoveryService
{
    public const TASK = 'PASSWORD_RECOVERY';

    public function __construct(
        private EntityManagerInterface $em,
        private NotificationHandler $notificationHandler,
        private TokenService $tokenService,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
        private LoggerInterface $logger,
        private string $routeName,
        private int $tokenTtl,
    ) {
    }

    public function sendNotification(User $user): void
    {
        $request = new NotificationRequest(
            type: PasswordRecoveryStrategy::REQUEST_TYPE,
            data: [
                'to' => $user->getEmail(),
                'recoveryUrl' => $this->generateUrl($user),
            ],
        );

        try {
            $this->notificationHandler->handleNotification($request);
        } catch (\Throwable $th) {
            $this->logger->error('Notification failed', [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);
        }
    }

    public function generateUrl(User $user): string
    {
        return $this->tokenService->generateUrl(
            routeName: $this->routeName,
            task: $this->getTask($user),
            target: $user->getEmail(),
            tokenTtl: $this->tokenTtl,
        );
    }

    public function recoverPassword(
        string $url,
        string $plainPassword,
    ): void {
        $params = $this->tokenService->getUrlParams($url);

        $this->validateUrlParams($params);

        $user = $this->userRepository->findOneBy(['email' => $params->target]);
        if (!$user instanceof User) {
            throw new InvalidParameterException(message: 'User not found');
        }

        $user->setPlainPassword($plainPassword);

        $errors = $this->validator->validate(
            value: $user,
            groups: [
                User::GROUP_PASSWORD_RECOVERY,
            ],
        );
        if (count($errors) > 0) {
            throw new InvalidParameterException(message: (string) $errors);
        }

        $password = $this->passwordHasher->hashPassword(
            user: $user,
            plainPassword: $plainPassword,
        );
        $user
            ->setPassword($password)
            ->eraseCredentials();

        $this->em->flush();
    }

    private function validateUrlParams(TokenUrlParams $params): void
    {
        $user = $this->userRepository->findOneBy(['email' => $params->target]);
        if (!$user instanceof User) {
            throw new InvalidParameterException(message: 'Invalid target');
        }
        if ($this->getTask($user) !== $params->task) {
            throw new InvalidParameterException(message: 'Invalid task');
        }

        $this->tokenService->validateUrlParams($params);
    }

    private function getTask(User $user): string
    {
        return self::TASK . '_' . $user->getPasswordChangedAt()?->getTimestamp();
    }
}
