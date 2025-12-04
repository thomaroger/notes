<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SchoolClassRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SchoolClassRepository::class)]
class SchoolClass
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $year = null;

    #[ORM\Column(length: 10)]
    private ?string $level = null;

    #[ORM\OneToMany(targetEntity: Child::class, mappedBy: 'schoolClass', orphanRemoval: true)]
    private Collection $children;

    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'schoolClass')]
    private Collection $users;

    #[ORM\OneToMany(targetEntity: Assessment::class, mappedBy: 'schoolClass', orphanRemoval: true)]
    private Collection $assessments;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->assessments = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf('%s - %s', $this->year ?? '', $this->level ?? '');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getYear(): ?string
    {
        return $this->year;
    }

    public function setYear(string $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(string $level): static
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return Collection<int, Child>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(Child $child): static
    {
        if (! $this->children->contains($child)) {
            $this->children->add($child);
            $child->setSchoolClass($this);
        }

        return $this;
    }

    public function removeChild(Child $child): static
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getSchoolClass() === $this) {
                $child->setSchoolClass(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (! $this->users->contains($user)) {
            $this->users->add($user);
            $user->setSchoolClass($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getSchoolClass() === $this) {
                $user->setSchoolClass(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Assessment>
     */
    public function getAssessments(): Collection
    {
        return $this->assessments;
    }

    public function addAssessment(Assessment $assessment): static
    {
        if (! $this->assessments->contains($assessment)) {
            $this->assessments->add($assessment);
            $assessment->setSchoolClass($this);
        }

        return $this;
    }

    public function removeAssessment(Assessment $assessment): static
    {
        if ($this->assessments->removeElement($assessment)) {
            // set the owning side to null (unless already changed)
            if ($assessment->getSchoolClass() === $this) {
                $assessment->setSchoolClass(null);
            }
        }

        return $this;
    }
}
