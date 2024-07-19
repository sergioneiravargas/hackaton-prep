<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(
    fields: 'email',
    groups: [
        self::GROUP_REGISTRATION,
    ],
)]
#[Gedmo\Loggable]
class User implements EntityInterface, UserInterface, PasswordAuthenticatedUserInterface
{
    use EntityTrait;

    // SECURITY ROLES
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    // VALIDATION GROUPS
    public const GROUP_REGISTRATION = 'USER_REGISTRATION';
    public const GROUP_PASSWORD_RECOVERY = 'USER_PASSWORD_RECOVERY';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(
        groups: [
            self::GROUP_REGISTRATION,
        ],
    )]
    #[Assert\Email(
        groups: [
            self::GROUP_REGISTRATION,
        ],
    )]
    #[Gedmo\Versioned]
    private ?string $email = null;

    #[ORM\Column]
    #[Gedmo\Versioned]
    private array $roles = [self::ROLE_USER];

    /**
     * The plain password.
     */
    #[Assert\NotBlank(
        groups: [
            self::GROUP_REGISTRATION,
            self::GROUP_PASSWORD_RECOVERY,
        ],
    )]
    #[Assert\Length(
        min: 8,
        max: 24,
        groups: [
            self::GROUP_REGISTRATION,
            self::GROUP_PASSWORD_RECOVERY,
        ],
    )]
    private ?string $plainPassword = null;

    /**
     * The hashed password.
     */
    #[ORM\Column]
    #[Gedmo\Versioned]
    private ?string $password = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE, nullable: true)]
    #[Gedmo\Versioned]
    private ?\DateTimeInterface $passwordChangedAt = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE, nullable: true)]
    #[Gedmo\Versioned]
    private ?\DateTime $enabledAt = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE, nullable: true)]
    #[Gedmo\Versioned]
    private ?\DateTime $verifiedAt = null;

    public function __construct()
    {
        $this->setEnabled(true);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): static
    {
        $roles[] = self::ROLE_USER; // rol predeterminado
        $roles = array_values($roles);

        $this->roles = array_unique($roles);

        return $this;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles(), true);
    }

    public static function roleLabel(string $role): string
    {
        return match ($role) {
            self::ROLE_USER => 'User',
            self::ROLE_ADMIN => 'Admin',
            default => throw new \InvalidArgumentException(sprintf('Invalid role "%s"', $role)),
        };
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $password): static
    {
        $this->plainPassword = $password;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $isFirstTimePassword = null === $this->password;
        $this->password = $password;
        if (!$isFirstTimePassword) {
            $this->setPasswordChangedAt(new \DateTime());
        }

        return $this;
    }

    public function getPasswordChangedAt(): ?\DateTimeInterface
    {
        return $this->passwordChangedAt;
    }

    public function setPasswordChangedAt(?\DateTimeInterface $passwordChangedAt): static
    {
        $this->passwordChangedAt = $passwordChangedAt;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    public function getEnabledAt(): ?\DateTime
    {
        return $this->enabledAt;
    }

    public function setEnabledAt(?\DateTime $enabledAt): static
    {
        $this->enabledAt = $enabledAt;

        return $this;
    }

    public function isEnabled(): bool
    {
        return (bool) $this->enabledAt;
    }

    public function setEnabled(bool $enabled): static
    {
        if ($enabled !== $this->isEnabled()) {
            $this->setEnabledAt($enabled ? new \DateTime() : null);
        }

        return $this;
    }

    public function getVerifiedAt(): ?\DateTime
    {
        return $this->verifiedAt;
    }

    public function setVerifiedAt(?\DateTime $verifiedAt): static
    {
        $this->verifiedAt = $verifiedAt;

        return $this;
    }

    public function isVerified(): bool
    {
        return (bool) $this->verifiedAt;
    }

    public function setVerified(bool $verified): static
    {
        if ($verified !== $this->isVerified()) {
            $this->setVerifiedAt($verified ? new \DateTime() : null);
        }

        return $this;
    }
}
