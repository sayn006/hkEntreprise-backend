<?php

namespace App\Repository;

use App\Entity\FactureSituationCoordonneesBancaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FactureSituationCoordonneesBancaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FactureSituationCoordonneesBancaire::class);
    }
}
