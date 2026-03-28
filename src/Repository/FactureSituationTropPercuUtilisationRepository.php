<?php

namespace App\Repository;

use App\Entity\FactureSituationTropPercuUtilisation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FactureSituationTropPercuUtilisationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FactureSituationTropPercuUtilisation::class);
    }
}
