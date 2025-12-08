<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Assessment;
use App\Form\AssessmentType;
use App\Repository\AssessmentRepository;
use App\Repository\StatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(AssessmentRepository $assessmentRepository, StatRepository $statRepo): Response
    {
        $user = $this->getUser();
        if (! $user) {
            return $this->redirectToRoute('app_login');
        }

        $assessments = $assessmentRepository->findBy(
            [
                'schoolClass' => $user->getSchoolClass(),
            ],
            [
                'date' => 'ASC',
            ]
        );

        foreach ($assessments as $assessment) {
            $stat = $statRepo->findOneBy([
                'entityType' => 'assessment',
                'entityId' => $assessment->getId(),
            ]);

            if (! $stat) {
                return $this->redirectToRoute('stats_stat');
            }

            $assessment->stats = $stat->getData();
        }

        // Regroupe par Category / Theme
        $groupedByTheme = [];
        foreach ($assessments as $assessment) {
            $category = $assessment->getCategory();
            $parentCategory = $assessment->getCategory()
                ->getParent();
            $theme = $parentCategory->getTheme();

            $themeStat = $statRepo->findOneBy([
                'entityType' => 'theme',
                'entityId' => $theme->getId(),
            ]);

            $theme->stats = $themeStat->getData();

            $parentCategoryStat = $statRepo->findOneBy([
                'entityType' => 'category',
                'entityId' => $parentCategory->getId(),
            ]);

            $parentCategory->stats = $parentCategoryStat->getData();

            $categoryStat = $statRepo->findOneBy([
                'entityType' => 'category',
                'entityId' => $category->getId(),
            ]);

            $category->stats = $categoryStat->getData();

            if (! isset($groupedByTheme[$theme->getId()])) {
                $groupedByTheme[$theme->getId()] = [
                    'theme' => $theme,
                    'categories' => [],
                ];
            }
            if (! isset($groupedByTheme[$theme->getId()]['categories'][$parentCategory->getId()])) {
                $groupedByTheme[$theme->getId()]['categories'][$parentCategory->getId()] = [
                    'category' => $parentCategory,
                    'categories' => [],
                ];
            }
            if (! isset($groupedByTheme[$theme->getId()]['categories'][$parentCategory->getId()]['categories'][$category->getId()])) {
                $groupedByTheme[$theme->getId()]['categories'][$parentCategory->getId()]['categories'][$category->getId()] = [
                    'category' => $category,
                    'assessments' => [],
                ];
            }
            $groupedByTheme[$theme->getId()]['categories'][$parentCategory->getId()]['categories'][$category->getId()]['assessments'][] = $assessment;
        }

        ksort($groupedByTheme, SORT_NATURAL | SORT_FLAG_CASE);

        foreach ($groupedByTheme as $categories) {
            foreach ($categories['categories'] as $subcategories) {
                foreach ($subcategories['categories'] as $assessments) {
                    foreach ($assessments['assessments'] as $assessment) {
                        $scores = $assessment->getScores()
                            ->toArray();
                        usort($scores, function ($a, $b) {
                            return strcmp($a->getChild()->getLastName(), $b->getChild()->getLastName())
                                ?: strcmp($a->getChild()->getFirstName(), $b->getChild()->getFirstName());
                        });
                        $assessment->setScores($scores);
                    }
                }
            }
        }

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
