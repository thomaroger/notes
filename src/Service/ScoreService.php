<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Assessment;
use App\Entity\Child;
use App\Entity\Score;
use App\Repository\AssessmentRepository;
use App\Repository\ChildRepository;
use App\Repository\ScoreRepository;
use App\Repository\StatRepository;
use Doctrine\ORM\EntityManagerInterface;

class ScoreService
{
    public function __construct(
        private readonly ScoreRepository $scoreRepository,
        private readonly StatRepository $statRepository,
        private readonly ChildRepository $childRepository,
        private readonly AssessmentRepository $assessmentRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Met à jour ou crée un score depuis une requête
     *
     * @return array{status: string, message?: string, score?: float|null}
     */
    public function updateScoreFromRequest(array $requestData): array
    {
        $childId = $requestData['childId'] ?? null;
        $assessmentId = $requestData['assessmentId'] ?? null;

        if (! $childId || ! $assessmentId) {
            return [
                'status' => 'error',
                'message' => 'Paramètres manquants',
            ];
        }

        $child = $this->childRepository->find($childId);
        $assessment = $this->assessmentRepository->find($assessmentId);

        if (! $child || ! $assessment) {
            return [
                'status' => 'error',
                'message' => 'Élève ou évaluation introuvable',
            ];
        }

        // Gestion de l'absence
        if (isset($requestData['absent'])) {
            $absent = (bool) $requestData['absent'];
            $this->updateOrCreateScore($child, $assessment, null, $absent);

            return [
                'status' => 'success',
                'message' => $absent ? 'Absent' : 'Présent',
            ];
        }

        // Gestion de la note
        if (isset($requestData['score'])) {
            $scoreValue = $requestData['score'];

            if (! is_numeric($scoreValue)) {
                return [
                    'status' => 'error',
                    'message' => 'La note doit être un nombre',
                ];
            }

            $scoreValue = (float) $scoreValue;

            if (! $this->validateScore($scoreValue, $assessment)) {
                return [
                    'status' => 'error',
                    'message' => "La note doit être entre 0 et {$assessment->getMaxScore()}",
                ];
            }

            $score = $this->updateOrCreateScore($child, $assessment, $scoreValue, false);

            return [
                'status' => 'success',
                'score' => $score->getScore(),
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Aucune donnée à enregistrer',
        ];
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
