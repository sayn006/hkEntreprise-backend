<?php

namespace App\Repository;

use App\Entity\IndiceBtp;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IndiceBtp>
 */
class IndiceBtpRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IndiceBtp::class);
    }

    /**
     * Récupère la valeur de l'indice pour un type donné et un mois donné.
     * Fallback : valeur du mois le plus proche antérieur (si la saisie admin
     * prend du retard, on utilise le dernier indice connu).
     */
    public function findForTypeAndMonth(string $type, \DateTimeInterface $mois): ?IndiceBtp
    {
        $first = (clone $mois);
        $first = (new \DateTimeImmutable($first->format('Y-m-01')));

        $qb = $this->createQueryBuilder('i')
            ->where('i.type = :t')
            ->andWhere('i.mois <= :m')
            ->setParameter('t', strtoupper($type))
            ->setParameter('m', $first)
            ->orderBy('i.mois', 'DESC')
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
