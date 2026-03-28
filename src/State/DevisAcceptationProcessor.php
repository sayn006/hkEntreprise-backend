<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Chantier;
use App\Entity\Devis;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Quand un devis passe à 'EtatDevisAccepte' :
 * - Crée automatiquement un Chantier lié (si pas déjà créé)
 */
class DevisAcceptationProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $innerProcessor,
        private EntityManagerInterface $em,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $result = $this->innerProcessor->process($data, $operation, $uriVariables, $context);

        if (!$result instanceof Devis) {
            return $result;
        }

        if ($result->getEtat() === 'EtatDevisAccepte' && $result->getChantier() === null) {
            $chantier = new Chantier();
            $chantier->setCode('CH-' . $result->getNumero());
            $chantier->setNom($result->getTitre());
            $chantier->setSlug('ch-' . strtolower(preg_replace('/[^a-z0-9]+/i', '-', $result->getNumero())));
            $chantier->setSoftDelete(false);

            $this->em->persist($chantier);

            $result->setChantier($chantier);

            $this->em->flush();
        }

        return $result;
    }
}
