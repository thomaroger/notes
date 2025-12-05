<?php

declare(strict_types=1);

// src/Controller/Front/ScoreController.php

namespace App\Controller;

use App\Entity\Assessment;
use App\Entity\Score;
use App\Repository\AssessmentRepository;
use App\Repository\ChildRepository;
use App\Repository\ScoreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/score')]
class ScoreController extends AbstractController
{
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
        usort($children, fn ($a, $b) => strcmp($a->getLastName(), $b->getLastName())); // ordre alphabétique

        return $this->render('front/score/edit.html.twig', [
            'assessment' => $assessment,
            'children' => $children,
            'user' => $user,
        ]);
    }

    #[Route('/update', name: 'score_update', methods: ['POST'])]
    public function update(
        Request $request,
        EntityManagerInterface $em,
        ScoreRepository $scoreRepository,
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

        // Cherche le score existant ou en crée un nouveau
        $score = $scoreRepository->findOneBy([
            'child' => $child,
            'assessment' => $assessment,
        ]) ?? new Score();
        $score->setChild($child);
        $score->setAssessment($assessment);

        // Gestion de l'absence
        if ($request->request->has('absent')) {
            $absent = (bool) $request->request->get('absent');
            $score->setAbsent($absent);

            if ($absent) {
                $score->setScore(null); // pas de note si absent
            }

            $em->persist($score);
            $em->flush();

            return new JsonResponse([
                'status' => 'success',
                'message' => $absent ? 'Absent' : 'Présent',
            ]);
        }

        // Gestion de la note
        if ($request->request->has('score')) {
            $scoreValue = $request->request->get('score');

            // Validation côté serveur
            if (! is_numeric($scoreValue) || $scoreValue < 0 || $scoreValue > $assessment->getMaxScore()) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => "La note doit être entre 0 et {$assessment->getMaxScore()}",
                ]);
            }

            $score->setScore((float) $scoreValue);
            $score->setAbsent(false); // note saisie → élève présent

            $em->persist($score);
            $em->flush();

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
