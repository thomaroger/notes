<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Assessment;
use App\Entity\Child;
use App\Entity\Score;
use App\Repository\ScoreRepository;
use App\Repository\StatRepository;
use Doctrine\ORM\EntityManagerInterface;

class ScoreService
{
    public function __construct(
        private readonly ScoreRepository $scoreRepository,
        private readonly StatRepository $statRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Met à jour ou crée un score pour un enfant et une évaluation
     */
    public function updateOrCreateScore(
        Child $child,
        Assessment $assessment,
        ?float $score = null,
        bool $absent = false
    ): Score {
        $existingScore = $this->scoreRepository->findOneByChildAndAssessment($child, $assessment);

        if ($existingScore) {
            $scoreEntity = $existingScore;
        } else {
            $scoreEntity = new Score();
            $scoreEntity->setChild($child);
            $scoreEntity->setAssessment($assessment);
        }

        if ($absent) {
            $scoreEntity->setAbsent(true);
            $scoreEntity->setScore(null);
        } else {
            $scoreEntity->setAbsent(false);
            if ($score !== null) {
                $scoreEntity->setScore($score);
            }
        }

        $this->entityManager->persist($scoreEntity);
        $this->entityManager->flush();

        // Invalide les statistiques après modification
        $this->invalidateStats();

        return $scoreEntity;
    }

    /**
     * Valide qu'un score est dans les limites autorisées
     */
    public function validateScore(float $score, Assessment $assessment): bool
    {
        return $score >= 0 && $score <= $assessment->getMaxScore();
    }

    /**
     * Invalide toutes les statistiques en les supprimant
     */
    public function invalidateStats(): void
    {
        $this->statRepository->truncateAll();
    }
}
