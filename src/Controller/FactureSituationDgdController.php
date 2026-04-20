<?php

namespace App\Controller;

use App\Entity\Chantier;
use App\Entity\FactureSituation;
use App\Entity\FactureSituationFacturationTravaux;
use App\Entity\FactureSituationTotal;
use App\Repository\DevisAvancementRepository;
use App\Repository\FactureSituationRepository;
use App\Repository\FactureSituationRetenueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Création du Décompte Général Définitif (DGD) à la clôture d'un chantier.
 *
 * Règle métier BTP :
 *   - Tous les avancements (non supprimés) du chantier doivent être à l'état
 *     EtatAvancementFacture (facturés).
 *   - Le DGD est une FactureSituation spéciale (isDgd=true) qui libère le cumul
 *     des retenues de garantie prélevées sur les situations précédentes.
 *   - Pas de nouvelle retenue : une ligne "Libération retenue de garantie"
 *     avec montant NÉGATIF (= retenues cumulées × -1 = crédit pour l'entreprise).
 *   - Numérotation DGD-YYYY-NNN, titre "Décompte Général Définitif".
 */
#[Route('/api/chantiers')]
#[IsGranted('ROLE_USER')]
class FactureSituationDgdController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private DevisAvancementRepository $avancementRepo,
        private FactureSituationRepository $situationRepo,
        private FactureSituationRetenueRepository $retenueRepo,
    ) {}

    #[Route('/{id}/create-dgd', name: 'api_chantier_create_dgd', methods: ['POST'])]
    public function createDgd(Chantier $chantier): JsonResponse
    {
        // 1. Vérifier qu'un DGD n'existe pas déjà pour ce chantier
        $existing = $this->situationRepo->findOneBy(['chantier' => $chantier, 'isDgd' => true, 'softDelete' => false]);
        if ($existing) {
            return $this->json([
                'error' => 'Un DGD existe déjà pour ce chantier.',
                'id'    => $existing->getId(),
                'numeroFacture' => $existing->getNumeroFacture(),
            ], 409);
        }

        // 2. Récupérer tous les avancements du chantier (via les devis du chantier)
        $allAvancements = [];
        foreach ($chantier->getBonCommandes() as $_) {} // hydrate si besoin
        // devis liés au chantier
        $qb = $this->em->createQueryBuilder();
        $qb->select('a')
            ->from('App\Entity\DevisAvancement', 'a')
            ->join('a.devis', 'd')
            ->where('d.chantier = :c')
            ->andWhere('a.isDeleted = false')
            ->setParameter('c', $chantier);
        $allAvancements = $qb->getQuery()->getResult();

        if (empty($allAvancements)) {
            return $this->json(['error' => 'Aucun avancement trouvé pour ce chantier.'], 400);
        }

        // 3. Vérifier que tous les avancements sont facturés
        $notBilled = [];
        foreach ($allAvancements as $av) {
            if ($av->getEtat() !== 'EtatAvancementFacture') {
                $notBilled[] = $av->getNumero() . ' (' . $av->getEtat() . ')';
            }
        }
        if (!empty($notBilled)) {
            return $this->json([
                'error' => 'Tous les avancements doivent être Facturés pour clôturer avec un DGD.',
                'avancementsNonFactures' => $notBilled,
            ], 400);
        }

        // 4. Cumul des retenues de garantie prélevées (par situation liée aux avancements)
        $totalRetenue = 0.0;
        $tvaPercent = $chantier->getTva() !== null ? (float) $chantier->getTva() : 20.0;
        $previousSituations = [];
        foreach ($allAvancements as $av) {
            $fs = $av->getFactureSituation();
            if (!$fs) continue;
            $previousSituations[] = $fs;
            foreach ($this->retenueRepo->findBy(['situation' => $fs]) as $ret) {
                // les montants retenue sont stockés négatifs (déduction) — on les cumule en valeur absolue
                $totalRetenue += abs((float) $ret->getCumule() ?: (float) $ret->getMontant());
                // le cumule final contient déjà le total à date si présent
                break; // on ne prend qu'une ligne retenue par situation (elle agrège déjà)
            }
        }

        if ($totalRetenue <= 0.0) {
            return $this->json([
                'error' => 'Aucune retenue de garantie cumulée à libérer (montant = 0).',
            ], 400);
        }

        // 5. Numérotation : DGD-YYYY-NNN
        $year = (int) (new \DateTime())->format('Y');
        $prefix = sprintf('DGD-%d-', $year);
        $latest = $this->em->createQuery(
            'SELECT fs.numeroFacture FROM App\Entity\FactureSituation fs
             WHERE fs.isDgd = true AND fs.numeroFacture LIKE :p
             ORDER BY fs.id DESC'
        )->setParameter('p', $prefix . '%')->setMaxResults(1)->getOneOrNullResult();
        $next = 1;
        if ($latest && preg_match('/(\d+)$/', $latest['numeroFacture'], $m)) {
            $next = ((int) $m[1]) + 1;
        }
        $numeroFacture = $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);

        // 6. Créer la FactureSituation DGD
        $dgd = new FactureSituation();
        $dgd->setDateSituation(new \DateTime());
        $dgd->setTitre('Décompte Général Définitif');
        $dgd->setNumeroFacture($numeroFacture);
        $dgd->setNumSituation('DGD');
        $dgd->setChantier($chantier);
        $dgd->setIsDgd(true);
        if ($user = $this->getUser()) {
            $dgd->setCreatedUser($user);
        }
        $this->em->persist($dgd);
        $this->em->flush(); // avoir id

        // 7. Ligne libération de retenue (valeur positive du point de vue entreprise = crédit)
        $ft = new FactureSituationFacturationTravaux();
        $ft->setSituation($dgd);
        $ft->setType('LIBERATION_RG');
        $ft->setDesignation(sprintf(
            'Libération de la retenue de garantie cumulée (%d situation%s)',
            count($previousSituations),
            count($previousSituations) > 1 ? 's' : ''
        ));
        $ft->setMontant(number_format($totalRetenue, 2, '.', ''));
        $ft->setCumule(number_format($totalRetenue, 2, '.', ''));
        $ft->setCumuleAnterieur('0.00');
        $ft->setFacturationFinDuMois(number_format($totalRetenue, 2, '.', ''));
        $this->em->persist($ft);

        // 8. Totaux DGD : HT = totalRetenue, TVA selon chantier, TTC
        $totalHt = $totalRetenue;
        $tvaMontant = round($totalHt * $tvaPercent / 100.0, 2);
        $totalTtc = $totalHt + $tvaMontant;

        $this->persistTotal($dgd, 'TOTAL_HT', 'Total HT (libération RG)', $totalHt);
        $this->persistTotal($dgd, 'TVA', sprintf('TVA %s%%', rtrim(rtrim(number_format($tvaPercent, 2, '.', ''), '0'), '.')), $tvaMontant);
        $this->persistTotal($dgd, 'TOTAL_TTC', 'Total TTC DGD', $totalTtc);

        $dgd->setMontantTotalTTC(number_format($totalTtc, 2, '.', ''));

        $this->em->flush();

        return $this->json([
            'message'         => 'DGD créé avec succès.',
            'id'              => $dgd->getId(),
            'numeroFacture'   => $dgd->getNumeroFacture(),
            'totalRetenue'    => number_format($totalRetenue, 2, '.', ''),
            'montantTotalTTC' => $dgd->getMontantTotalTTC(),
            'isDgd'           => true,
        ], 201);
    }

    private function persistTotal(FactureSituation $dgd, string $divers, string $designation, float $montant): void
    {
        $t = new FactureSituationTotal();
        $t->setSituation($dgd);
        $t->setDivers($divers);
        $t->setDesignation($designation);
        $t->setMontant(number_format($montant, 2, '.', ''));
        $t->setCumule(number_format($montant, 2, '.', ''));
        $t->setCumuleAnterieur('0.00');
        $t->setFacturationFinDuMois(number_format($montant, 2, '.', ''));
        $this->em->persist($t);
    }
}
