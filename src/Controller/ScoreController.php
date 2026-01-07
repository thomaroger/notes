<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Assessment;
use App\Repository\AssessmentRepository;
use App\Repository\ChildRepository;
use App\Service\ChildService;
use App\Service\ScoreService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/score')]
class ScoreController extends AbstractController
{
    public function __construct(
        private readonly ChildService $childService,
        private readonly ScoreService $scoreService
    ) {
    }

    #[Route('/edit/{assessment}', name: 'score_edit')]
    public function edit(Assessment $assessment): Response
    {
        $user = $this->getUser();
        if (! $user) {
            return $this->redirectToRoute('app_login');
        }

        $children = $assessment->getSchoolClass()
            ->getChildren()
            ->toArray();
        $children = $this->childService->sortChildrenByLastName($children);

        return $this->render('front/score/edit.html.twig', [
            'assessment' => $assessment,
            'children' => $children,
            'user' => $user,
        ]);
    }

    #[Route('/update', name: 'score_update', methods: ['POST'])]
    public function update(
        Request $request,
        ChildRepository $childRepository,
        AssessmentRepository $assessmentRepository
    ): JsonResponse {
        $childId = $request->request->get('childId');
        $assessmentId = $request->request->get('assessmentId');

        if (! $childId || ! $assessmentId) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Paramètres manquants',
            ]);
        }

        $child = $childRepository->find($childId);
        $assessment = $assessmentRepository->find($assessmentId);

        if (! $child || ! $assessment) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Élève ou évaluation introuvable',
            ]);
        }

        // Gestion de l'absence
        if ($request->request->has('absent')) {
            $absent = (bool) $request->request->get('absent');
            $this->scoreService->updateOrCreateScore($child, $assessment, null, $absent);

            return new JsonResponse([
                'status' => 'success',
                'message' => $absent ? 'Absent' : 'Présent',
            ]);
        }

        // Gestion de la note
        if ($request->request->has('score')) {
            $scoreValue = $request->request->get('score');

            if (! is_numeric($scoreValue)) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'La note doit être un nombre',
                ]);
            }

            $scoreValue = (float) $scoreValue;

            // Validation côté serveur
            if (! $this->scoreService->validateScore($scoreValue, $assessment)) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => "La note doit être entre 0 et {$assessment->getMaxScore()}",
                ]);
            }

            $score = $this->scoreService->updateOrCreateScore($child, $assessment, $scoreValue, false);

            return new JsonResponse([
                'status' => 'success',
                'score' => $score->getScore(),
            ]);
        }

        return new JsonResponse([
            'status' => 'error',
            'message' => 'Aucune donnée à enregistrer',
        ]);
    }
}
