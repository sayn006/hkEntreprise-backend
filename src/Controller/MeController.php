<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Endpoints liés au user courant (profil).
 *   GET    /api/me                        -> infos user courant
 *   PATCH  /api/me                        -> update nom/prenom/email/telephone
 *   POST   /api/me/change-password        -> {currentPassword, newPassword}
 */
#[Route('/api/me')]
#[IsGranted('ROLE_USER')]
class MeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher,
        private NormalizerInterface $normalizer,
    ) {
    }

    #[Route('', name: 'api_me_get', methods: ['GET'])]
    public function me(): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        $data = $this->normalizer->normalize($user, 'jsonld', ['groups' => ['user:read']]);

        return new JsonResponse($data);
    }

    #[Route('', name: 'api_me_patch', methods: ['PATCH'])]
    public function update(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        $payload = json_decode($request->getContent(), true) ?: [];

        $allowed = ['nom', 'prenom', 'email', 'telephone'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $payload)) {
                $setter = 'set' . ucfirst($field);
                if (method_exists($user, $setter)) {
                    $user->$setter($payload[$field]);
                }
            }
        }

        $this->em->flush();

        $data = $this->normalizer->normalize($user, 'jsonld', ['groups' => ['user:read']]);

        return new JsonResponse($data);
    }

    #[Route('/change-password', name: 'api_me_change_password', methods: ['POST'])]
    public function changePassword(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        $payload = json_decode($request->getContent(), true) ?: [];
        $current = (string) ($payload['currentPassword'] ?? '');
        $new     = (string) ($payload['newPassword'] ?? '');

        if ($current === '' || $new === '') {
            return $this->json(['error' => 'currentPassword et newPassword obligatoires.'], 400);
        }
        if (strlen($new) < 6) {
            return $this->json(['error' => 'Le nouveau mot de passe doit contenir au moins 6 caractères.'], 400);
        }

        if (!$this->hasher->isPasswordValid($user, $current)) {
            return $this->json(['error' => 'Mot de passe actuel incorrect.'], 400);
        }

        $user->setPassword($this->hasher->hashPassword($user, $new));
        $this->em->flush();

        return $this->json(['ok' => true]);
    }
}
