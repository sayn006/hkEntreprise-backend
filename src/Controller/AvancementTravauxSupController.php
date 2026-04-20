<?php

namespace App\Controller;

use App\Entity\DevisAvancement;
use App\Entity\DevisAvancementDetail;
use App\Repository\DevisAvancementDetailRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Ajoute une ligne "Travaux supplémentaires" sur un avancement en cours.
 *
 * Travaux supplémentaires = demande du MOA pendant l'exécution, non prévue
 * au marché initial. Ponctuelle, reste uniquement sur cet avancement.
 *
 * Payload attendu :
 *   { designation, quantite, prixUnitaire, unite?, pourcentage? }
 */
#[Route('/api/devis-avancements')]
#[IsGranted('ROLE_USER')]
class AvancementTravauxSupController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private DevisAvancementDetailRepository $detailRepo,
    ) {}

    #[Route('/{id}/add-line', name: 'api_avancement_add_line', methods: ['POST'])]
    public function addLine(DevisAvancement $avancement, Request $request): JsonResponse
    {
        if ($avancement->isDeleted()) {
            return $this->json(['error' => 'Avancement supprimé.'], 400);
        }
        if ($avancement->getEtat() !== 'EtatAvancementEnCours') {
            return $this->json([
                'error' => 'L\'avancement doit être "En cours" pour ajouter une ligne travaux supplémentaires.',
            ], 400);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        $designation = trim((string) ($data['designation'] ?? ''));
        if ($designation === '') {
            return $this->json(['error' => 'La désignation est obligatoire.'], 400);
        }

        $quantite     = isset($data['quantite']) ? (int) $data['quantite'] : 1;
        $prixUnitaire = isset($data['prixUnitaire']) ? (float) $data['prixUnitaire'] : 0.0;
        $unite        = isset($data['unite']) ? (string) $data['unite'] : 'U';
        $pourcentage  = isset($data['pourcentage']) ? (float) $data['pourcentage'] : 100.0; // TS livrée = 100% par défaut

        if ($quantite < 1) $quantite = 1;
        if ($prixUnitaire < 0) $prixUnitaire = 0.0;
        if ($pourcentage < 0) $pourcentage = 0.0;
        if ($pourcentage > 100) $pourcentage = 100.0;

        $totalDevis = round($quantite * $prixUnitaire, 2);
        $totalHT    = round($totalDevis * $pourcentage / 100.0, 2);

        // Placement en fin : displayOrder = max existant + 10
        $maxOrder = 0;
        foreach ($this->detailRepo->findBy(['devisAvancement' => $avancement]) as $d) {
            $maxOrder = max($maxOrder, $d->getDisplayOrder());
        }

        $line = new DevisAvancementDetail();
        $line->setDevisAvancement($avancement);
        $line->setIsTravauxSupplementaires(true);
        $line->setReference('TS');
        $line->setDesignation($designation);
        $line->setQuantite($quantite);
        $line->setUnite($unite);
        $line->setPrixUnitaire($prixUnitaire);
        $line->setTotalDevis($totalDevis);
        $line->setPourcentageMoins1(0);
        $line->setTotalHTMoins1(0);
        $line->setPourcentage($pourcentage);
        $line->setTotalHT($totalHT);
        $line->setLineType('RL');
        $line->setDisplayOrder($maxOrder + 10);

        $this->em->persist($line);

        // Recalcul totaux de l'avancement
        $this->em->flush(); // pour que findBy récupère la nouvelle ligne

        $allLines = $this->detailRepo->findBy(['devisAvancement' => $avancement, 'isDeleted' => false]);
        $newTotalHT    = 0.0;
        $newTotalCumule = 0.0;
        $totalDevisGlobal = 0.0;
        foreach ($allLines as $l) {
            if ($l->isBlockHeader() || $l->isBlockTotal() || $l->isBlockFooter()) continue;
            $tl = (float) $l->getTotalDevis();
            $pctMoins1 = (float) $l->getPourcentageMoins1();
            $pct = (float) $l->getPourcentage();
            $newTotalHT += ($tl * $pct) / 100.0;
            $newTotalCumule += ($tl * min($pctMoins1 + $pct, 100.0)) / 100.0;
            $totalDevisGlobal += $tl;
        }
        $pctGlobal = $totalDevisGlobal > 0 ? ($newTotalCumule / $totalDevisGlobal) * 100.0 : 0.0;

        $avancement->setTotalHT($newTotalHT);
        $avancement->setTotalCumule($newTotalCumule);
        $avancement->setPourcentageGlobal($pctGlobal);

        $this->em->flush();

        return $this->json([
            'message'    => 'Ligne travaux supplémentaires ajoutée.',
            'id'         => $line->getId(),
            'designation'=> $line->getDesignation(),
            'totalHT'    => $line->getTotalHT(),
            'totalDevis' => $line->getTotalDevis(),
            'avancement' => [
                'totalHT'           => $avancement->getTotalHT(),
                'totalCumule'       => $avancement->getTotalCumule(),
                'pourcentageGlobal' => $avancement->getPourcentageGlobal(),
            ],
        ], 201);
    }
}
