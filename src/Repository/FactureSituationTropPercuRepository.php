<?php

namespace App\Repository;

use App\Entity\FactureSituationTropPercu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FactureSituationTropPercuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FactureSituationTropPercu::class);
    }
}
