<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Repository\StatRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class StatResetController extends AbstractController
{
    #[Route('/admin/reset_stat', name: 'admin_stat_reset')]
    #[IsGranted('ROLE_ADMIN')]
    public function reset(StatRepository $statRepository): Response
    {
        $statRepository->truncateAll();

        $this->addFlash('success', 'Statistiques réinitialisées avec succès.');

        return $this->redirectToRoute('admin');
    }
}
