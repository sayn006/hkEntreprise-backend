<?php

namespace App\Entity;

use App\Repository\LoginAuditRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entité pour l'audit des tentatives de connexion.
 * Enregistre chaque tentative (succès/échec) pour analyse et sécurité.
 */
#[ORM\Entity(repositoryClass: LoginAuditRepository::class)]
#[ORM\Index(name: 'idx_login_audit_ip', columns: ['ip_address'])]
#[ORM\Index(name: 'idx_login_audit_username', columns: ['username'])]
#[ORM\Index(name: 'idx_login_audit_created', columns: ['created_at'])]
class LoginAudit
{
    // Raisons d'échec
    public const FAILURE_INVALID_CREDENTIALS = 'invalid_credentials';
    public const FAILURE_ACCOUNT_LOCKED = 'account_locked';
    public const FAILURE_ACCOUNT_INACTIVE = 'account_inactive';
    public const FAILURE_RATE_LIMITED = 'rate_limited';
    public const FAILURE_USER_NOT_FOUND = 'user_not_found';
    public const FAILURE_MALFORMED_REQUEST = 'malformed_request';

    // Sources de connexion
    public const SOURCE_WEB = 'web';
    public const SOURCE_MOBILE = 'mobile';
    public const SOURCE_API = 'api';
    public const SOURCE_BOT = 'bot';
    public const SOURCE_UNKNOWN = 'unknown';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $username = null;

    #[ORM\Column(length: 45)]
    private ?string $ipAddress = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $success = false;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $failureReason = null;

    #[ORM\Column(length: 20, options: ['default' => 'unknown'])]
    private string $source = self::SOURCE_UNKNOWN;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): static
    {
        // Tronquer si trop long
        $this->userAgent = $userAgent ? substr($userAgent, 0, 500) : null;
        return $this;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): static
    {
        $this->success = $success;
        return $this;
    }

    public function getFailureReason(): ?string
    {
        return $this->failureReason;
    }

    public function setFailureReason(?string $failureReason): static
    {
        $this->failureReason = $failureReason;
        return $this;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $source): static
    {
        $this->source = $source;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Crée un enregistrement d'audit pour une tentative réussie
     */
    public static function createSuccess(string $username, string $ipAddress, ?string $userAgent, ?User $user = null, string $source = self::SOURCE_UNKNOWN): self
    {
        $audit = new self();
        $audit->setUsername($username)
            ->setIpAddress($ipAddress)
            ->setUserAgent($userAgent)
            ->setSuccess(true)
            ->setSource($source)
            ->setUser($user);

        return $audit;
    }

    /**
     * Crée un enregistrement d'audit pour une tentative échouée
     */
    public static function createFailure(string $username, string $ipAddress, ?string $userAgent, string $reason, ?User $user = null, string $source = self::SOURCE_UNKNOWN): self
    {
        $audit = new self();
        $audit->setUsername($username)
            ->setIpAddress($ipAddress)
            ->setUserAgent($userAgent)
            ->setSuccess(false)
            ->setFailureReason($reason)
            ->setSource($source)
            ->setUser($user);

        return $audit;
    }

    /**
     * Détecte la source de la requête à partir du User-Agent et du path
     */
    public static function detectSource(?string $userAgent, string $path = ''): string
    {
        $userAgent = strtolower($userAgent ?? '');

        // Détection API
        if (str_starts_with($path, '/api/')) {
            return self::SOURCE_API;
        }

        // Détection Mobile (app Flutter/Dart ou mobile browsers)
        if (str_contains($userAgent, 'dart') || str_contains($userAgent, 'flutter') || str_contains($userAgent, 'gmaxx-mobile')) {
            return self::SOURCE_MOBILE;
        }

        // Détection Bots/Scanners
        $botPatterns = ['bot', 'crawler', 'spider', 'curl', 'wget', 'python', 'java/', 'go-http', 'scanner', 'nikto', 'sqlmap', 'nmap'];
        foreach ($botPatterns as $pattern) {
            if (str_contains($userAgent, $pattern)) {
                return self::SOURCE_BOT;
            }
        }

        // Détection Web (navigateurs classiques)
        $browserPatterns = ['mozilla', 'chrome', 'safari', 'firefox', 'edge', 'opera'];
        foreach ($browserPatterns as $pattern) {
            if (str_contains($userAgent, $pattern)) {
                return self::SOURCE_WEB;
            }
        }

        return self::SOURCE_UNKNOWN;
    }
}
