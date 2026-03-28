<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\DevisAvancement;
use App\Entity\DevisAvancementDetail;
use App\Repository\DevisAvancementDetailRepository;
use App\Repository\DevisAvancementRepository;
use App\Repository\DevisDetailRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Règles GMAXX pour la création d'un avancement :
 *
 * 1. Auto-numérotation (numeroOrdre = last+1)
 * 2. Copie TOUTES les lignes du devis (devisDetails, isDeleted=false) avec :
 *    - reference, designation, quantite, unite, prixUnitaire
 *    - totalDevis = detail->getTotal() (le total calculé de la ligne)
 *    - blockNumber, lineType, lineSubType, displayOrder, orderInBlock
 *    - isBlockHeader, isBlockTotal, isBlockFooter
 * 3. Premier avancement : pourcentageMoins1 = 0, totalHTMoins1 = 0
 * 4. Avancements suivants : pourcentageMoins1 = pourcentage du mois précédent,
 *                           totalHTMoins1    = totalHT du mois précédent
 * 5. Dans tous les cas : pourcentage = 0, totalHT = 0 (à saisir ce mois)
 */
class AvancementCreationProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $innerProcessor,
        private EntityManagerInterface $em,
        private DevisAvancementRepository $avancementRepo,
        private DevisDetailRepository $detailRepo,
        private DevisAvancementDetailRepository $avancementDetailRepo,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof DevisAvancement) {
            return $this->innerProcessor->process($data, $operation, $uriVariables, $context);
        }

        $devis = $data->getDevis();
        if (!$devis) {
            return $this->innerProcessor->process($data, $operation, $uriVariables, $context);
        }

        // --- Règle 1 : devis doit être Accepté ---
        if ($devis->getEtat() !== 'EtatDevisAccepte') {
            throw new \RuntimeException('Le devis doit être accepté avant de créer un avancement.');
        }

        // --- Règle 2 : dernier avancement doit être Validé (ou aucun) ---
        $lastAvancement = $this->avancementRepo->findOneBy(
            ['devis' => $devis, 'isDeleted' => false],
            ['numeroOrdre' => 'DESC']
        );

        if ($lastAvancement !== null && $lastAvancement->getEtat() === 'EtatAvancementEnCours') {
            throw new \RuntimeException('Un avancement est déjà en cours. Validez-le avant d\'en créer un nouveau.');
        }

        $numeroOrdre = $lastAvancement ? $lastAvancement->getNumeroOrdre() + 1 : 1;

        $data->setNumeroOrdre($numeroOrdre);
        $data->setDateCreation(new \DateTime());
        $data->setEtat('EtatAvancementEnCours');

        // moisReference
        try { $mr = $data->getMoisReference(); } catch (\Error) { $mr = null; }
        if (!$mr) {
            $data->setMoisReference(new \DateTime());
        }

        // numero
        try { $existingNumero = $data->getNumero(); } catch (\Error) { $existingNumero = ''; }
        if (empty($existingNumero)) {
            $data->setNumero('AVA-' . $devis->getNumero() . '-' . str_pad((string)$numeroOrdre, 2, '0', STR_PAD_LEFT));
        }

        // --- Persist l'avancement ---
        $result = $this->innerProcessor->process($data, $operation, $uriVariables, $context);

        // --- Lignes du devis (non supprimées, triées par displayOrder) ---
        $devisDetails = $this->detailRepo->findBy(
            ['devis' => $devis, 'isDeleted' => false],
            ['displayOrder' => 'ASC']
        );

        // --- Index du mois précédent : dernier avancement VALIDÉ ou FACTURÉ ---
        // (règle GMAXX : M-1 = le dernier avancement dont les données sont figées)
        $previousDetailMap = [];
        $lastValidated = $this->avancementRepo->findOneBy(
            ['devis' => $devis, 'isDeleted' => false, 'etat' => 'EtatAvancementValide'],
            ['numeroOrdre' => 'DESC']
        );
        if (!$lastValidated) {
            $lastValidated = $this->avancementRepo->findOneBy(
                ['devis' => $devis, 'isDeleted' => false, 'etat' => 'EtatAvancementFacture'],
                ['numeroOrdre' => 'DESC']
            );
        }

        if ($lastValidated) {
            $prevDetails = $this->avancementDetailRepo->findBy(['devisAvancement' => $lastValidated]);
            foreach ($prevDetails as $pd) {
                if ($pd->getDevisDetail()) {
                    $previousDetailMap[$pd->getDevisDetail()->getId()] = $pd;
                }
            }
        }

        // --- Créer les lignes d'avancement ---
        foreach ($devisDetails as $devisDetail) {
            $avDetail = new DevisAvancementDetail();
            $avDetail->setDevisAvancement($result);
            $avDetail->setDevisDetail($devisDetail);

            // Copie complète depuis le devisDetail (règle GMAXX copyFromDevisDetail)
            $avDetail->setReference($devisDetail->getReference());
            $avDetail->setDesignation($devisDetail->getDesignation());
            $avDetail->setQuantite($devisDetail->getQuantite());
            $avDetail->setUnite($devisDetail->getUnite());
            $avDetail->setPrixUnitaire($devisDetail->getPrixUnitaire() ? (float)$devisDetail->getPrixUnitaire() : null);

            // totalDevis = total calculé de la ligne (pas quantite*pu)
            $totalDevis = $devisDetail->getTotal();
            if ($totalDevis === null && $devisDetail->getQuantite() && $devisDetail->getPrixUnitaire()) {
                $totalDevis = $devisDetail->getQuantite() * (float)$devisDetail->getPrixUnitaire();
            }
            $avDetail->setTotalDevis($totalDevis);

            // Métadonnées de structure (blocs, ordre)
            $avDetail->setBlockNumber($devisDetail->getBlockNumber());
            $avDetail->setLineType($devisDetail->getLineType() ?: 'RL');
            $avDetail->setLineSubType($devisDetail->getLineSubType());
            $avDetail->setDisplayOrder($devisDetail->getDisplayOrder());
            $avDetail->setOrderInBlock($devisDetail->getOrderInBlock());
            $avDetail->setIsBlockHeader($devisDetail->getIsBlockHeader());
            $avDetail->setIsBlockTotal($devisDetail->getIsBlockTotal());
            $avDetail->setIsBlockFooter($devisDetail->getIsBlockFooter());

            // Mois précédent (règle GMAXX copyFromPreviousMonth)
            if ($numeroOrdre === 1 || !isset($previousDetailMap[$devisDetail->getId()])) {
                $avDetail->setPourcentageMoins1(0);
                $avDetail->setTotalHTMoins1(0);
            } else {
                $prev = $previousDetailMap[$devisDetail->getId()];
                $avDetail->setPourcentageMoins1((float)$prev->getPourcentage());
                $avDetail->setTotalHTMoins1((float)$prev->getTotalHT());
            }

            // Ce mois : à zéro, à saisir
            $avDetail->setPourcentage(0);
            $avDetail->setTotalHT(0);

            $this->em->persist($avDetail);
        }

        $this->em->flush();

        return $result;
    }
}
