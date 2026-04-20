<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Met à jour lastLoginAt à chaque login JSON réussi.
 */
#[AsEventListener(event: 'lexik_jwt_authentication.on_authentication_success')]
final class LoginSuccessListener
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function __invoke(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof User) {
            return;
        }

        $user->updateLastLogin();
        $this->em->flush();
    }
}
