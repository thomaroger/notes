<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\AssessmentType;
use App\Service\AssessmentManagementService;
use App\Service\AssessmentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly AssessmentService $assessmentService,
        private readonly AssessmentManagementService $assessmentManagementService
    ) {
    }

    #[Route('/', name: 'app_home')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $assessments = $this->assessmentService->getAssessmentsWithStats($user->getSchoolClass());

        // Vérifie que toutes les évaluations ont des stats
        if (! $this->assessmentService->allAssessmentsHaveStats($assessments)) {
            return $this->redirectToRoute('stats_stat');
        }

        // Groupe par thème et enrichit avec les stats
        $groupedByTheme = $this->assessmentService->groupAssessmentsByTheme($assessments);

        // Trie les scores de toutes les évaluations
        $this->assessmentService->sortAllScoresInGroupedStructure($groupedByTheme);

        return $this->render('front/index.html.twig', [
            'user' => $user,
            'groupedByTheme' => $groupedByTheme,
        ]);
    }

    #[Route('/assessment/new', name: 'assessment_new')]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $assessment = $this->assessmentManagementService->createAssessment($user->getSchoolClass());

        $form = $this->createForm(AssessmentType::class, $assessment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->assessmentManagementService->saveAssessment($assessment);

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
