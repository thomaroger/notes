<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatResetController extends AbstractController
{
    #[Route('/admin/reset_stat', name: 'admin_stat_reset')]
    public function reset(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (! $user) {
            return $this->redirectToRoute('app_login');
        }

        if (! $user->hasRole('ROLE_ADMIN')) {
            throw new AccessDeniedException('Vous n\avez pas les accès nécessaires pour faire cette action.');
        }

        $connection = $em->getConnection();
        $connection->executeStatement('TRUNCATE TABLE stat;');

        return $this->redirectToRoute('admin');

    }
}
