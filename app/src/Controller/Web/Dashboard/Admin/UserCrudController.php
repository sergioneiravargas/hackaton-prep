<?php

declare(strict_types=1);

namespace App\Controller\Web\Dashboard\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->setDisabled(true);

        yield EmailField::new('email');

        yield BooleanField::new('enabled')
            ->renderAsSwitch(false);

        yield BooleanField::new('verified')
            ->renderAsSwitch(false);

        // Roles
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new \RuntimeException('Invalid user');
        }

        $roles = [
            User::roleLabel(User::ROLE_ADMIN) => User::ROLE_ADMIN,
        ];
        yield ChoiceField::new('roles')
            ->setChoices($roles)
            ->setFormType(ChoiceType::class)
            ->allowMultipleChoices(true)
            ->setRequired(false)
            ->renderExpanded()
            ->renderAsBadges();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(
                Action::NEW,
                Action::DELETE,
            );
    }
}
