<?php

namespace App\Controller;

use App\Entity\Devis;
use App\Entity\Facture;
use App\Entity\FactureLigne;
use App\Repository\FactureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Crée une Facture à partir d'un Devis.
 *
 * - Copie le chantier et le client du devis (via devis.chantier.client)
 * - Duplique les DevisDetail "facturables" (quantite/prixUnitaire/tva présents,
 *   hors lignes de structure BH/BT/BF) en FactureLigne.
 * - Retourne la Facture créée (groupes facture:read).
 */
#[Route('/api/devis')]
#[IsGranted('ROLE_USER')]
class DevisFactureController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private FactureRepository $factureRepo,
        private SerializerInterface $serializer,
    ) {
    }

    #[Route('/{id}/create-facture', name: 'api_devis_create_facture', methods: ['POST'])]
    public function createFacture(Devis $devis): JsonResponse
    {
        if ($devis->getIsDeleted()) {
            return $this->json(['error' => 'Devis supprimé.'], 400);
        }

        if ($devis->getEtat() !== 'EtatDevisAccepte') {
            return $this->json([
                'error' => 'Seul un devis accepté peut être converti en facture.',
            ], 400);
        }

        if ($devis->getType() === 'avancement') {
            return $this->json([
                'error' => 'Les devis en mode avancement génèrent des situations, pas des factures directes.',
            ], 400);
        }

        $chantier = $devis->getChantier();
        $client = $chantier?->getClient();
        if (!$chantier || !$client) {
            return $this->json([
                'error' => 'Le devis doit avoir un chantier avec un client pour créer une facture.',
            ], 400);
        }

        $facture = new Facture();
        $facture->setNumero($this->factureRepo->generateNumero());
        $facture->setDateFacture(new \DateTime());
        $facture->setChantier($chantier);
        $facture->setClient($client);
        $facture->setStatut('Brouillon');
        $facture->setDescription(sprintf('Facture générée depuis le devis %s', $devis->getNumero()));

        // Copier les lignes du devis (on skip les lignes structurelles)
        foreach ($devis->getDevisDetails() as $detail) {
            if ($detail->getIsDeleted()) {
                continue;
            }
            // Skip les entêtes/totaux/footers de blocs
            $lt = $detail->getLineType();
            if (in_array($lt, ['BH', 'BT', 'BF'], true)) {
                continue;
            }
            // Skip les lignes sans données chiffrées
            if ($detail->getQuantite() === null && $detail->getPrixUnitaire() === null) {
                continue;
            }

            $ligne = new FactureLigne();
            $ligne->setDesignation($detail->getDesignation() ?? '');
            $ligne->setQuantite($detail->getQuantite() ?? 1);
            // prixUnitaire sur devis = prix de vente (pvUnit peut être plus précis)
            $pu = $detail->getPvUnit() ?? $detail->getPrixUnitaire();
            $ligne->setPrixUnitaireHt($pu !== null ? number_format((float) $pu, 2, '.', '') : '0.00');
            $tva = $detail->getTva();
            $ligne->setTauxTva($tva !== null ? number_format((float) $tva, 2, '.', '') : '20.00');

            $facture->addLigne($ligne);
            $this->em->persist($ligne);
        }

        $this->em->persist($facture);
        $this->em->flush();

        $json = $this->serializer->serialize($facture, 'jsonld', ['groups' => ['facture:read']]);

        return new JsonResponse($json, 201, [], true);
    }
}
