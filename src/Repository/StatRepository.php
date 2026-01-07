<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Stat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Stat>
 */
class StatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stat::class);
    }

    public function findByEntityTypeAndId(string $entityType, int $entityId): ?Stat
    {
        return $this->findOneBy([
            'entityType' => $entityType,
            'entityId' => $entityId,
        ]);
    }

    public function truncateAll(): void
    {
        $connection = $this->getEntityManager()
            ->getConnection();
        $connection->executeStatement('TRUNCATE TABLE stat;');
    }
}
