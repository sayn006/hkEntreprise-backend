<?php

namespace App\Repository;

use App\Entity\ChantierSousTraitent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ChantierSousTraitentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChantierSousTraitent::class);
    }
}
