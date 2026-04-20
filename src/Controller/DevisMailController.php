<?php

namespace App\Controller;

use App\Entity\Devis;
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
 * Envoi par email du devis (PDF en pièce jointe).
 *
 * Expéditeur par défaut : MAILER_FROM env var, sinon entreprise.email,
 * sinon no-reply@hk-entreprise.fr.
 */
#[Route('/api/devis')]
#[IsGranted('ROLE_USER')]
class DevisMailController extends AbstractController
{
    public function __construct(
        private MailerInterface $mailer,
        private PdfService $pdfService,
        private EntrepriseRepository $entrepriseRepo,
        private EntityManagerInterface $em,
        private Environment $twig,
    ) {
    }

    #[Route('/{id}/send-email', name: 'api_devis_send_email', methods: ['POST'])]
    public function sendEmail(Devis $devis, Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent() ?: '[]', true) ?? [];

        $to = $payload['to'] ?? null;
        if (!$to) {
            // Tente l'email du client lié au chantier
            $client = $devis->getChantier()?->getClient();
            $to = $client?->getEmail();
        }

        if (!$to || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return $this->json([
                'error' => 'Adresse email destinataire invalide ou manquante.',
            ], 400);
        }

        $entreprise = $this->entrepriseRepo->findOneBy([]);
        $fromEmail = $_ENV['MAILER_FROM']
            ?? $entreprise?->getEmail()
            ?? 'no-reply@hk-entreprise.fr';
        $fromName = $entreprise?->getNom() ?? 'HK Entreprise';

        $subject = $payload['subject']
            ?? sprintf('Devis %s — %s', $devis->getNumero(), $devis->getTitre());

        $html = $this->twig->render('emails/devis.html.twig', [
            'devis'        => $devis,
            'entreprise'   => $entreprise,
            'messageIntro' => $payload['message'] ?? null,
        ]);

        $pdf = $this->pdfService->generateDevisPdf($devis);
        $filename = sprintf('Devis-%s.pdf', $devis->getNumero());

        $email = (new Email())
            ->from(new Address($fromEmail, $fromName))
            ->to($to)
            ->subject($subject)
            ->html($html)
            ->attach($pdf, $filename, 'application/pdf');

        // Destinataires additionnels (cc)
        if (!empty($payload['cc']) && is_array($payload['cc'])) {
            foreach ($payload['cc'] as $cc) {
                if (filter_var($cc, FILTER_VALIDATE_EMAIL)) {
                    $email->addCc($cc);
                }
            }
        }

        $this->mailer->send($email);

        // Audit : tracer la date d'envoi
        $devis->setEnvoyeAt(new \DateTime());
        if ($devis->getEtat() === 'EtatDevisBrouillon') {
            $devis->setEtat('EtatDevisEnvoye');
        }
        $this->em->flush();

        return $this->json([
            'message'  => 'Devis envoyé avec succès.',
            'to'       => $to,
            'envoyeAt' => $devis->getEnvoyeAt()?->format(\DateTimeInterface::ATOM),
        ]);
    }
}
