<?php

namespace App\Service;

use App\Entity\DevisAvancement;
use App\Entity\FactureSituation;
use App\Entity\FactureSituationDetail;
use App\Entity\FactureSituationFacturationTravaux;
use App\Entity\FactureSituationRetenue;
use App\Entity\FactureSituationTotal;
use App\Repository\DevisAvancementRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Auto-remplit une FactureSituation à partir d'un DevisAvancement validé.
 *
 * Logique BTP (inspirée de gmaxx) :
 *   - Montant du mois (delta M) = totalHT d'une ligne d'avancement (déjà calculé par AvancementCreationProcessor :
 *     pct-saisi% du totalDevis, soit uniquement ce qui a été ajouté ce mois — indépendant de M-1).
 *   - Cumulé = pourcentageMoins1 + pct (plafonné à 100) × totalDevis
 *   - Cumulé antérieur = totalHTMoins1
 *   - RG = -% × montant_mois
 *   - TVA = % × (Total HT après retenues)
 *
 * Idempotent : si la situation possède déjà des totaux, on ne refait rien.
 */
class SituationBuilderService
{
    public function __construct(
        private EntityManagerInterface $em,
        private DevisAvancementRepository $avancementRepo,
        private PrixRevisionCalculator $revisionCalculator,
    ) {}

