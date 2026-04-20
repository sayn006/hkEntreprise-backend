<?php

namespace App\Controller;

use App\Entity\DevisAvancement;
use App\Entity\FactureSituation;
use App\Service\SituationBuilderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Génère une Situation (FactureSituation) en brouillon depuis un avancement validé.
 *
 * Règle métier : un avancement validé peut générer une unique Situation.
 * Si elle existe déjà, on la retourne sans la recréer.
 */
#[Route('/api/devis-avancements')]
#[IsGranted('ROLE_USER')]
class AvancementSituationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private SituationBuilderService $situationBuilder,
    ) {
    }

    #[Route('/{id}/create-situation', name: 'api_avancement_create_situation', methods: ['POST'])]
    public function createSituation(DevisAvancement $avancement): JsonResponse
    {
        if ($avancement->isDeleted()) {
            return $this->json(['error' => 'Avancement supprimé.'], 400);
        }

        if ($avancement->getEtat() !== 'EtatAvancementValide') {
            return $this->json([
                'error' => 'Seul un avancement validé peut générer une situation.',
            ], 400);
        }

        if ($avancement->hasFactureSituation()) {
            $existing = $avancement->getFactureSituation();
            return $this->json([
                'message' => 'Situation déjà existante pour cet avancement.',
                'id'      => $existing?->getId(),
            ]);
        }

        $devis     = $avancement->getDevis();
        $chantier  = $devis?->getChantier();

        $situation = new FactureSituation();
        $situation->setDateSituation(new \DateTime());
        $situation->setTitre(sprintf(
            'Situation %s — %s',
            str_pad((string) $avancement->getNumeroOrdre(), 2, '0', STR_PAD_LEFT),
            $devis?->getTitre() ?? $avancement->getNumero()
        ));
        $situation->setNumSituation(str_pad((string) $avancement->getNumeroOrdre(), 2, '0', STR_PAD_LEFT));
        $situation->setNumeroFacture(sprintf(
            'FS-%s-%s',
            date('Y'),
            str_pad((string) $avancement->getNumeroOrdre(), 3, '0', STR_PAD_LEFT)
        ));
        $situation->setMontantTotalTTC((string) $avancement->getTotalCumule());

        if ($chantier) {
            $situation->setChantier($chantier);
        }

        if ($user = $this->getUser()) {
            // L'entité a un setter createdUser si disponible
            if (method_exists($situation, 'setCreatedUser')) {
                $situation->setCreatedUser($user);
            }
        }

        $this->em->persist($situation);

        // Relier avancement ↔ situation (les lignes détaillées seront saisies
        // dans l'UI situation — le modèle FactureSituationDetail est spécifique
        // à GMAXX et ne mappe pas 1-1 les lignes d'avancement)
        $avancement->setFactureSituation($situation);

        $this->em->flush();

        // Auto-remplissage (détails, travaux, retenue, totaux)
        $this->situationBuilder->buildFromAvancement($avancement);

        return $this->json([
            'message'          => 'Situation créée en brouillon.',
            'id'               => $situation->getId(),
            'numeroFacture'    => $situation->getNumeroFacture(),
            'titre'            => $situation->getTitre(),
        ], 201);
    }
}
