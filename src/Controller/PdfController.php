<?php

namespace App\Controller;

use App\Entity\BonCommande;
use App\Entity\Devis;
use App\Entity\DevisAvancement;
use App\Entity\Facture;
use App\Entity\FactureSituation;
use App\Service\PdfService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/pdf')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class PdfController extends AbstractController
{
    public function __construct(private PdfService $pdfService) {}

    #[Route('/devis/{id}', name: 'api_pdf_devis', methods: ['GET'])]
    public function devisPdf(Devis $devis): Response
    {
        $pdf = $this->pdfService->generateDevisPdf($devis);

        return new Response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => sprintf('inline; filename="Devis-%s.pdf"', $devis->getNumero()),
        ]);
    }

    #[Route('/facture/{id}', name: 'api_pdf_facture', methods: ['GET'])]
    public function facturePdf(Facture $facture): Response
    {
        $pdf = $this->pdfService->generateFacturePdf($facture);

        return new Response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => sprintf('inline; filename="Facture-%s.pdf"', $facture->getNumero()),
        ]);
    }

    #[Route('/situation/{id}', name: 'api_pdf_situation', methods: ['GET'])]
    public function situationPdf(FactureSituation $situation): Response
    {
        $pdf = $this->pdfService->generateSituationPdf($situation);
        $numero = $situation->getNumeroFacture() ?: (string) $situation->getId();

        return new Response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => sprintf('inline; filename="Situation-%s.pdf"', $numero),
        ]);
    }

    #[Route('/avancement/{id}', name: 'api_pdf_avancement', methods: ['GET'])]
    public function avancementPdf(DevisAvancement $avancement): Response
    {
        $pdf = $this->pdfService->generateAvancementPdf($avancement);

        return new Response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => sprintf('inline; filename="Avancement-%s.pdf"', $avancement->getNumero()),
        ]);
    }

    #[Route('/bon-commande/{id}', name: 'api_pdf_bon_commande', methods: ['GET'])]
    public function bonCommandePdf(BonCommande $bon): Response
    {
        $pdf = $this->pdfService->generateBonCommandePdf($bon);

        return new Response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => sprintf('inline; filename="BonCommande-%s.pdf"', $bon->getNumCommande()),
        ]);
    }
}
