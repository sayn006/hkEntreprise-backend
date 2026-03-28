<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Patch(),
    ],
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']],
)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups(['user:read'])]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $prenom = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $telephone = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $code_postal = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $ville = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $email = null;

    #[ORM\ManyToOne]
    private ?TypeLibelles $fonction = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private ?int $menuToggel = 0;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private ?bool $isActive = true;

    // ===== Champs de sécurité login =====

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $failedLoginAttempts = 0;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $lockedUntil = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $lastLoginAt = null;

    /**
     * @var Collection<int, ChantierResponsable>
     */
    #[ORM\OneToMany(targetEntity: ChantierResponsable::class, mappedBy: 'responsable')]
    private Collection $chantierResponsables;

    #[ORM\Column(nullable: true)]
    private ?int $suivi = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $taux_horaire = null;

    public function __construct()
    {
        $this->chantierResponsables = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->nom . ' ' . $this->prenom;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->code_postal;
    }

    public function setCodePostal(?string $code_postal): static
    {
        $this->code_postal = $code_postal;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): static
    {
        $this->ville = $ville;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getFonction(): ?TypeLibelles
    {
        return $this->fonction;
    }

    public function setFonction(?TypeLibelles $fonction): static
    {
        $this->fonction = $fonction;

        return $this;
    }

    public function getMenuToggel(): ?int
    {
        return $this->menuToggel;
    }

    public function setMenuToggel(int $menuToggel): static
    {
        $this->menuToggel = $menuToggel;

        return $this;
    }

    /**
     * @return Collection<int, ChantierResponsable>
     */
    public function getChantierResponsables(): Collection
    {
        return $this->chantierResponsables;
    }

    public function addChantierResponsable(ChantierResponsable $chantierResponsable): static
    {
        if (!$this->chantierResponsables->contains($chantierResponsable)) {
            $this->chantierResponsables->add($chantierResponsable);
            $chantierResponsable->setResponsable($this);
        }

        return $this;
    }

    public function removeChantierResponsable(ChantierResponsable $chantierResponsable): static
    {
        if ($this->chantierResponsables->removeElement($chantierResponsable)) {
            // set the owning side to null (unless already changed)
            if ($chantierResponsable->getResponsable() === $this) {
                $chantierResponsable->setResponsable(null);
            }
        }

        return $this;
    }

    public function getSuivi(): ?int
    {
        return $this->suivi;
    }

    public function setSuivi(?int $suivi): static
    {
        $this->suivi = $suivi;

        return $this;
    }

    public function getTauxHoraire(): ?float
    {
        return $this->taux_horaire;
    }

    public function setTauxHoraire(?float $taux_horaire): static
    {
        $this->taux_horaire = $taux_horaire;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Vérifie si l'utilisateur est un admin
     */
    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->roles, true);
    }

    /**
     * Vérifie si l'utilisateur est un supérieur (ROLE_SUPERIOR)
     */
    public function isSuperior(): bool
    {
        return in_array('ROLE_SUPERIOR', $this->roles, true);
    }

    /**
     * Vérifie si l'utilisateur est un ouvrier (ROLE_WORKER)
     */
    public function isWorker(): bool
    {
        return in_array('ROLE_WORKER', $this->roles, true);
    }

    /**
     * Vérifie si l'utilisateur est un utilisateur standard (ROLE_USER uniquement)
     */
    public function isStandard(): bool
    {
        return count($this->roles) === 0 || (count($this->roles) === 1 && in_array('ROLE_USER', $this->roles, true));
    }

    /**
     * Retourne le rôle principal de l'utilisateur
     */
    public function getMainRole(): string
    {
        if (in_array('ROLE_ADMIN', $this->roles, true)) {
            return 'ROLE_ADMIN';
        }
        if (in_array('ROLE_SUPERIOR', $this->roles, true)) {
            return 'ROLE_SUPERIOR';
        }
        if (in_array('ROLE_WORKER', $this->roles, true)) {
            return 'ROLE_WORKER';
        }
        return 'ROLE_USER';
    }

    /**
     * Retourne le label français du rôle principal
     */
    public function getRoleLabel(): string
    {
        return match ($this->getMainRole()) {
            'ROLE_ADMIN' => 'Admin',
            'ROLE_SUPERIOR' => 'Avancé',
            'ROLE_WORKER' => 'Ouvrier',
            default => 'Standard',
        };
    }

    // ===== Méthodes de sécurité login =====

    public function getFailedLoginAttempts(): int
    {
        return $this->failedLoginAttempts;
    }

    public function setFailedLoginAttempts(int $failedLoginAttempts): static
    {
        $this->failedLoginAttempts = $failedLoginAttempts;
        return $this;
    }

    public function incrementFailedLoginAttempts(): static
    {
        $this->failedLoginAttempts++;
        return $this;
    }

    public function resetFailedLoginAttempts(): static
    {
        $this->failedLoginAttempts = 0;
        $this->lockedUntil = null;
        return $this;
    }

    public function getLockedUntil(): ?\DateTimeInterface
    {
        return $this->lockedUntil;
    }

    public function setLockedUntil(?\DateTimeInterface $lockedUntil): static
    {
        $this->lockedUntil = $lockedUntil;
        return $this;
    }

    public function isLocked(): bool
    {
        if ($this->lockedUntil === null) {
            return false;
        }
        return $this->lockedUntil > new \DateTime();
    }

    public function lockAccount(int $minutes = 15): static
    {
        $this->lockedUntil = new \DateTime("+{$minutes} minutes");
        return $this;
    }

    public function getLastLoginAt(): ?\DateTimeInterface
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTimeInterface $lastLoginAt): static
    {
        $this->lastLoginAt = $lastLoginAt;
        return $this;
    }

    public function updateLastLogin(): static
    {
        $this->lastLoginAt = new \DateTime();
        return $this;
    }
}
