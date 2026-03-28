<?php

namespace App\Service;

use App\Entity\Devis;
use App\Entity\Entreprise;
use App\Entity\Facture;
use App\Repository\DevisDetailRepository;
use App\Repository\EntrepriseRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

class PdfService
{
    public function __construct(
        private Environment $twig,
        private EntrepriseRepository $entrepriseRepo,
        private DevisDetailRepository $detailRepo,
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

        $dompdf = $this->buildDompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    public function generateFacturePdf(Facture $facture): string
    {
        $html = $this->twig->render('pdf/facture.html.twig', [
            'facture'    => $facture,
            'entreprise' => $this->getEntreprise(),
        ]);

        $dompdf = $this->buildDompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
