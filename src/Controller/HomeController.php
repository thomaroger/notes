<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Assessment;
use App\Form\AssessmentType;
use App\Repository\StatRepository;
use App\Service\AssessmentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(AssessmentService $assessmentService, StatRepository $statRepo): Response
    {
        $user = $this->getUser();
        if (! $user) {
            return $this->redirectToRoute('app_login');
        }

        $assessments = $assessmentService->getAssessmentsWithStats($user->getSchoolClass());

        // Vérifie que toutes les évaluations ont des stats
        foreach ($assessments as $assessment) {
            $stat = $statRepo->findByEntityTypeAndId('assessment', $assessment->getId());
            if (! $stat) {
                return $this->redirectToRoute('stats_stat');
            }
        }

        // Groupe par thème et enrichit avec les stats
        $groupedByTheme = $assessmentService->groupAssessmentsByTheme($assessments);

        // Trie les scores de toutes les évaluations
        $assessmentService->sortAllScoresInGroupedStructure($groupedByTheme);

        return $this->render('front/index.html.twig', [
            'user' => $user,
            'groupedByTheme' => $groupedByTheme,
        ]);
    }

    #[Route('/assessment/new', name: 'assessment_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (! $user) {
            return $this->redirectToRoute('app_login');
        }

        $assessment = new Assessment();
        $assessment->setSchoolClass($user->getSchoolClass());

        $form = $this->createForm(AssessmentType::class, $assessment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($assessment);
            $em->flush();

            $this->addFlash('success', 'Évaluation créée avec succès !');
            return $this->redirectToRoute('score_edit', [
                'assessment' => $assessment->getId(),
            ]);
        }

        return $this->render('/front/assessment/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
