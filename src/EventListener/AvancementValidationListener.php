<?php

namespace App\EventListener;

use App\Entity\DevisAvancement;
use App\Entity\FactureSituation;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;

/**
 * Quand un DevisAvancement passe au statut "EtatAvancementValide",
 * génère automatiquement la FactureSituation liée en brouillon,
 * si elle n'existe pas déjà.
 */
#[AsDoctrineListener(event: Events::postUpdate)]
class AvancementValidationListener
{
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

        // Déjà lié à une situation ? rien à faire.
        if ($entity->getFactureSituation() !== null) {
            return;
        }

        $this->createSituation($entity, $em);
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
