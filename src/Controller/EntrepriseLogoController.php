<?php

namespace App\Controller;

use App\Entity\Entreprise;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Upload du logo d'une entreprise.
 *
 * POST /api/entreprises/{id}/upload-logo
 * multipart/form-data champ `file`.
 *
 * Le fichier est écrit dans /public/uploads/ et l'URL relative est stockée
 * dans entreprise.logo.
 */
#[Route('/api/entreprises')]
#[IsGranted('ROLE_USER')]
class EntrepriseLogoController extends AbstractController
{
    private const ALLOWED_MIME = [
        'image/png',
        'image/jpeg',
        'image/jpg',
        'image/webp',
        'image/svg+xml',
    ];
    private const MAX_SIZE_BYTES = 5 * 1024 * 1024; // 5 Mo

    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    #[Route('/{id}/upload-logo', name: 'api_entreprise_upload_logo', methods: ['POST'])]
    public function upload(Entreprise $entreprise, Request $request): JsonResponse
    {
        /** @var UploadedFile|null $file */
        $file = $request->files->get('file');

        if (!$file) {
            return $this->json(['error' => 'Aucun fichier fourni (champ multipart "file" manquant).'], 400);
        }

        if (!$file->isValid()) {
            return $this->json(['error' => 'Upload invalide : ' . $file->getErrorMessage()], 400);
        }

        if ($file->getSize() > self::MAX_SIZE_BYTES) {
            return $this->json(['error' => 'Fichier trop volumineux (max 5 Mo).'], 400);
        }

        $mime = $file->getMimeType() ?? '';
        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            return $this->json([
                'error' => "Format non supporté ($mime). Autorisés : PNG, JPEG, WEBP, SVG.",
            ], 400);
        }

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0775, true);
        }

        $ext = $file->guessExtension() ?: 'png';
        $basename = sprintf('entreprise-%d-logo-%s.%s', $entreprise->getId(), bin2hex(random_bytes(4)), $ext);

        try {
            $file->move($uploadDir, $basename);
        } catch (\Throwable $e) {
            return $this->json(['error' => 'Échec écriture : ' . $e->getMessage()], 500);
        }

        // Supprimer l'ancien logo s'il était local
        $oldLogo = $entreprise->getLogo();
        if ($oldLogo && str_starts_with($oldLogo, '/uploads/')) {
            $oldPath = $this->getParameter('kernel.project_dir') . '/public' . $oldLogo;
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        $publicUrl = '/uploads/' . $basename;
        $entreprise->setLogo($publicUrl);
        $this->em->flush();

        return $this->json([
            'logo' => $publicUrl,
            'id'   => $entreprise->getId(),
        ]);
    }
}
