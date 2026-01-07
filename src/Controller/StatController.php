<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\StatService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class StatController extends AbstractController
{
    #[Route('/stats/stat', name: 'stats_stat')]
    #[IsGranted('ROLE_USER')]
    public function notes(StatService $statService): Response
    {
        $created = $statService->computeAllStats();

        $this->addFlash('success', "{$created} statistiques créées.");

        return $this->redirectToRoute('app_home');
    }
}
