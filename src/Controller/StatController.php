<?php

declare(strict_types=1);

// src/Controller/ChildController.php

namespace App\Controller;

use App\Entity\Stat;
use App\Repository\AssessmentRepository;
use App\Repository\StatRepository;
use App\Repository\ThemeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatController extends AbstractController
{
    #[Route('/stats/stat', name: 'stats_stat')]
    public function notes(
        ThemeRepository $themeRepository,
        StatRepository $statRepo,
        AssessmentRepository $assessmentRepository,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();
        if (! $user) {
            return $this->redirectToRoute('app_login');
        }

        $countAssessments = count($assessmentRepository->findAll());
        $themes = $themeRepository->findAll();
        $created = 0;
        foreach ($themes as $theme) {

            $themeData = [];
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

                $parentCategoryData = [];
                $parentCategorytotal = 0;
                $parentCategoryPresent = 0;
                $parentCategoryAbsent = 0;
                $parentCategoryAvg = 0;
                $parentCategoryLess40 = 0;
                $parentCategoryBetween41_75 = 0;
                $parentCategoryMore75 = 0;
                $parentCategoryMaxScore = 0;
                $countParentAssessments = 0;

                foreach ($category->getChildren() as $subcategory) {

                    $subCategoryData = [];
                    $subCategorytotal = 0;
                    $subCategoryPresent = 0;
                    $subCategoryAbsent = 0;
                    $subCategoryAvg = 0;
                    $subCategoryLess40 = 0;
                    $subCategoryBetween41_75 = 0;
                    $subCategoryMore75 = 0;
                    $subCategoryMaxScore = 0;

                    foreach ($subcategory->getAssessments() as $assessment) {
                        $less40child = ' ';
                        $between41_75child = ' ';
                        $more75child = ' ';
                        $absentchild = ' ';
                        $existing = $statRepo->findOneBy([
                            'entityType' => 'assessment',
                            'entityId' => $assessment->getId(),
                        ]);

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

                        foreach ($assessment->getScores() as $score) {
                            $child = $score->getChild();
                            if ($score->isAbsent() === true) {
                                $absentchild .= $child->getFirstname() . ' ' . $child->getLastname() . ',';
                            } elseif (($score->getScore() / $assessment->getMaxScore()) * 100 <= 40) {
                                $less40child .= $child->getFirstname() . ' ' . $child->getLastname() . ',';
                            } elseif (($score->getScore() / $assessment->getMaxScore()) * 100 <= 75) {
                                $between41_75child .= $child->getFirstname() . ' ' . $child->getLastname() . ',';
                            } else {
                                $more75child .= $child->getFirstname() . ' ' . $child->getLastname() . ',';
                            }

                        }

                        // Calcul des stats via le service dédié
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

                        $subCategorytotal += $total;
                        $subCategoryPresent += $present;
                        $subCategoryAbsent += $absent;
                        $subCategoryAvg += $avg;
                        $subCategoryLess40 += $less40;
                        $subCategoryBetween41_75 += $between41_75;
                        $subCategoryMore75 += $more75;
                        $subCategoryMaxScore += $assessment->getMaxScore();

                        $parentCategorytotal += $total;
                        $parentCategoryPresent += $present;
                        $parentCategoryAbsent += $absent;
                        $parentCategoryAvg += $avg;
                        $parentCategoryLess40 += $less40;
                        $parentCategoryBetween41_75 += $between41_75;
                        $parentCategoryMore75 += $more75;
                        $parentCategoryMaxScore += $assessment->getMaxScore();

                        $themeTotal += $total;
                        $themePresent += $present;
                        $themeAbsent += $absent;
                        $themeAvg += $avg;
                        $themeLess40 += $less40;
                        $themeBetween41_75 += $between41_75;
                        $themeMore75 += $more75;
                        $themeMaxScore += $assessment->getMaxScore();

                        // Création de la stat
                        if ($existing) {
                            continue;
                        }
                        $stat = new Stat('assessment', $assessment->getId(), $data);
                        $em->persist($stat);

                        $em->flush();

                        $created++;

                    }

                    $existing = $statRepo->findOneBy([
                        'entityType' => 'category',
                        'entityId' => $subcategory->getId(),
                    ]);

                    $subCategoryData['numberAssessment'] = $subcategory->getAssessments()->count();
                    $countParentAssessments += $subCategoryData['numberAssessment'];
                    $themeAssessments += $subCategoryData['numberAssessment'];
                    $subCategoryData['allAssessment'] = $countAssessments;
                    $subCategoryData['total'] = $subCategorytotal;
                    $subCategoryData['present'] = $subCategoryPresent;
                    $subCategoryData['absent'] = $subCategoryAbsent;
                    $subCategoryData['avg'] = $subCategoryData['numberAssessment'] !== 0 ? $subCategoryAvg / $subCategoryData['numberAssessment'] : 0;
                    $subCategoryData['less40'] = $subCategoryLess40;
                    $subCategoryData['between41_75'] = $subCategoryBetween41_75;
                    $subCategoryData['more75'] = $subCategoryMore75;
                    $subCategoryData['maxscore'] = $subCategoryData['numberAssessment'] !== 0 ? $subCategoryMaxScore / $subCategoryData['numberAssessment'] : 0;

                    if ($existing) {
                        continue;
                    }
                    $subCategoryStat = new Stat('category', $subcategory->getId(), $subCategoryData);
                    $em->persist($subCategoryStat);

                    $em->flush();

                    $created++;
                }

                $existing = $statRepo->findOneBy([
                    'entityType' => 'category',
                    'entityId' => $category->getId(),
                ]);

                $parentCategoryData['numberAssessment'] = $countParentAssessments;
                $parentCategoryData['allAssessment'] = $countAssessments;
                $parentCategoryData['total'] = $parentCategorytotal;
                $parentCategoryData['present'] = $parentCategoryPresent;
                $parentCategoryData['absent'] = $parentCategoryAbsent;
                $parentCategoryData['avg'] = $parentCategoryData['numberAssessment'] !== 0 ? $parentCategoryAvg / $parentCategoryData['numberAssessment'] : 0;
                $parentCategoryData['less40'] = $parentCategoryLess40;
                $parentCategoryData['between41_75'] = $parentCategoryBetween41_75;
                $parentCategoryData['more75'] = $parentCategoryMore75;
                $parentCategoryData['maxscore'] = $parentCategoryData['numberAssessment'] !== 0 ? $parentCategoryMaxScore / $parentCategoryData['numberAssessment'] : 0;

                if ($existing) {
                    continue;
                }
                $parentCategoryStat = new Stat('category', $category->getId(), $parentCategoryData);
                $em->persist($parentCategoryStat);

                $em->flush();

                $created++;
            }

            $existing = $statRepo->findOneBy([
                'entityType' => 'theme',
                'entityId' => $theme->getId(),
            ]);

            $themeData['numberAssessment'] = $themeAssessments;
            $themeData['allAssessment'] = $countAssessments;
            $themeData['total'] = $themeTotal;
            $themeData['present'] = $themePresent;
            $themeData['absent'] = $themeAbsent;
            $themeData['avg'] = $themeData['numberAssessment'] !== 0 ? $themeAvg / $themeData['numberAssessment'] : 0;
            $themeData['less40'] = $themeLess40;
            $themeData['between41_75'] = $themeBetween41_75;
            $themeData['more75'] = $themeMore75;
            $themeData['maxscore'] = $themeData['numberAssessment'] !== 0 ? $themeMaxScore / $themeData['numberAssessment'] : 0;

            if ($existing) {
                continue;
            }
            $themeStat = new Stat('theme', $theme->getId(), $themeData);
            $em->persist($themeStat);

            $created++;

        }

        $em->flush();

        $this->addFlash('success', "{$created} statistiques créées.");

        return $this->redirectToRoute('app_home');
    }
}
