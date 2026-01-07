<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Assessment;
use App\Entity\User;
use App\Service\ChildService;
use App\Service\ScoreService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/score')]
class ScoreController extends AbstractController
{
    public function __construct(
        private readonly ChildService $childService,
        private readonly ScoreService $scoreService
    ) {
    }

    #[Route('/edit/{assessment}', name: 'score_edit')]
    #[IsGranted('ROLE_USER')]
    public function edit(Assessment $assessment): Response
    {
        /** @var User $user */
        $user = $this->getUser();

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
    #[IsGranted('ROLE_USER')]
    public function update(Request $request): JsonResponse
    {
        $result = $this->scoreService->updateScoreFromRequest($request->request->all());

        return new JsonResponse($result);
    }
}
