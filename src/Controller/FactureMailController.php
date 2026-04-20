<?php

namespace App\Controller;

use App\Entity\Facture;
use App\Repository\EntrepriseRepository;
use App\Service\PdfService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Twig\Environment;

/**
 * Envoi par email d'une facture avec PDF en pièce jointe.
 */
#[Route('/api/factures')]
#[IsGranted('ROLE_USER')]
class FactureMailController extends AbstractController
{
    public function __construct(
        private MailerInterface $mailer,
        private PdfService $pdfService,
        private EntrepriseRepository $entrepriseRepo,
        private EntityManagerInterface $em,
        private Environment $twig,
    ) {
    }

    #[Route('/{id}/send-email', name: 'api_facture_send_email', methods: ['POST'])]
    public function sendEmail(Facture $facture, Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent() ?: '[]', true) ?? [];

        $to = $payload['to'] ?? null;
        if (!$to) {
            $to = $facture->getClient()?->getEmail();
        }

        if (!$to || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return $this->json([
                'error' => 'Adresse email destinataire invalide ou manquante.',
            ], 400);
        }

        $entreprise = $this->entrepriseRepo->findOneBy([]);
        $fromEmail  = $entreprise?->resolveFromEmail('facture')
            ?? $_ENV['MAILER_FROM']
            ?? 'no-reply@hk-entreprise.fr';
        $fromName   = $entreprise?->getNom() ?? 'HK Entreprise';

        $subject = $payload['subject']
            ?? sprintf('Facture %s', $facture->getNumero());

        $html = $this->twig->render('emails/facture.html.twig', [
            'facture'      => $facture,
            'entreprise'   => $entreprise,
            'messageIntro' => $payload['message'] ?? null,
        ]);

        $pdf      = $this->pdfService->generateFacturePdf($facture);
        $filename = sprintf('Facture-%s.pdf', $facture->getNumero());

        $email = (new Email())
            ->from(new Address($fromEmail, $fromName))
            ->to($to)
            ->subject($subject)
            ->html($html)
            ->attach($pdf, $filename, 'application/pdf');

        if (!empty($payload['cc']) && is_array($payload['cc'])) {
            foreach ($payload['cc'] as $cc) {
                if (filter_var($cc, FILTER_VALIDATE_EMAIL)) {
                    $email->addCc($cc);
                }
            }
        }

        $this->mailer->send($email);

        return $this->json([
            'message' => 'Facture envoyée avec succès.',
            'to'      => $to,
        ]);
    }
}
