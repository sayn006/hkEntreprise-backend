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

/**
 * Reset du mot de passe d'un user par un admin (ou par l'utilisateur lui-même).
 *   POST /api/users/{id}/change-password  body: { "newPassword": "..." }
 */
#[Route('/api/users')]
#[IsGranted('ROLE_USER')]
class UserPasswordController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher,
    ) {
    }

    #[Route('/{id}/change-password', name: 'api_user_change_password', methods: ['POST'])]
    public function changePassword(User $target, Request $request): JsonResponse
    {
        /** @var User|null $current */
        $current = $this->getUser();
        if (!$current) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        // Seul un admin peut reset un autre user. Un user non-admin ne peut reset que lui-même.
        if ($current->getId() !== $target->getId() && !$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['error' => 'Accès refusé.'], 403);
        }

        $payload = json_decode($request->getContent(), true) ?: [];
        $newPassword = (string) ($payload['newPassword'] ?? '');

        if ($newPassword === '') {
            return $this->json(['error' => 'newPassword obligatoire.'], 400);
        }
        if (strlen($newPassword) < 6) {
            return $this->json(['error' => 'Le mot de passe doit contenir au moins 6 caractères.'], 400);
        }

        $target->setPassword($this->hasher->hashPassword($target, $newPassword));
        $this->em->flush();

        return $this->json(['ok' => true, 'id' => $target->getId()]);
    }
}
