<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Assessment;
use App\Entity\SchoolClass;
use Doctrine\ORM\EntityManagerInterface;

class AssessmentManagementService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Crée une nouvelle évaluation pour une classe
     */
    public function createAssessment(SchoolClass $schoolClass): Assessment
    {
        $assessment = new Assessment();
        $assessment->setSchoolClass($schoolClass);

        return $assessment;
    }

    /**
     * Sauvegarde une évaluation
     */
    public function saveAssessment(Assessment $assessment): void
    {
        $this->entityManager->persist($assessment);
        $this->entityManager->flush();
    }
}
