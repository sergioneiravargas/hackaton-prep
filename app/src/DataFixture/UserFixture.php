<?php

declare(strict_types=1);

namespace App\DataFixture;

use App\DataFixture\Factory\UserFactory;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixture extends Fixture
{
    public const REFERENCE_ADMIN_1 = 'REFERENCE_ADMIN_1';
    public const REFERENCE_USER_1 = 'REFERENCE_USER_1';

    public function load(ObjectManager $manager): void
    {
        $defaultPassword = 'password';

        $admin = UserFactory::new()
            ->withAttributes([
                'email' => 'admin@email.com',
                'plainPassword' => $defaultPassword,
            ])
            ->promoteRole(User::ROLE_ADMIN)
            ->create();
        $this->addReference(self::REFERENCE_ADMIN_1, $admin->object());

        // Normal users
        $user = UserFactory::new()
            ->withAttributes([
                'email' => 'user@email.com',
                'plainPassword' => $defaultPassword,
            ])
            ->create();
        $this->addReference(self::REFERENCE_USER_1, $user->object());
    }
}
