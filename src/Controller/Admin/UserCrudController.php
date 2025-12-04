<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('schoolClass', 'Classe'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('firstName', 'Prénom')
                ->setRequired(true),
            TextField::new('lastName', 'Nom')
                ->setRequired(true),
            EmailField::new('email', 'Email'),
            TextField::new('password', 'Mot de passe')
                ->setRequired($pageName === 'new')
                ->hideOnIndex()
                ->setHelp('Laissez vide pour ne pas modifier le mot de passe'),
            ChoiceField::new('roles', 'Rôles')
                ->setChoices([
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                ])
                ->allowMultipleChoices()
                ->renderExpanded(),
            AssociationField::new('schoolClass', 'Classe')
                ->setHelp('Uniquement pour les utilisateurs avec ROLE_USER'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var User $entityInstance */
        $this->hashPassword($entityInstance, $entityManager);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var User $entityInstance */
        $this->hashPassword($entityInstance, $entityManager);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function hashPassword(User $user, EntityManagerInterface $entityManager): void
    {
        $plainPassword = $user->getPassword();

        // Si le mot de passe est vide ou null, on ne le modifie pas (uniquement pour la mise à jour)
        if (empty($plainPassword)) {
            // Récupérer le mot de passe actuel depuis la base de données si l'utilisateur existe
            if ($user->getId() !== null) {
                $existingUser = $entityManager->getRepository(User::class)->find($user->getId());
                if ($existingUser) {
                    $user->setPassword($existingUser->getPassword());
                }
            }
            return;
        }

        // Hasher le nouveau mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);
    }
}
