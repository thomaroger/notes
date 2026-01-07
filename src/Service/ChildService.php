<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Assessment;
use App\Entity\Child;
use App\Repository\AssessmentRepository;
use App\Repository\ChildRepository;

class ChildService
{
    public function __construct(
        private readonly ChildRepository $childRepository,
        private readonly AssessmentRepository $assessmentRepository
    ) {
    }

    /**
     * Récupère tous les enfants triés par nom de famille
     *
     * @return Child[]
     */
    public function getAllChildrenSorted(): array
    {
        return $this->childRepository->findBy([], [
            'lastName' => 'ASC',
        ]);
    }

    /**
     * Récupère un enfant par son ID ou le premier enfant disponible
     */
    public function getChildByIdOrFirst(?int $childId): ?Child
    {
        if ($childId) {
            return $this->childRepository->find($childId);
        }

        $children = $this->getAllChildrenSorted();
        return $children[0] ?? null;
    }

    /**
     * Filtre les évaluations pour ne garder que celles où l'enfant a un score
     *
     * @param Assessment[] $assessments
     * @return Assessment[]
     */
    public function filterAssessmentsWithChildScores(array $assessments, Child $child): array
    {
        return array_filter($assessments, function ($assessment) use ($child) {
            foreach ($child->getScores() as $score) {
                if ($score->getAssessment()->getId() === $assessment->getId()) {
                    return true; // a une note ou absent
                }
            }
            return false; // pas de note et pas absent → on exclut
        });
    }

    /**
     * Trie les enfants par nom de famille
     *
     * @param Child[] $children
     * @return Child[]
     */
    public function sortChildrenByLastName(array $children): array
    {
        usort($children, fn ($a, $b) => strcmp($a->getLastName(), $b->getLastName()));
        return $children;
    }
}
