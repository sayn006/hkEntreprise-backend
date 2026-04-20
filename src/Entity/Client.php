<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use App\Repository\ContactsRepository;
use App\Repository\UploadsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Patch(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['client:read']],
    denormalizationContext: ['groups' => ['client:write']],
)]
#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['client:read', 'chantier:read', 'facture:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['client:read', 'client:write', 'chantier:read', 'facture:read', 'devis:read'])]
    #[Assert\NotBlank(message: 'La raison sociale est obligatoire.')]
    private ?string $raisonSocial = null;

    #[ORM\ManyToOne(inversedBy: 'clients')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['client:read', 'client:write'])]
    #[Assert\NotNull(message: 'La forme juridique est obligatoire.')]
    private ?FormeJuridique $formeJuridique = null;

    #[ORM\Column(length: 150, nullable: true)]
    #[Groups(['client:read', 'client:write'])]
    private ?string $sirenSiret = null;

    #[ORM\Column(length: 25, nullable: true)]
    #[Groups(['client:read', 'client:write', 'chantier:read', 'facture:read', 'devis:read'])]
    private ?string $telephone = null;

    #[ORM\Column(length: 200, nullable: true)]
    #[Groups(['client:read', 'client:write', 'chantier:read', 'facture:read', 'devis:read'])]
    private ?string $email = null;

    #[ORM\ManyToOne(targetEntity: self::class)]
    private ?self $affiliation = null;

    /**
     * @var Collection<int, Chantier>
     */
    #[ORM\OneToMany(targetEntity: Chantier::class, mappedBy: 'client')]
    private Collection $chantiers;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['client:read', 'client:write', 'facture:read'])]
    private ?string $adresse = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['client:read', 'client:write', 'facture:read'])]
    private ?string $codePostal = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['client:read', 'client:write', 'facture:read'])]
    private ?string $ville = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['client:read', 'client:write'])]
    private ?string $emailCompta = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['client:read', 'client:write'])]
    private ?string $telCompta = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    #[Groups(['client:read', 'client:write'])]
    private bool $isActive = true;

    private contactsRepository $contactsRepository;

    // Injecter le repository via un setter ou un constructeur
    public function setContactsRepository(ContactsRepository $contactsRepository): void
    {
        $this->contactsRepository = $contactsRepository;
    }

    // Méthode pour récupérer les contacts associés à cette entité
    public function getContacts(): array
    {
        // Vous pouvez maintenant accéder au repository à l'intérieur de l'entité
        return $this->contactsRepository->findContactsByEntityTypeAndId('Client', $this->id);
    }

    public function __construct()
    {
        $this->chantiers = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString()
    {
        return $this->raisonSocial;
    }


    public function getRaisonSocial(): ?string
    {
        return $this->raisonSocial;
    }

    public function setRaisonSocial(?string $raisonSocial): static
    {
        $this->raisonSocial = $raisonSocial;

        return $this;
    }

    public function getFormeJuridique(): ?FormeJuridique
    {
        return $this->formeJuridique;
    }

    public function setFormeJuridique(?FormeJuridique $formeJuridique): static
    {
        $this->formeJuridique = $formeJuridique;

        return $this;
    }

    public function getSirenSiret(): ?string
    {
        return $this->sirenSiret;
    }

    public function setSirenSiret(?string $sirenSiret): static
    {
        $this->sirenSiret = $sirenSiret;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getAffiliation(): ?self
    {
        return $this->affiliation;
    }

    public function setAffiliation(?self $affiliation): static
    {
        $this->affiliation = $affiliation;

        return $this;
    }

    /**
     * @return Collection<int, Chantier>
     */
    public function getChantiers(): Collection
    {
        return $this->chantiers;
    }

    public function addChantier(Chantier $chantier): static
    {
        if (!$this->chantiers->contains($chantier)) {
            $this->chantiers->add($chantier);
            $chantier->setClient($this);
        }

        return $this;
    }

    public function removeChantier(Chantier $chantier): static
    {
        if ($this->chantiers->removeElement($chantier)) {
            // set the owning side to null (unless already changed)
            if ($chantier->getClient() === $this) {
                $chantier->setClient(null);
            }
        }

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
        return $this->codePostal;
    }

    public function setCodePostal(?string $codePostal): static
    {
        $this->codePostal = $codePostal;

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

    public function getEmailCompta(): ?string
    {
        return $this->emailCompta;
    }

    public function setEmailCompta(?string $emailCompta): static
    {
        $this->emailCompta = $emailCompta;

        return $this;
    }

    public function getTelCompta(): ?string
    {
        return $this->telCompta;
    }

    public function setTelCompta(?string $telCompta): static
    {
        $this->telCompta = $telCompta;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }
}
