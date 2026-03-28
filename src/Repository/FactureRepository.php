<?php

namespace App\Repository;

use App\Entity\Facture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FactureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Facture::class);
    }

    public function generateNumero(): string
    {
        $year = date('Y');
        $prefix = "FAC-{$year}-";

        $last = $this->createQueryBuilder('f')
            ->select('f.numero')
            ->where('f.numero LIKE :prefix')
            ->setParameter('prefix', $prefix . '%')
            ->orderBy('f.numero', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($last && $last['numero']) {
            $lastNumber = (int) substr($last['numero'], strlen($prefix));
            $next = $lastNumber + 1;
        } else {
            $next = 1;
        }

        return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
