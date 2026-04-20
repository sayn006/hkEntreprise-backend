<?php

namespace App\EventListener;

use App\Entity\DevisAvancement;
use App\Entity\FactureSituation;
use App\Service\SituationBuilderService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;

/**
 * Quand un DevisAvancement passe au statut "EtatAvancementValide",
 * génère automatiquement la FactureSituation liée en brouillon
 * et la remplit automatiquement (détails / travaux / retenue / totaux)
 * via SituationBuilderService.
 */
#[AsDoctrineListener(event: Events::postUpdate)]
class AvancementValidationListener
{
    public function __construct(
        private SituationBuilderService $situationBuilder,
    ) {}

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof DevisAvancement) {
            return;
        }

        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();
        $changes = $uow->getEntityChangeSet($entity);

        if (!isset($changes['etat'])) {
            return;
        }

        [$oldEtat, $newEtat] = $changes['etat'];
        if ($newEtat !== 'EtatAvancementValide') {
            return;
        }

        // Crée la situation si elle n'existe pas déjà
        if ($entity->getFactureSituation() === null) {
            $this->createSituation($entity, $em);
        }

        // Auto-remplissage (idempotent côté service)
        $this->situationBuilder->buildFromAvancement($entity);
    }

    private function createSituation(DevisAvancement $avancement, EntityManagerInterface $em): void
    {
        $devis    = $avancement->getDevis();
        $chantier = $devis?->getChantier();

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

        $em->persist($situation);
        $avancement->setFactureSituation($situation);

        // Flush immédiat pour que la FactureSituation ait un id
        $em->flush();
    }
}
