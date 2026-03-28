<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\FournisseurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [new GetCollection(), new Get(), new Post(), new Patch(), new Delete()],
    normalizationContext: ['groups' => ['fournisseur:read']],
    denormalizationContext: ['groups' => ['fournisseur:write']],
)]
#[ORM\Entity(repositoryClass: FournisseurRepository::class)]
class Fournisseur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['fournisseur:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['fournisseur:read', 'fournisseur:write'])]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['fournisseur:read', 'fournisseur:write'])]
    private ?string $adresse = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['fournisseur:read', 'fournisseur:write'])]
    private ?string $codePostal = null;

    #[ORM\Column(length: 60, nullable: true)]
    #[Groups(['fournisseur:read', 'fournisseur:write'])]
    private ?string $ville = null;

    #[ORM\Column(length: 25, nullable: true)]
    #[Groups(['fournisseur:read', 'fournisseur:write'])]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['fournisseur:read', 'fournisseur:write'])]
    private ?string $email = null;

    /**
     * @var Collection<int, BonCommande>
     */
    #[ORM\OneToMany(targetEntity: BonCommande::class, mappedBy: 'fournisseur')]
    #[Groups(['fournisseur:read'])]
    private Collection $bonCommandes;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['fournisseur:read', 'fournisseur:write'])]
    private ?string $contact = null;

    /**
     * @var Collection<int, FournisseurContact>
     */
    #[ORM\OneToMany(targetEntity: FournisseurContact::class, mappedBy: 'fournisseur', orphanRemoval: true)]
    #[Groups(['fournisseur:read'])]
    private Collection $fournisseurContacts;

    public function __construct()
    {
        $this->bonCommandes = new ArrayCollection();
        $this->fournisseurContacts = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->nom .' - '. $this->ville .' - '. $this->codePostal;

    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<int, BonCommande>
     */
    public function getBonCommandes(): Collection
    {
        return $this->bonCommandes;
    }

    public function addBonCommande(BonCommande $bonCommande): static
    {
        if (!$this->bonCommandes->contains($bonCommande)) {
            $this->bonCommandes->add($bonCommande);
            $bonCommande->setFournisseur($this);
        }

        return $this;
    }

    public function removeBonCommande(BonCommande $bonCommande): static
    {
        if ($this->bonCommandes->removeElement($bonCommande)) {
            // set the owning side to null (unless already changed)
            if ($bonCommande->getFournisseur() === $this) {
                $bonCommande->setFournisseur(null);
            }
        }

        return $this;
    }

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function setContact(?string $contact): static
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * @return Collection<int, FournisseurContact>
     */
    public function getFournisseurContacts(): Collection
    {
        return $this->fournisseurContacts;
    }

    public function addFournisseurContact(FournisseurContact $fournisseurContact): static
    {
        if (!$this->fournisseurContacts->contains($fournisseurContact)) {
            $this->fournisseurContacts->add($fournisseurContact);
            $fournisseurContact->setFournisseur($this);
        }

        return $this;
    }

    public function removeFournisseurContact(FournisseurContact $fournisseurContact): static
    {
        if ($this->fournisseurContacts->removeElement($fournisseurContact)) {
            // set the owning side to null (unless already changed)
            if ($fournisseurContact->getFournisseur() === $this) {
                $fournisseurContact->setFournisseur(null);
            }
        }

        return $this;
    }
}
