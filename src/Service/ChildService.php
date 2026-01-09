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

    /**
     * Génère un commentaire en fonction du pourcentage de réussite par thème
     * Format adapté pour les livrets scolaires
     *
     * @param float|null $percentage Le pourcentage de réussite (0-100) ou null si aucune note
     * @param string $themeName Le nom du thème
     * @param string $childFirstName Le prénom de l'enfant
     * @return string Le commentaire approprié
     */
    public function getThemeComment(?float $percentage, string $themeName, string $childFirstName): string
    {
        if ($percentage === null || $percentage === 0) {
            return sprintf(
                'Dans le domaine "%s", %s n\'a pas encore été évalué(e) sur suffisamment d\'évaluations pour établir un bilan.',
                $themeName,
                $childFirstName
            );
        }

        if ($percentage < 40) {
            return sprintf(
                'Dans le domaine "%s", %s rencontre des difficultés importantes. Les résultats obtenus sont insuffisants et nécessitent un travail de remise à niveau. Des efforts soutenus seront nécessaires pour progresser dans ce domaine.',
                $themeName,
                $childFirstName
            );
        }

        if ($percentage <= 75) {
            return sprintf(
                'Dans le domaine "%s", %s montre une maîtrise partielle des compétences. Les résultats sont satisfaisants mais peuvent être améliorés. Des efforts supplémentaires et un travail régulier permettront de consolider les acquis et de progresser.',
                $themeName,
                $childFirstName
            );
        }

        return sprintf(
            'Dans le domaine "%s", %s démontre une très bonne maîtrise des compétences. Les résultats sont excellents et témoignent d\'un travail sérieux et régulier. Félicitations pour cette belle réussite.',
            $themeName,
            $childFirstName
        );
    }
}
