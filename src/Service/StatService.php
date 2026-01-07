<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Assessment;
use App\Entity\Category;
use App\Entity\Stat;
use App\Entity\Theme;
use App\Repository\AssessmentRepository;
use App\Repository\StatRepository;
use App\Repository\ThemeRepository;
use Doctrine\ORM\EntityManagerInterface;

class StatService
{
    public function __construct(
        private readonly ThemeRepository $themeRepository,
        private readonly StatRepository $statRepository,
        private readonly AssessmentRepository $assessmentRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Calcule et crée toutes les statistiques pour toutes les entités
     *
     * @return int Nombre de statistiques créées
     */
    public function computeAllStats(): int
    {
        $countAssessments = count($this->assessmentRepository->findAll());
        $themes = $this->themeRepository->findAll();

        foreach ($themes as $theme) {
            // Calcule d'abord toutes les stats des catégories et évaluations
            $themeData = $this->computeThemeStats($theme, $countAssessments);
            // Crée la stat du thème
            $existing = $this->statRepository->findByEntityTypeAndId('theme', $theme->getId());
            if (! $existing) {
                $themeStat = new Stat('theme', $theme->getId(), $themeData);
                $this->entityManager->persist($themeStat);
            }
        }
        $this->entityManager->flush();
        $created = $this->statRepository->count([]);
        return $created;
    }

    /**
     * Calcule les statistiques d'un thème et de toutes ses catégories
     */
    private function computeThemeStats(Theme $theme, int $countAssessments): array
    {
        $themeTotal = 0;
        $themePresent = 0;
        $themeAbsent = 0;
        $themeAvg = 0;
        $themeLess40 = 0;
        $themeBetween41_75 = 0;
        $themeMore75 = 0;
        $themeMaxScore = 0;
        $themeAssessments = 0;

        foreach ($theme->getCategories() as $category) {
            if ($category->hasParent()) {
                continue; // On traite seulement les catégories parentes
            }

            $parentCategoryData = $this->computeParentCategoryStats($category, $countAssessments);
            $themeTotal += $parentCategoryData['total'];
            $themePresent += $parentCategoryData['present'];
            $themeAbsent += $parentCategoryData['absent'];
            $themeAvg += $parentCategoryData['avg'] * $parentCategoryData['numberAssessment'];
            $themeLess40 += $parentCategoryData['less40'];
            $themeBetween41_75 += $parentCategoryData['between41_75'];
            $themeMore75 += $parentCategoryData['more75'];
            $themeMaxScore += $parentCategoryData['maxscore'] * $parentCategoryData['numberAssessment'];
            $themeAssessments += $parentCategoryData['numberAssessment'];
        }

        $themeData = [];
        $themeData['numberAssessment'] = $themeAssessments;
        $themeData['allAssessment'] = $countAssessments;
        $themeData['total'] = $themeTotal;
        $themeData['present'] = $themePresent;
        $themeData['absent'] = $themeAbsent;
        $themeData['avg'] = $themeAssessments !== 0 ? $themeAvg / $themeAssessments : 0;
        $themeData['less40'] = $themeLess40;
        $themeData['between41_75'] = $themeBetween41_75;
        $themeData['more75'] = $themeMore75;
        $themeData['maxscore'] = $themeAssessments !== 0 ? $themeMaxScore / $themeAssessments : 0;

        return $themeData;
    }

    /**
     * Calcule les statistiques d'une catégorie parente et de toutes ses sous-catégories
     */
    private function computeParentCategoryStats(Category $parentCategory, int $countAssessments): array
    {
        $parentCategorytotal = 0;
        $parentCategoryPresent = 0;
        $parentCategoryAbsent = 0;
        $parentCategoryAvg = 0;
        $parentCategoryLess40 = 0;
        $parentCategoryBetween41_75 = 0;
        $parentCategoryMore75 = 0;
        $parentCategoryMaxScore = 0;
        $countParentAssessments = 0;

        foreach ($parentCategory->getChildren() as $subcategory) {
            $subCategoryData = $this->computeSubCategoryStats($subcategory, $countAssessments);

            $countParentAssessments += $subCategoryData['numberAssessment'];
            $parentCategorytotal += $subCategoryData['total'];
            $parentCategoryPresent += $subCategoryData['present'];
            $parentCategoryAbsent += $subCategoryData['absent'];
            $parentCategoryAvg += $subCategoryData['avg'] * $subCategoryData['numberAssessment'];
            $parentCategoryLess40 += $subCategoryData['less40'];
            $parentCategoryBetween41_75 += $subCategoryData['between41_75'];
            $parentCategoryMore75 += $subCategoryData['more75'];
            $parentCategoryMaxScore += $subCategoryData['maxscore'] * $subCategoryData['numberAssessment'];
        }

        $parentCategoryData = [];
        $parentCategoryData['numberAssessment'] = $countParentAssessments;
        $parentCategoryData['allAssessment'] = $countAssessments;
        $parentCategoryData['total'] = $parentCategorytotal;
        $parentCategoryData['present'] = $parentCategoryPresent;
        $parentCategoryData['absent'] = $parentCategoryAbsent;
        $parentCategoryData['avg'] = $countParentAssessments !== 0 ? $parentCategoryAvg / $countParentAssessments : 0;
        $parentCategoryData['less40'] = $parentCategoryLess40;
        $parentCategoryData['between41_75'] = $parentCategoryBetween41_75;
        $parentCategoryData['more75'] = $parentCategoryMore75;
        $parentCategoryData['maxscore'] = $countParentAssessments !== 0 ? $parentCategoryMaxScore / $countParentAssessments : 0;

        // Création de la stat pour la catégorie parente
        $existing = $this->statRepository->findByEntityTypeAndId('category', $parentCategory->getId());
        if (! $existing) {
            $parentCategoryStat = new Stat('category', $parentCategory->getId(), $parentCategoryData);
            $this->entityManager->persist($parentCategoryStat);
            $this->entityManager->flush();
        }

        return $parentCategoryData;
    }

    /**
     * Calcule les statistiques d'une sous-catégorie et de toutes ses évaluations
     */
    private function computeSubCategoryStats(Category $subcategory, int $countAssessments): array
    {
        $subCategorytotal = 0;
        $subCategoryPresent = 0;
        $subCategoryAbsent = 0;
        $subCategoryAvg = 0;
        $subCategoryLess40 = 0;
        $subCategoryBetween41_75 = 0;
        $subCategoryMore75 = 0;
        $subCategoryMaxScore = 0;

        foreach ($subcategory->getAssessments() as $assessment) {
            $assessmentData = $this->computeAssessmentStats($assessment);

            $subCategorytotal += $assessmentData['total'];
            $subCategoryPresent += $assessmentData['present'];
            $subCategoryAbsent += $assessmentData['absent'];
            $subCategoryAvg += $assessmentData['avg'];
            $subCategoryLess40 += $assessmentData['less40'];
            $subCategoryBetween41_75 += $assessmentData['between41_75'];
            $subCategoryMore75 += $assessmentData['more75'];
            $subCategoryMaxScore += $assessment->getMaxScore();
        }

        $subCategoryData = [];
        $subCategoryData['numberAssessment'] = $subcategory->getAssessments()->count();
        $subCategoryData['allAssessment'] = $countAssessments;
        $subCategoryData['total'] = $subCategorytotal;
        $subCategoryData['present'] = $subCategoryPresent;
        $subCategoryData['absent'] = $subCategoryAbsent;
        $subCategoryData['avg'] = $subCategoryData['numberAssessment'] !== 0 ? $subCategoryAvg / $subCategoryData['numberAssessment'] : 0;
        $subCategoryData['less40'] = $subCategoryLess40;
        $subCategoryData['between41_75'] = $subCategoryBetween41_75;
        $subCategoryData['more75'] = $subCategoryMore75;
        $subCategoryData['maxscore'] = $subCategoryData['numberAssessment'] !== 0 ? $subCategoryMaxScore / $subCategoryData['numberAssessment'] : 0;

        // Création de la stat pour la sous-catégorie
        $existing = $this->statRepository->findByEntityTypeAndId('category', $subcategory->getId());
        if (! $existing) {
            $subCategoryStat = new Stat('category', $subcategory->getId(), $subCategoryData);
            $this->entityManager->persist($subCategoryStat);
            $this->entityManager->flush();
        }

        return $subCategoryData;
    }

    /**
     * Calcule les statistiques d'une évaluation
     */
    private function computeAssessmentStats(Assessment $assessment): array
    {
        $existing = $this->statRepository->findByEntityTypeAndId('assessment', $assessment->getId());
        if ($existing) {
            // Si la stat existe déjà, on retourne ses données
            return $existing->getData();
        }

        $scores = $assessment->getScores();
        $total = $assessment->getSchoolClass()
            ->getChildren()
            ->count();
        $present = $scores->filter(fn ($s) => $s->getScore() !== null)
            ->count();
        $absent = $scores->filter(fn ($s) => $s->isAbsent() === true)
            ->count();

        $scoresValues = $scores
            ->filter(fn ($s) => $s->getScore() !== null)
            ->map(fn ($s) => $s->getScore())
            ->toArray();

        $min = $scoresValues ? min($scoresValues) : 0;
        $max = $scoresValues ? max($scoresValues) : 0;
        $avg = $scoresValues ? array_sum($scoresValues) / count($scoresValues) : 0;

        $less40 = count(array_filter($scoresValues, fn ($s) => $s < 0.41 * $assessment->getMaxScore()));
        $between41_75 = count(
            array_filter(
                $scoresValues,
                fn ($s) => $s >= 0.41 * $assessment->getMaxScore() && $s <= 0.75 * $assessment->getMaxScore()
            )
        );
        $more75 = count(array_filter($scoresValues, fn ($s) => $s > 0.75 * $assessment->getMaxScore()));

        if ($total === 0) {
            $total = 1;
        }

        $less40child = ' ';
        $between41_75child = ' ';
        $more75child = ' ';
        $absentchild = ' ';

        foreach ($assessment->getScores() as $score) {
            $child = $score->getChild();
            if ($score->isAbsent() === true) {
                $absentchild .= $child->getFirstName() . ' ' . $child->getLastName() . ',';
            } elseif (($score->getScore() / $assessment->getMaxScore()) * 100 <= 40) {
                $less40child .= $child->getFirstName() . ' ' . $child->getLastName() . ',';
            } elseif (($score->getScore() / $assessment->getMaxScore()) * 100 <= 75) {
                $between41_75child .= $child->getFirstName() . ' ' . $child->getLastName() . ',';
            } else {
                $more75child .= $child->getFirstName() . ' ' . $child->getLastName() . ',';
            }
        }

        $data = [];
        $data['total'] = $total;
        $data['present'] = $present;
        $data['absent'] = $absent;
        $data['min'] = $min;
        $data['max'] = $max;
        $data['avg'] = $avg;
        $data['less40'] = $less40;
        $data['between41_75'] = $between41_75;
        $data['more75'] = $more75;
        $data['less40child'] = trim($less40child, ',');
        $data['between41_75child'] = trim($between41_75child, ',');
        $data['more75child'] = trim($more75child, ',');
        $data['absentchild'] = trim($absentchild, ',');

        // Création de la stat pour l'évaluation
        $stat = new Stat('assessment', $assessment->getId(), $data);
        $this->entityManager->persist($stat);
        $this->entityManager->flush();

        return $data;
    }
}
