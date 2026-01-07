<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

class SecurityService
{
    /**
     * Vérifie si l'utilisateur est authentifié et le retourne
     */
    public function requireUser(UserInterface|null $user): User
    {
        if (! $user instanceof User) {
            throw new AccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        return $user;
    }

    /**
     * Vérifie si l'utilisateur a un rôle spécifique
     */
    public function hasRole(UserInterface|null $user, string $role): bool
    {
        if (! $user instanceof User) {
            return false;
        }

        return $user->hasRole($role);
    }

    /**
     * Vérifie si l'utilisateur est administrateur
     */
    public function isAdmin(UserInterface|null $user): bool
    {
        return $this->hasRole($user, 'ROLE_ADMIN');
    }

    /**
     * Vérifie si l'utilisateur est un utilisateur standard (non admin)
     */
    public function isUser(UserInterface|null $user): bool
    {
        if (! $user instanceof User) {
            return false;
        }

        $roles = $user->getRoles();
        return ! in_array('ROLE_ADMIN', $roles, true) || count($roles) > 1;
    }

    /**
     * Détermine la route de redirection après connexion selon le rôle de l'utilisateur
     */
    public function getRedirectRouteAfterLogin(UserInterface $user): string
    {
        if (! $user instanceof User) {
            return 'app_home';
        }

        $roles = $user->getRoles();
        if (in_array('ROLE_ADMIN', $roles, true) && count($roles) === 1) {
            return 'admin';
        }

        return 'app_home';
    }

    /**
     * Vérifie si l'utilisateur a accès à une ressource spécifique
     */
    public function canAccess(UserInterface|null $user, string $resource, mixed $object = null): bool
    {
        if (! $user instanceof User) {
            return false;
        }

        // Logique de vérification d'accès personnalisée
        // Peut être étendue avec des voters Symfony
        return true;
    }
}
