<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\FactureSituation;
use App\Entity\Situation;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Quand une Situation est créée (POST /api/situations) :
 * - Génère automatiquement une FactureSituation liée au même chantier
 */
class SituationGenerationProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $innerProcessor,
        private EntityManagerInterface $em,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $result = $this->innerProcessor->process($data, $operation, $uriVariables, $context);

        if (!$result instanceof Situation) {
            return $result;
        }

        // Crée la FactureSituation liée
        $factureSituation = new FactureSituation();
        $factureSituation->setDateSituation(new \DateTime());
        $factureSituation->setTitre($result->getTitre() ?? 'Situation n°' . $result->getNumero());
        $factureSituation->setNumSituation((string) $result->getNumero());

        // Lier au chantier via la facture si disponible
        if ($result->getFacture()?->getChantier()) {
            $factureSituation->setChantier($result->getFacture()->getChantier());
        }

        // Générer un numéro de facture
        $factureSituation->setNumeroFacture('FS-' . date('Y') . '-' . str_pad((string) $result->getNumero(), 3, '0', STR_PAD_LEFT));

        $this->em->persist($factureSituation);
        $this->em->flush();

        return $result;
    }
}
