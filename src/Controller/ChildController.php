<?php

declare(strict_types=1);

// src/Controller/ChildController.php

namespace App\Controller;

use App\Repository\AssessmentRepository;
use App\Repository\ChildRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChildController extends AbstractController
{
    #[Route('/child/notes', name: 'child_notes')]
    public function notes(
        Request $request,
        ChildRepository $childRepository,
        AssessmentRepository $assessmentRepository
    ): Response {
        // Récupération de l'enfant sélectionné (ou le premier par défaut)
        $user = $this->getUser();
        if (! $user) {
            return $this->redirectToRoute('app_login');
        }

        $childId = $request->query->get('child');
        $children = $childRepository->findby([], [
            'lastName' => 'ASC',
        ]);

        if (! $children) {
            $this->addFlash('warning', 'Aucun enfant trouvé.');
            return $this->redirectToRoute('app_home');
        }

        $child = $childId ? $childRepository->find($childId) : $children[0];

        if (! $child) {
            $this->addFlash('danger', 'Enfant non trouvé.');
            return $this->redirectToRoute('app_home');
        }

        // Récupération des évaluations de la classe de l'enfant
        $assessments = $assessmentRepository->findBy(
            [
                'schoolClass' => $child->getSchoolClass(),
            ],
            [
                'date' => 'ASC',
            ]
        );

        $assessments = array_filter($assessments, function ($assessment) use ($child) {
            foreach ($child->getScores() as $score) {
                if ($score->getAssessment()->getId() === $assessment->getId()) {
                    return true; // a une note ou absent
                }
            }
            return false; // pas de note et pas absent → on exclut
        });

        // Groupement des évaluations par thème
        $groupedByTheme = [];
        foreach ($assessments as $assessment) {
            $themeName = $assessment->getCategory()
                ->getParent()
                ->getTheme()
                ->getName();
            if (! isset($groupedByTheme[$themeName])) {
                $groupedByTheme[$themeName] = [];
            }
            $groupedByTheme[$themeName][] = $assessment;
        }

        return $this->render('front/child/notes.html.twig', [
            'child' => $child,
            'children' => $children,
            'groupedByTheme' => $groupedByTheme,
            'user' => $user,
        ]);
    }
}
