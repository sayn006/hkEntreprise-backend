<?php

namespace App\Service;

use App\Entity\Chantier;
use App\Repository\IndiceBtpRepository;

/**
 * Calcule la révision de prix BTP sur une situation.
 *
 * Formule standard des marchés publics :
 *     K = 0.15 + 0.85 × (I_actuel / I_base)
 *
 * Où :
 *   - K         = coefficient appliqué au montant HT du mois
 *   - I_base    = indice au mois de référence du marché (signature)
 *   - I_actuel  = indice au mois de la situation
 *   - 0.15      = part fixe (15% non revisable)
 *   - 0.85      = part revisable (85%)
 *
 * Le montant de révision à AJOUTER à la situation = montantMois × (K - 1).
 *
 * Retourne null si le chantier n'est pas révisable ou s'il manque un indice.
 */
class PrixRevisionCalculator
{
    public function __construct(
        private IndiceBtpRepository $indiceRepo,
    ) {}

    /**
     * Calcule le coefficient K pour un mois donné, sur un chantier révisable.
     * Retourne null si non applicable (chantier non révisable, indices manquants, etc.).
     */
    public function computeCoefficient(Chantier $chantier, \DateTimeInterface $moisSituation): ?float
    {
        if (!$chantier->isPrixRevisable()) return null;
        $type = $chantier->getIndiceType();
        $base = $chantier->getIndiceBaseMois();
        if (!$type || !$base) return null;

        $iBase = $this->indiceRepo->findForTypeAndMonth($type, $base);
        if (!$iBase || (float) $iBase->getValeur() <= 0) return null;

        $iActuel = $this->indiceRepo->findForTypeAndMonth($type, $moisSituation);
        if (!$iActuel || (float) $iActuel->getValeur() <= 0) return null;

        $ratio = (float) $iActuel->getValeur() / (float) $iBase->getValeur();
        $k = 0.15 + 0.85 * $ratio;

        return round($k, 6);
    }

    /**
     * Retourne le montant de révision à ajouter (peut être négatif si les indices ont baissé)
     * ou 0.0 si non applicable. Utilise computeCoefficient() pour obtenir K.
     *
     * @return array{coefficient: float|null, montant: float, applicable: bool,
     *               indiceBase: float|null, indiceActuel: float|null}
     */
    public function computeRevision(Chantier $chantier, \DateTimeInterface $moisSituation, float $montantHtMois): array
    {
        $k = $this->computeCoefficient($chantier, $moisSituation);
        if ($k === null) {
            return [
                'coefficient'  => null,
                'montant'      => 0.0,
                'applicable'   => false,
                'indiceBase'   => null,
                'indiceActuel' => null,
            ];
        }

        $type = $chantier->getIndiceType();
        $iBase = $this->indiceRepo->findForTypeAndMonth($type, $chantier->getIndiceBaseMois());
        $iActuel = $this->indiceRepo->findForTypeAndMonth($type, $moisSituation);

        // Montant de révision = montantMois × (K - 1)
        $montantRevision = round($montantHtMois * ($k - 1.0), 2);

        return [
            'coefficient'  => $k,
            'montant'      => $montantRevision,
            'applicable'   => true,
            'indiceBase'   => $iBase ? (float) $iBase->getValeur() : null,
            'indiceActuel' => $iActuel ? (float) $iActuel->getValeur() : null,
        ];
    }
}
