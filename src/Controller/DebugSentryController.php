<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Route de debug pour vérifier l'intégration Sentry.
 * À supprimer une fois la vérification faite.
 */
class DebugSentryController extends AbstractController
{
    #[Route('/debug-sentry-error', name: 'debug_sentry_error', methods: ['GET'])]
    public function trigger(): void
    {
        throw new \RuntimeException('HK test Sentry : exception volontaire.');
    }
}
