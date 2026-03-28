<?php

namespace App\Repository;

use App\Entity\SousTraitantAvenant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SousTraitantAvenantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SousTraitantAvenant::class);
    }
}
