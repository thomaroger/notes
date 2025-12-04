<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ScoreRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: ScoreRepository::class)]
#[Assert\Callback('validateScore')]
class Score
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'scores')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Child $child = null;

    #[ORM\ManyToOne(inversedBy: 'scores')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Assessment $assessment = null;

    #[ORM\Column]
    #[Assert\Range(min: 0, notInRangeMessage: 'La note doit être supérieure ou égale à 0.')]
    private ?float $score = null;

    public function __toString(): string
    {
        return sprintf(
            '%s - %s: %.2f/%d',
            $this->child?->__toString() ?? '',
            $this->assessment?->getTitle() ?? '',
            $this->score ?? 0,
            $this->assessment?->getMaxScore() ?? 0
        );
    }

    public function validateScore(ExecutionContextInterface $context): void
    {
        if ($this->assessment && $this->score !== null) {
            $maxScore = $this->assessment->getMaxScore();
            if ($this->score > $maxScore) {
                $context->buildViolation(
                    'La note ne peut pas dépasser la notation maximale de l\'évaluation ({{ max }}).'
                )
                    ->setParameter('{{ max }}', $maxScore)
                    ->atPath('score')
                    ->addViolation();
            }
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChild(): ?Child
    {
        return $this->child;
    }

    public function setChild(?Child $child): static
    {
        $this->child = $child;

        return $this;
    }

    public function getAssessment(): ?Assessment
    {
        return $this->assessment;
    }

    public function setAssessment(?Assessment $assessment): static
    {
        $this->assessment = $assessment;

        return $this;
    }

    public function getScore(): ?float
    {
        return $this->score;
    }

    public function setScore(float $score): static
    {
        $this->score = $score;

        return $this;
    }
}
