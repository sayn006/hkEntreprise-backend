<?php

namespace App\Service;

use App\Entity\BonCommande;
use App\Entity\Devis;
use App\Entity\DevisAvancement;
use App\Entity\Entreprise;
use App\Entity\Facture;
use App\Entity\FactureSituation;
use App\Repository\BonCommandeArticleRepository;
use App\Repository\DevisAvancementDetailRepository;
use App\Repository\DevisDetailRepository;
use App\Repository\EntrepriseRepository;
use App\Repository\FactureSituationDetailRepository;
use App\Repository\FactureSituationFacturationTravauxRepository;
use App\Repository\FactureSituationPaiementRepository;
use App\Repository\FactureSituationRetenueRepository;
use App\Repository\FactureSituationSousTraitantRepository;
use App\Repository\FactureSituationTotalRepository;
use App\Repository\FactureSituationTropPercuRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

class PdfService
{
    public function __construct(
        private Environment $twig,
        private EntrepriseRepository $entrepriseRepo,
        private DevisDetailRepository $detailRepo,
        private DevisAvancementDetailRepository $avancementDetailRepo,
        private FactureSituationDetailRepository $situationDetailRepo,
        private FactureSituationFacturationTravauxRepository $facturationTravauxRepo,
        private FactureSituationRetenueRepository $retenueRepo,
        private FactureSituationTotalRepository $totalRepo,
        private FactureSituationSousTraitantRepository $sousTraitantRepo,
        private FactureSituationPaiementRepository $paiementRepo,
        private FactureSituationTropPercuRepository $tropPercuRepo,
        private BonCommandeArticleRepository $bonArticleRepo,
    ) {}

    private function buildDompdf(): Dompdf
    {
        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $options->setIsRemoteEnabled(true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultPaperSize', 'A4');
        $options->set('defaultPaperOrientation', 'portrait');
        $options->set('dpi', 150);

        return new Dompdf($options);
    }

    private function getEntreprise(): Entreprise
    {
        $entreprise = $this->entrepriseRepo->findOneBy([]);
        if (!$entreprise) {
            // Entreprise par défaut si aucune en base
            $entreprise = new Entreprise();
            $entreprise->setNom('HK Entreprise');
        }
        return $entreprise;
    }

    private function renderPdf(string $html): string
    {
        $dompdf = $this->buildDompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    public function generateDevisPdf(Devis $devis): string
    {
        $details = $this->detailRepo->findBy(
            ['devis' => $devis, 'isDeleted' => false],
            ['displayOrder' => 'ASC']
        );

        $html = $this->twig->render('pdf/devis.html.twig', [
            'devis'      => $devis,
            'details'    => $details,
            'entreprise' => $this->getEntreprise(),
        ]);

        return $this->renderPdf($html);
    }

    public function generateFacturePdf(Facture $facture): string
    {
        $html = $this->twig->render('pdf/facture.html.twig', [
            'facture'    => $facture,
            'entreprise' => $this->getEntreprise(),
        ]);

        return $this->renderPdf($html);
    }

    public function generateSituationPdf(FactureSituation $situation): string
    {
        $html = $this->twig->render('pdf/situation.html.twig', [
            'situation'           => $situation,
            'details'             => $this->situationDetailRepo->findBy(['situation' => $situation]),
            'facturationTravaux'  => $this->facturationTravauxRepo->findBy(['situation' => $situation]),
            'retenues'            => $this->retenueRepo->findBy(['situation' => $situation]),
            'totaux'              => $this->totalRepo->findBy(['situation' => $situation]),
            'sousTraitants'       => $this->sousTraitantRepo->findBy(['situation' => $situation]),
            'paiements'           => $this->paiementRepo->findBy(['situation' => $situation]),
            'tropPercus'          => $this->tropPercuRepo->findBy(['factureSituation' => $situation]),
            'entreprise'          => $this->getEntreprise(),
        ]);

        return $this->renderPdf($html);
    }

    public function generateAvancementPdf(DevisAvancement $avancement): string
    {
        $details = $this->avancementDetailRepo->findBy(
            ['devisAvancement' => $avancement, 'isDeleted' => false],
            ['displayOrder' => 'ASC']
        );

        $html = $this->twig->render('pdf/avancement.html.twig', [
            'avancement' => $avancement,
            'details'    => $details,
            'entreprise' => $this->getEntreprise(),
        ]);

        return $this->renderPdf($html);
    }

    public function generateBonCommandePdf(BonCommande $bon): string
    {
        $articles = $this->bonArticleRepo->findBy(['bonCommande' => $bon]);

        $html = $this->twig->render('pdf/bon_commande.html.twig', [
            'bon'        => $bon,
            'articles'   => $articles,
            'entreprise' => $this->getEntreprise(),
        ]);

        return $this->renderPdf($html);
    }
}
