<?php

namespace App\Controller;

use App\Entity\BonCommande;
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
 * Envoi par email d'un bon de commande au fournisseur avec PDF en pièce jointe.
 */
#[Route('/api/bon-commandes')]
#[IsGranted('ROLE_USER')]
class BonCommandeMailController extends AbstractController
{
    public function __construct(
        private MailerInterface $mailer,
        private PdfService $pdfService,
        private EntrepriseRepository $entrepriseRepo,
        private EntityManagerInterface $em,
        private Environment $twig,
    ) {
    }

    #[Route('/{id}/send-email', name: 'api_bon_commande_send_email', methods: ['POST'])]
    public function sendEmail(BonCommande $bon, Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent() ?: '[]', true) ?? [];

        $to = $payload['to'] ?? null;
        if (!$to) {
            $to = $bon->getFournisseurContact()?->getEmail()
                ?? $bon->getFournisseur()?->getEmail();
        }

        if (!$to || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return $this->json([
                'error' => 'Adresse email destinataire invalide ou manquante.',
            ], 400);
        }

        $entreprise = $this->entrepriseRepo->findOneBy([]);
        $fromEmail  = $entreprise?->resolveFromEmail('bon_commande')
            ?? $_ENV['MAILER_FROM']
            ?? 'no-reply@hk-entreprise.fr';
        $fromName   = $entreprise?->getNom() ?? 'HK Entreprise';

        $subject = $payload['subject']
            ?? sprintf('Bon de commande %s', $bon->getNumCommande());

        $html = $this->twig->render('emails/bon_commande.html.twig', [
            'bon'          => $bon,
            'entreprise'   => $entreprise,
            'messageIntro' => $payload['message'] ?? null,
        ]);

        $pdf      = $this->pdfService->generateBonCommandePdf($bon);
        $filename = sprintf('BonCommande-%s.pdf', $bon->getNumCommande());

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

        // Marquer comme envoyé si la colonne existe
        if (method_exists($bon, 'setIsSent')) {
            $bon->setIsSent(1);
            $this->em->flush();
        }

        return $this->json([
            'message' => 'Bon de commande envoyé avec succès.',
            'to'      => $to,
        ]);
    }
}
