<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Repository\EntrepriseRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Twig\Environment;

/**
 * State processor pour User qui hash `plainPassword` vers `password` avant
 * de déléguer au processor Doctrine standard.
 *
 * À la création (Post), envoie un email de bienvenue avec les identifiants
 * en clair. Skippable via header `X-Skip-Welcome-Email: 1` ou query
 * `?skipWelcomeEmail=1` (utile pour seed/fixtures).
 */
final class UserPasswordHasherProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private UserPasswordHasherInterface $passwordHasher,
        private MailerInterface $mailer,
        private Environment $twig,
        private EntrepriseRepository $entrepriseRepo,
        private RequestStack $requestStack,
        private LoggerInterface $logger,
        #[Autowire(param: 'kernel.environment')]
        private string $env = 'prod',
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $plainPassword = null;
        $isCreation = $operation instanceof Post;

        if ($data instanceof User && $data->getPlainPassword()) {
            $plainPassword = $data->getPlainPassword();
            $hashed = $this->passwordHasher->hashPassword($data, $plainPassword);
            $data->setPassword($hashed);
            $data->eraseCredentials();
        }

        $result = $this->persistProcessor->process($data, $operation, $uriVariables, $context);

        if ($isCreation && $result instanceof User && $plainPassword !== null && !$this->shouldSkipWelcomeEmail()) {
            $this->sendWelcomeEmail($result, $plainPassword);
        }

        return $result;
    }

    private function shouldSkipWelcomeEmail(): bool
    {
        $req = $this->requestStack->getCurrentRequest();
        if ($req === null) return true;
        if ($req->headers->get('X-Skip-Welcome-Email') === '1') return true;
        if ($req->query->get('skipWelcomeEmail') === '1') return true;
        return false;
    }

    private function sendWelcomeEmail(User $user, string $plainPassword): void
    {
        $to = $user->getEmail();
        if (!$to || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            // Pas d'email → on log et on skip
            $this->logger->info('Welcome email skipped: user without valid email', [
                'userId' => $user->getId(),
            ]);
            return;
        }

        $entreprise = $this->entrepriseRepo->findOneBy([]);
        $fromEmail = $entreprise?->resolveFromEmail('default')
            ?? $_ENV['MAILER_FROM']
            ?? 'no-reply@hk-entreprise.fr';
        $fromName = $entreprise?->getNom() ?? 'HK Entreprise';

        $loginUrl = $_ENV['APP_FRONTEND_URL'] ?? 'https://hk.cashloose.com';
        $loginUrl = rtrim($loginUrl, '/') . '/login';

        try {
            $html = $this->twig->render('emails/user_welcome.html.twig', [
                'user'          => $user,
                'entreprise'    => $entreprise,
                'plainPassword' => $plainPassword,
                'loginUrl'      => $loginUrl,
            ]);

            $subject = sprintf('Bienvenue sur %s', $entreprise?->getNom() ?? 'HK Entreprise');

            $email = (new Email())
                ->from(new Address($fromEmail, $fromName))
                ->to($to)
                ->subject($subject)
                ->html($html);

            $this->mailer->send($email);
        } catch (\Throwable $e) {
            // Best-effort : on ne bloque pas la création de user si l'envoi échoue
            $this->logger->error('Welcome email failed', [
                'userId'  => $user->getId(),
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
