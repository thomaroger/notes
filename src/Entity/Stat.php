<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\StatRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StatRepository::class)]
#[ORM\Table(name: 'stat')]
class Stat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // category | theme | assessment | etc.
    #[ORM\Column(length: 50)]
    private string $entityType;

    // ID de l'entité ciblée
    #[ORM\Column]
    private int $entityId;

    // Données JSON des stats calculées
    #[ORM\Column(type: 'json')]
    private array $data = [];

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $computedAt;

    public function __construct(string $entityType, int $entityId, array $data = [])
    {
        $this->entityType = $entityType;
        $this->entityId = $entityId;
        $this->data = $data;
        $this->computedAt = new \DateTimeImmutable();
    }

    // -------- GETTERS / SETTERS --------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }

    public function setEntityType(string $entityType): self
    {
        $this->entityType = $entityType;
        return $this;
    }

    public function getEntityId(): int
    {
        return $this->entityId;
    }

    public function setEntityId(int $entityId): self
    {
        $this->entityId = $entityId;
        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function addData(string $key, mixed $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }

    public function getDataJsonField(): string
    {
        return json_encode($this->data);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function getComputedAt(): \DateTimeInterface
    {
        return $this->computedAt;
    }

    public function setComputedAt(\DateTimeInterface $computedAt): self
    {
        $this->computedAt = $computedAt;
        return $this;
    }

    public function refreshTimestamp(): self
    {
        $this->computedAt = new \DateTimeImmutable();
        return $this;
    }
}
