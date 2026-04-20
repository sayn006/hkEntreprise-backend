<?php

namespace App\Controller;

use App\Entity\DevisAvancement;
use App\Entity\DevisAvancementDetail;
use App\Repository\DevisAvancementDetailRepository;
use App\Repository\DevisAvancementRepository;
use App\Repository\DevisDetailRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Crée un nouvel avancement (N+1) à partir d'un avancement source (N).
 *
 * Règle :
 *   - source doit être Validé (EtatAvancementValide) ou Facturé (EtatAvancementFacture)
 *   - N+1 reprend toutes les lignes du devis, avec pourcentageMoins1 = pct cumulé de N
 *     et totalHTMoins1 = totalHT cumulé de N
 *   - numeroOrdre = source.numeroOrdre + 1
 *   - moisReference = source.moisReference + 1 mois
 *   - etat = EtatAvancementEnCours
 */
#[Route('/api/devis-avancements')]
#[IsGranted('ROLE_USER')]
class AvancementNextController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private DevisAvancementRepository $avancementRepo,
        private DevisAvancementDetailRepository $avancementDetailRepo,
        private DevisDetailRepository $devisDetailRepo,
    ) {}

    #[Route('/{id}/create-next', name: 'api_avancement_create_next', methods: ['POST'])]
    public function createNext(DevisAvancement $source): JsonResponse
    {
        if ($source->isDeleted()) {
            return $this->json(['error' => 'Avancement source supprimé.'], 400);
        }

        $etatSource = $source->getEtat();
        if (!in_array($etatSource, ['EtatAvancementValide', 'EtatAvancementFacture'], true)) {
            return $this->json([
                'error' => 'L\'avancement source doit être Validé ou Facturé pour créer le suivant.',
            ], 400);
        }

        $devis = $source->getDevis();
        if (!$devis) {
            return $this->json(['error' => 'Aucun devis lié à l\'avancement source.'], 400);
        }

        // Vérifie qu'il n'existe pas déjà un avancement N+1 (= numeroOrdre supérieur non supprimé)
        $already = $this->avancementRepo->findOneBy([
            'devis'     => $devis,
            'isDeleted' => false,
            'numeroOrdre' => $source->getNumeroOrdre() + 1,
        ]);
        if ($already) {
            return $this->json([
                'message' => 'Avancement suivant déjà existant.',
                'id'      => $already->getId(),
                'numero'  => $already->getNumero(),
            ]);
        }

        $nextOrdre = $source->getNumeroOrdre() + 1;

        // moisReference = +1 mois
        $nextMois = (clone $source->getMoisReference())->modify('+1 month');

        $next = new DevisAvancement();
        $next->setDevis($devis);
        $next->setNumeroOrdre($nextOrdre);
        $next->setNumero('AVA-' . $devis->getNumero() . '-' . str_pad((string) $nextOrdre, 2, '0', STR_PAD_LEFT));
        $next->setMoisReference($nextMois);
        $next->setDateCreation(new \DateTime());
        $next->setEtat('EtatAvancementEnCours');
        $next->setTotalHT(0);
        $next->setTotalCumule((float) $source->getTotalCumule());
        $next->setPourcentageGlobal((float) $source->getPourcentageGlobal());

        if ($user = $this->getUser()) {
            $next->setCreatedBy($user);
        }

        $this->em->persist($next);
        $this->em->flush(); // avoir un id

        // Copie des lignes depuis les détails source (pct cumulé -> pctMoins1 de N+1)
        $sourceDetails = $this->avancementDetailRepo->findBy(['devisAvancement' => $source]);

        foreach ($sourceDetails as $srcDetail) {
            $devisDetail = $srcDetail->getDevisDetail();
            // Certaines lignes (header, etc.) peuvent ne pas avoir de devisDetail, on les duplique quand même
            $nd = new DevisAvancementDetail();
            $nd->setDevisAvancement($next);
            $nd->setDevisDetail($devisDetail);
            $nd->setReference($srcDetail->getReference());
            $nd->setDesignation($srcDetail->getDesignation());
            $nd->setQuantite($srcDetail->getQuantite());
            $nd->setUnite($srcDetail->getUnite());
            $nd->setPrixUnitaire($srcDetail->getPrixUnitaire());
            $nd->setTotalDevis($srcDetail->getTotalDevis());
            $nd->setBlockNumber($srcDetail->getBlockNumber());
            $nd->setLineType($srcDetail->getLineType());
            $nd->setLineSubType($srcDetail->getLineSubType());
            $nd->setDisplayOrder($srcDetail->getDisplayOrder());
            $nd->setOrderInBlock($srcDetail->getOrderInBlock());
            $nd->setIsBlockHeader($srcDetail->isBlockHeader());
            $nd->setIsBlockTotal($srcDetail->isBlockTotal());
            $nd->setIsBlockFooter($srcDetail->isBlockFooter());

            // Pourcentage M-1 = pct cumulé à la fin de N, = pctMoins1 + pct du mois N
            $pctCumule = (float) $srcDetail->getPourcentageMoins1() + (float) $srcDetail->getPourcentage();
            $nd->setPourcentageMoins1(min($pctCumule, 100.0));
            $nd->setTotalHTMoins1((float) $srcDetail->getTotalHT() + (float) $srcDetail->getTotalHTMoins1());

            $nd->setPourcentage(0);
            $nd->setTotalHT(0);

            $this->em->persist($nd);
        }

        $this->em->flush();

        return $this->json([
            'message'     => 'Avancement suivant créé.',
            'id'          => $next->getId(),
            'numero'      => $next->getNumero(),
            'numeroOrdre' => $next->getNumeroOrdre(),
            'totalCumule' => $next->getTotalCumule(),
        ], 201);
    }
}
