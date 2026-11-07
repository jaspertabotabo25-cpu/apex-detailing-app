<?php

namespace App\Repository;

use App\Entity\Inventory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Inventory>
 */
class InventoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inventory::class);
    }

    /**
     * @return Inventory[]
     */
    public function findLowStockItems(): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.quantity <= i.lowStockThreshold')
            ->orderBy('i.quantity', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