    public function buildFromAvancement(DevisAvancement $avancement): ?FactureSituation
    {
        $situation = $avancement->getFactureSituation();
        if ($situation === null) {
            return null;
        }

        // Idempotent : si déjà rempli, on sort
        if (!$situation->getTotaux()->isEmpty() || !$situation->getFacturationTravaux()->isEmpty()) {
            return $situation;
        }

        $devis    = $avancement->getDevis();
        $chantier = $devis?->getChantier();

        // === 1. Constantes utiles ===
        $tvaPercent = $chantier && $chantier->getTva() !== null
            ? (float) $chantier->getTva()
            : 20.0;
        $retenuePercent = $chantier && $chantier->getPourcentageRetenue() !== null
            ? (float) $chantier->getPourcentageRetenue()
            : 0.0;

        // === 2. Lignes détaillées ===
        $details = $avancement->getDevisAvancementDetails();

        $sumMontantMois       = 0.0;
        $sumCumule            = 0.0;
        $sumCumuleAnterieur   = 0.0;

        foreach ($details as $avDetail) {
            // Ne pas créer de ligne détail pour les en-têtes / totaux de blocs / lignes vides
            if ($avDetail->isBlockHeader() || $avDetail->isBlockTotal() || $avDetail->isBlockFooter()) {
                continue;
            }
            // Skip les lignes sans montant (header implicites, séparateurs)
            if ($avDetail->getTotalDevis() === null) {
                continue;
            }

            $pct         = (float) $avDetail->getPourcentage();
            $pctMoins1   = (float) $avDetail->getPourcentageMoins1();
            $totalDevis  = (float) $avDetail->getTotalDevis();

            // Cumul total = min((pctM1 + pct), 100) × totalDevis
            $pctCumule   = min($pctMoins1 + $pct, 100.0);
            $cumule      = $totalDevis * $pctCumule / 100.0;
            $cumulAnt    = (float) $avDetail->getTotalHTMoins1();
            // Montant du mois = différence (cumule actuel - cumule M-1)
            $montantMois = $cumule - $cumulAnt;

            $fsDetail = new FactureSituationDetail();
            $fsDetail->setSituation($situation);
            $fsDetail->setDesignation($avDetail->getDesignation() ?? ($avDetail->getReference() ?? 'Ligne'));
            $fsDetail->setMontant(number_format($montantMois, 2, '.', ''));
            $fsDetail->setCumule(number_format($cumule, 2, '.', ''));
            $fsDetail->setCumuleAnterieur(number_format($cumulAnt, 2, '.', ''));
            $fsDetail->setType('DETAIL');
            $fsDetail->setGroupe(FactureSituationDetail::GROUPE_FACTURATION_TRAVAUX);

            $this->em->persist($fsDetail);

            $sumMontantMois     += $montantMois;
            $sumCumule          += $cumule;
            $sumCumuleAnterieur += $cumulAnt;
        }

        // === 3. Ligne agrégée "Facturation des travaux du mois" ===
        $facturationTravaux = new FactureSituationFacturationTravaux();
        $facturationTravaux->setSituation($situation);
        $facturationTravaux->setType('TRAVAUX');
        $facturationTravaux->setDesignation('Facturation des travaux du mois');
        $facturationTravaux->setMontant(number_format($sumMontantMois, 2, '.', ''));
        $facturationTravaux->setCumule(number_format($sumCumule, 2, '.', ''));
        $facturationTravaux->setCumuleAnterieur(number_format($sumCumuleAnterieur, 2, '.', ''));
        $facturationTravaux->setFacturationFinDuMois(number_format($sumMontantMois, 2, '.', ''));
        $this->em->persist($facturationTravaux);

        // === 4. Retenue de garantie ===
        $montantRetenueMois = 0.0;
        $cumulRetenue       = 0.0;
        $cumulRetenueAnt    = 0.0;

        if ($retenuePercent > 0) {
            $montantRetenueMois = -1 * $sumMontantMois * $retenuePercent / 100.0;
            $cumulRetenue       = -1 * $sumCumule * $retenuePercent / 100.0;
            $cumulRetenueAnt    = -1 * $sumCumuleAnterieur * $retenuePercent / 100.0;

            $retenue = new FactureSituationRetenue();
            $retenue->setSituation($situation);
            $retenue->setDesignation(sprintf('Retenue de garantie (%s%%)', rtrim(rtrim(number_format($retenuePercent, 2, '.', ''), '0'), '.')));
            $retenue->setMontant(number_format($montantRetenueMois, 2, '.', ''));
            $retenue->setCumule(number_format($cumulRetenue, 2, '.', ''));
            $retenue->setCumuleAnterieur(number_format($cumulRetenueAnt, 2, '.', ''));
            $retenue->setFacturationFinDuMois(number_format($montantRetenueMois, 2, '.', ''));
            $this->em->persist($retenue);
        }

        // === 4bis. Révision de prix BTP (si chantier révisable) ===
        $montantRevisionMois = 0.0;
        if ($chantier && $chantier->isPrixRevisable()) {
            $moisSituation = $situation->getDateSituation() ?? new \DateTime();
            $rev = $this->revisionCalculator->computeRevision($chantier, $moisSituation, $sumMontantMois);
            if ($rev['applicable'] && abs($rev['montant']) > 0.005) {
                $montantRevisionMois = (float) $rev['montant'];
                $coef = $rev['coefficient'];
                $revLine = new FactureSituationFacturationTravaux();
                $revLine->setSituation($situation);
                $revLine->setType('REVISION');
                $revLine->setDesignation(sprintf(
                    'Révision de prix (%s, K=%.4f)',
                    $chantier->getIndiceType() ?? '—',
                    $coef ?? 0.0
                ));
                $revLine->setMontant(number_format($montantRevisionMois, 2, '.', ''));
                $revLine->setCumule(number_format($montantRevisionMois, 2, '.', ''));
                $revLine->setCumuleAnterieur('0.00');
                $revLine->setFacturationFinDuMois(number_format($montantRevisionMois, 2, '.', ''));
                $this->em->persist($revLine);
            }
        }

        // === 5. Totaux ===
        $totalHtMois        = $sumMontantMois + $montantRetenueMois + $montantRevisionMois;
        $totalHtCumule      = $sumCumule + $cumulRetenue + $montantRevisionMois;
        $totalHtCumuleAnt   = $sumCumuleAnterieur + $cumulRetenueAnt;

        $tvaMois        = $totalHtMois * $tvaPercent / 100.0;
        $tvaCumule      = $totalHtCumule * $tvaPercent / 100.0;
        $tvaCumuleAnt   = $totalHtCumuleAnt * $tvaPercent / 100.0;

        $ttcMois        = $totalHtMois + $tvaMois;
        $ttcCumule      = $totalHtCumule + $tvaCumule;
        $ttcCumuleAnt   = $totalHtCumuleAnt + $tvaCumuleAnt;

        $this->persistTotal($situation, 'TOTAL_HT', 'Total HT', $totalHtMois, $totalHtCumule, $totalHtCumuleAnt);
        $this->persistTotal($situation, 'TVA', sprintf('TVA %s%%', rtrim(rtrim(number_format($tvaPercent, 2, '.', ''), '0'), '.')), $tvaMois, $tvaCumule, $tvaCumuleAnt);
        $this->persistTotal($situation, 'TOTAL_TTC', 'Total TTC', $ttcMois, $ttcCumule, $ttcCumuleAnt);

        // === 6. Montant TTC récapitulatif sur la situation ===
        $situation->setMontantTotalTTC(number_format($ttcMois, 2, '.', ''));

        $this->em->flush();

        return $situation;
    }

    private function persistTotal(FactureSituation $situation, string $divers, string $designation, float $montant, float $cumule, float $cumuleAnterieur): void
    {
        $t = new FactureSituationTotal();
        $t->setSituation($situation);
        $t->setDivers($divers);
        $t->setDesignation($designation);
        $t->setMontant(number_format($montant, 2, '.', ''));
        $t->setCumule(number_format($cumule, 2, '.', ''));
        $t->setCumuleAnterieur(number_format($cumuleAnterieur, 2, '.', ''));
        $t->setFacturationFinDuMois(number_format($montant, 2, '.', ''));
        $this->em->persist($t);
    }
}
