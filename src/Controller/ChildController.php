<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Service\AssessmentService;
use App\Service\ChildService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ChildController extends AbstractController
{
    #[Route('/child/notes', name: 'child_notes')]
    #[IsGranted('ROLE_USER')]
    public function notes(
        Request $request,
        ChildService $childService,
        AssessmentService $assessmentService
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $childId = $request->query->get('child');
        $children = $childService->getAllChildrenSorted();

        if (! $children) {
            $this->addFlash('warning', 'Aucun enfant trouvé.');
            return $this->redirectToRoute('app_home');
        }

        $child = $childService->getChildByIdOrFirst($childId);

        if (! $child) {
            $this->addFlash('danger', 'Enfant non trouvé.');
            return $this->redirectToRoute('app_home');
        }

        // Récupération des évaluations de la classe de l'enfant
        $assessments = $assessmentService->getAssessmentsWithStats($child->getSchoolClass());

        // Filtre pour ne garder que les évaluations où l'enfant a un score
        $assessments = $childService->filterAssessmentsWithChildScores($assessments, $child);

        // Groupement des évaluations par thème
        $groupedByTheme = $assessmentService->groupAssessmentsByTheme($assessments);

        return $this->render('front/child/notes.html.twig', [
            'child' => $child,
            'children' => $children,
            'groupedByTheme' => $groupedByTheme,
            'user' => $user,
        ]);
    }
}
