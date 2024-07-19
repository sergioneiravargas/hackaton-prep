<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;
use App\Service\User\Exception\InvalidParameterException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
        private EmailVerificationService $emailVerificationService,
    ) {
    }

    public function register(
        string $email,
        string $plainPassword,
        bool $isAdmin = false,
    ): void {
        $user = $this->createUser(
            email: $email,
            plainPassword: $plainPassword,
            isAdmin: $isAdmin,
        );

        $this->em->persist($user);
        $this->em->flush();

        $this->emailVerificationService->sendNotification($user);
    }

    private function createUser(
        string $email,
        string $plainPassword,
        bool $isAdmin = false,
    ): User {
        $user = new User();
        $user
            ->setEmail($email)
            ->setPlainPassword($plainPassword);
        if ($isAdmin) {
            $user->setRoles([User::ROLE_ADMIN]);
        }

        $errors = $this->validator->validate(
            value: $user,
            groups: [
                User::GROUP_REGISTRATION,
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

        return $user;
    }
}
