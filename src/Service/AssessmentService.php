<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Assessment;
use App\Entity\SchoolClass;
use App\Repository\AssessmentRepository;
use App\Repository\StatRepository;

class AssessmentService
{
    public function __construct(
        private readonly AssessmentRepository $assessmentRepository,
        private readonly StatRepository $statRepository
    ) {
    }

    /**
     * Récupère les évaluations d'une classe et les enrichit avec les statistiques
     *
     * @return Assessment[]
     */
    public function getAssessmentsWithStats(SchoolClass $schoolClass): array
    {
        $assessments = $this->assessmentRepository->findBySchoolClass($schoolClass);

        foreach ($assessments as $assessment) {
            $stat = $this->statRepository->findByEntityTypeAndId('assessment', $assessment->getId());
            if ($stat) {
                $assessment->stats = $stat->getData();
            }
        }

        return $assessments;
    }

    /**
     * Groupe les évaluations par thème/catégorie et enrichit avec les stats
     *
     * @param Assessment[] $assessments
     * @return array<int, array{theme: object, categories: array}>
     */
    public function groupAssessmentsByTheme(array $assessments): array
    {
        $groupedByTheme = [];

        foreach ($assessments as $assessment) {
            $category = $assessment->getCategory();
            $parentCategory = $category->getParent();
            $theme = $parentCategory->getTheme();

            // Enrichit le thème avec ses stats
            $themeStat = $this->statRepository->findByEntityTypeAndId('theme', $theme->getId());
            if ($themeStat) {
                $theme->stats = $themeStat->getData();
            }

            // Enrichit la catégorie parente avec ses stats
            $parentCategoryStat = $this->statRepository->findByEntityTypeAndId('category', $parentCategory->getId());
            if ($parentCategoryStat) {
                $parentCategory->stats = $parentCategoryStat->getData();
            }

            // Enrichit la catégorie avec ses stats
            $categoryStat = $this->statRepository->findByEntityTypeAndId('category', $category->getId());
            if ($categoryStat) {
                $category->stats = $categoryStat->getData();
            }

            // Construction de la structure hiérarchique
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

        // Tri par ordre naturel
        ksort($groupedByTheme, SORT_NATURAL | SORT_FLAG_CASE);

        return $groupedByTheme;
    }

    /**
     * Trie les scores d'une évaluation par nom et prénom de l'enfant
     */
    public function sortScoresByChildName(Assessment $assessment): void
    {
        $scores = $assessment->getScores()
            ->toArray();
        usort($scores, function ($a, $b) {
            return strcmp($a->getChild()->getLastName(), $b->getChild()->getLastName())
                ?: strcmp($a->getChild()->getFirstName(), $b->getChild()->getFirstName());
        });
        $assessment->setScores($scores);
    }

    /**
     * Trie les scores de toutes les évaluations dans une structure groupée
     *
     * @param array<int, array{theme: object, categories: array}> $groupedByTheme
     */
    public function sortAllScoresInGroupedStructure(array &$groupedByTheme): void
    {
        foreach ($groupedByTheme as $categories) {
            foreach ($categories['categories'] as $subcategories) {
                foreach ($subcategories['categories'] as $assessments) {
                    foreach ($assessments['assessments'] as $assessment) {
                        $this->sortScoresByChildName($assessment);
                    }
                }
            }
        }
    }
}
