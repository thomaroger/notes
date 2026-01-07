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
     * Vérifie si toutes les évaluations ont des statistiques
     *
     * @param Assessment[] $assessments
     * @return bool True si toutes les évaluations ont des stats, false sinon
     */
    public function allAssessmentsHaveStats(array $assessments): bool
    {
        foreach ($assessments as $assessment) {
            $stat = $this->statRepository->findByEntityTypeAndId('assessment', $assessment->getId());
            if (! $stat) {
                return false;
            }
        }

        return true;
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

            // Enrichit les entités avec leurs stats
            $this->enrichEntityWithStats($theme, 'theme');
            $this->enrichEntityWithStats($parentCategory, 'category');
            $this->enrichEntityWithStats($category, 'category');

            // Construit la structure hiérarchique
            $this->addAssessmentToGroupedStructure($groupedByTheme, $theme, $parentCategory, $category, $assessment);
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

    /**
     * Enrichit une entité avec ses statistiques
     */
    private function enrichEntityWithStats(object $entity, string $entityType): void
    {
        $stat = $this->statRepository->findByEntityTypeAndId($entityType, $entity->getId());
        if ($stat) {
            $entity->stats = $stat->getData();
        }
    }

    /**
     * Ajoute une évaluation à la structure groupée
     *
     * @param array<int, array{theme: object, categories: array}> $groupedByTheme
     */
    private function addAssessmentToGroupedStructure(
        array &$groupedByTheme,
        object $theme,
        object $parentCategory,
        object $category,
        Assessment $assessment
    ): void {
        $themeId = $theme->getId();
        $parentCategoryId = $parentCategory->getId();
        $categoryId = $category->getId();

        // Initialise le thème si nécessaire
        if (! isset($groupedByTheme[$themeId])) {
            $groupedByTheme[$themeId] = [
                'theme' => $theme,
                'categories' => [],
            ];
        }

        // Initialise la catégorie parente si nécessaire
        if (! isset($groupedByTheme[$themeId]['categories'][$parentCategoryId])) {
            $groupedByTheme[$themeId]['categories'][$parentCategoryId] = [
                'category' => $parentCategory,
                'categories' => [],
            ];
        }

        // Initialise la catégorie si nécessaire
        if (! isset($groupedByTheme[$themeId]['categories'][$parentCategoryId]['categories'][$categoryId])) {
            $groupedByTheme[$themeId]['categories'][$parentCategoryId]['categories'][$categoryId] = [
                'category' => $category,
                'assessments' => [],
            ];
        }

        // Ajoute l'évaluation
        $groupedByTheme[$themeId]['categories'][$parentCategoryId]['categories'][$categoryId]['assessments'][] = $assessment;
    }
}
