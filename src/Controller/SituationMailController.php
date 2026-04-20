<?php

namespace App\Controller;

use App\Entity\FactureSituation;
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
 * Envoi par email d'une situation (FactureSituation) avec PDF en pièce jointe.
 */
#[Route('/api/situations')]
#[IsGranted('ROLE_USER')]
class SituationMailController extends AbstractController
{
    public function __construct(
        private MailerInterface $mailer,
        private PdfService $pdfService,
        private EntrepriseRepository $entrepriseRepo,
        private EntityManagerInterface $em,
        private Environment $twig,
    ) {
    }

    #[Route('/{id}/send-email', name: 'api_situation_send_email', methods: ['POST'])]
    public function sendEmail(FactureSituation $situation, Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent() ?: '[]', true) ?? [];

        $to = $payload['to'] ?? null;
        if (!$to) {
            $client = $situation->getChantier()?->getClient();
            $to = $client?->getEmail();
        }

        if (!$to || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return $this->json([
                'error' => 'Adresse email destinataire invalide ou manquante.',
            ], 400);
        }

        $entreprise = $this->entrepriseRepo->findOneBy([]);
        $fromEmail  = $entreprise?->resolveFromEmail('situation')
            ?? $_ENV['MAILER_FROM']
            ?? 'no-reply@hk-entreprise.fr';
        $fromName   = $entreprise?->getNom() ?? 'HK Entreprise';

        $numero = $situation->getNumeroFacture() ?: (string) $situation->getId();

        $subject = $payload['subject']
            ?? sprintf('Situation %s%s', $numero, $situation->getTitre() ? ' — ' . $situation->getTitre() : '');

        $html = $this->twig->render('emails/situation.html.twig', [
            'situation'    => $situation,
            'entreprise'   => $entreprise,
            'messageIntro' => $payload['message'] ?? null,
        ]);

        $pdf      = $this->pdfService->generateSituationPdf($situation);
        $filename = sprintf('Situation-%s.pdf', $numero);

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

        $situation->setEnvoyeAt(new \DateTime());
        $this->em->flush();

        return $this->json([
            'message'  => 'Situation envoyée avec succès.',
            'to'       => $to,
            'envoyeAt' => $situation->getEnvoyeAt()?->format(\DateTimeInterface::ATOM),
        ]);
    }
}
