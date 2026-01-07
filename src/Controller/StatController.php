<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\StatService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatController extends AbstractController
{
    #[Route('/stats/stat', name: 'stats_stat')]
    public function notes(StatService $statService): Response
    {
        $user = $this->getUser();
        if (! $user) {
            return $this->redirectToRoute('app_login');
        }

        $created = $statService->computeAllStats();

        $this->addFlash('success', "{$created} statistiques créées.");

        return $this->redirectToRoute('app_home');
    }
}
