<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AssessmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AssessmentRepository::class)]
class Assessment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'assessments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SchoolClass $schoolClass = null;

    #[ORM\ManyToOne(inversedBy: 'assessments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column]
    #[Assert\Range(min: 1, max: 20, notInRangeMessage: 'La notation maximale doit être entre 1 et 20.')]
    private ?int $maxScore = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull(message: 'La date de l’évaluation est obligatoire.')]
    private ?\DateTimeInterface $date = null;

    #[ORM\OneToMany(targetEntity: Score::class, mappedBy: 'assessment', orphanRemoval: true)]
    private Collection $scores;

    public function __construct()
    {
        $this->scores = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf('%s (%s/%d)', $this->title ?? '', $this->schoolClass?->__toString() ?? '', $this->maxScore ?? 0);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSchoolClass(): ?SchoolClass
    {
        return $this->schoolClass;
    }

    public function setSchoolClass(?SchoolClass $schoolClass): static
    {
        $this->schoolClass = $schoolClass;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getMaxScore(): ?int
    {
        return $this->maxScore;
    }

    public function setMaxScore(int $maxScore): static
    {
        $this->maxScore = $maxScore;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return Collection<int, Score>
     */
    public function getScores(): Collection
    {
        return $this->scores;
    }

    public function setScores(array $scores): static
    {
        $this->scores = new ArrayCollection($scores);
        return $this;
    }

    public function addScore(Score $score): static
    {
        if (! $this->scores->contains($score)) {
            $this->scores->add($score);
            $score->setAssessment($this);
        }

        return $this;
    }

    public function removeScore(Score $score): static
    {
        if ($this->scores->removeElement($score)) {
            // set the owning side to null (unless already changed)
            if ($score->getAssessment() === $this) {
                $score->setAssessment(null);
            }
        }

        return $this;
    }
}
