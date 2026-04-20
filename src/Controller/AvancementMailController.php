<?php

namespace App\Controller;

use App\Entity\DevisAvancement;
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
 * Envoi par email d'un avancement (DevisAvancement) avec PDF en pièce jointe.
 */
#[Route('/api/devis-avancements')]
#[IsGranted('ROLE_USER')]
class AvancementMailController extends AbstractController
{
    public function __construct(
        private MailerInterface $mailer,
        private PdfService $pdfService,
        private EntrepriseRepository $entrepriseRepo,
        private EntityManagerInterface $em,
        private Environment $twig,
    ) {
    }

    #[Route('/{id}/send-email', name: 'api_avancement_send_email', methods: ['POST'])]
    public function sendEmail(DevisAvancement $avancement, Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent() ?: '[]', true) ?? [];

        $to = $payload['to'] ?? null;
        if (!$to) {
            $client = $avancement->getDevis()?->getChantier()?->getClient();
            $to = $client?->getEmail();
        }

        if (!$to || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return $this->json([
                'error' => 'Adresse email destinataire invalide ou manquante.',
            ], 400);
        }

        $entreprise = $this->entrepriseRepo->findOneBy([]);
        $fromEmail  = $entreprise?->resolveFromEmail('avancement')
            ?? $_ENV['MAILER_FROM']
            ?? 'no-reply@hk-entreprise.fr';
        $fromName   = $entreprise?->getNom() ?? 'HK Entreprise';

        $subject = $payload['subject']
            ?? sprintf('Avancement %s', $avancement->getNumero());

        $html = $this->twig->render('emails/avancement.html.twig', [
            'avancement'   => $avancement,
            'entreprise'   => $entreprise,
            'messageIntro' => $payload['message'] ?? null,
        ]);

        $pdf      = $this->pdfService->generateAvancementPdf($avancement);
        $filename = sprintf('Avancement-%s.pdf', $avancement->getNumero());

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
            'message' => 'Avancement envoyé avec succès.',
            'to'      => $to,
        ]);
    }
}
